<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Helpers\NumberGenerator;
use App\JambopayPayment;
use App\MpesaPayment;
use App\ShopItem;
use App\UserShopItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function getCart()
    {
        $cart = Cart::where('user_id', auth()->user()->id)->get();
        $cart->each(function ($item) {
            $item->load('shopItem');
        });

        return pozzy_httpOk($cart);
    }

    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $item = ShopItem::find($request->item_id);

        if (!$item) {
            return pozzy_httpNotFound('This item is not in the shop anymore');
        }

        auth()->user()->cartItems()->create([
            'shop_item_id' => $request->item_id,
            'quantity' => 1
        ]);

        return pozzy_httpOk('Item added to cart');
    }

    public function deleteFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $item = Cart::where('user_id', auth()->user()->id)->where('id', $request->item_id)->first();

        if ($item) {
            $item->delete();
            return pozzy_httpOk('Item removed from cart');
        }

        return pozzy_httpBadRequest('The item was not found in your cart');
    }

    public function deleteItemsFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array']
        ],[
            'items' => 'Please select item(s) to delete'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        collect($request->items)->each(function($item) {
            $cartItem = Cart::where('user_id', auth()->user()->id)->where('id', $item)->first();

            if ($cartItem) {
                $cartItem->delete();
            }
        });

        return pozzy_httpOk('Items removed from cart');
    }

    public function jambopayCheckout(Request $request)
    {
        info($request->all());

        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array'],
            'user_id' => ['required']
        ],[
            'items' => 'Please select item(s) to checkout',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        // Get total amount from items chosen
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $cartItem = Cart::find($item);
            $shopItem = ShopItem::find($cartItem->shop_item_id);
            $totalAmount += $shopItem->price;

            $invoice_number = NumberGenerator::generateNumber(JambopayPayment::class, 'invoice_id');

            JambopayPayment::create([
                'invoice_id' => $invoice_number,
                'user_id' => $request->user_id,
                'jambopay_payable_id' => $item,
                'jambopay_payable_type' => ShopItem::class,
            ]);

            UserShopItems::create([
                'user_id' => $request->user_id,
                'shop_item_id' => $item,
                'mpesa_checkout_string' => $invoice_number,
            ]);
        }

        $token = JambopayPaymentController::accessToken();

        return response()->json([
            'success' => true,
            'invoice_id' =>$invoice_number,
            'access_token' => $token,
            'amount' => $totalAmount,
            'client_key' => config('services.jambopay.client_key'),
            'callback_url' => route('shop.jambopay.checkout.callback'),
            'cancel_url' => route('jambopay.cancel'),
        ], 200);
    }

    public function jambopayCallback(Request $request)
    {
        $payments = JambopayPayment::where('invoice_id', $request->invoice_id)->get();

        if ($request->has('receipt') && $request->receipt != 'null') {
            $payments->each(function($payment) use ($request) {
                $payment->update([
                    'receipt' => $request->receipt,
                ]);

            });
        }

        $user_shop_items = UserShopItems::where('mpesa_checkout_string', $request->invoice_id)->get();

        $user_shop_items->each(function($item) {
            $item->update([
                'isPurchased' => true
            ]);
        });

        return response()->json(['message' => 'Payment successfull'], 200);
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array']
        ],[
            'items' => 'Please select item(s) to checkout'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        // Get total amount from items chosen
        $totalAmount = 0;
        foreach ($request->items as $key => $item) {
            $cartItem = Cart::find($item);
            $shopItem = ShopItem::find($cartItem->shop_item_id);
            $totalAmount += $shopItem->price;
        }

        $phone_number = auth()->user()->phone_number;
        if (strlen($phone_number) == 9) {
            $phone_number = '254'.$phone_number;
        } else {
            $phone_number = '254'.substr($phone_number, -9);
        }

        $account_number = Str::upper(Str::random(3)).time().Str::upper(Str::random(3));
        $transaction = new MpesaPaymentController;
        $results = $transaction->stkPush(
            $phone_number,
            $totalAmount,
            route('shop.item.purchase.callback'),
            $account_number,
            'Purchase of Shop Item'
        );

        if ($results['response_code'] != NULL) {
            $mpesa_payable_type = ShopItem::class;
            foreach ($request->items as $item) {
                $cartItem = Cart::find($item);
                $shop_item = ShopItem::find($cartItem->shop_item_id);
                MpesaPayment::create([
                    'user_id' => auth()->user()->id,
                    'user_phone_number' => $phone_number,
                    'mpesa_payable_id' => $shop_item->id,
                    'mpesa_payable_type' => $mpesa_payable_type,
                    'checkout_request_id' => $results['checkout_request_id']
                ]);

                auth()->user()->purchasedItems()->create([
                    'shop_item_id' => $shop_item->id,
                    'mpesa_checkout_string' => $results['checkout_request_id']
                ]);
            }

            return pozzy_httpOk('Payment being processed');
        }

        return pozzy_httpNotFound('An error occurred while processing the payment');
    }

    public function purchasedItemCallback(Request $request)
    {
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);

        info($callbackJSONData);

        $result_code = $callbackData->Body->stkCallback->ResultCode;
        $merchant_request_id = $callbackData->Body->stkCallback->MerchantRequestID;
        $checkout_request_id = $callbackData->Body->stkCallback->CheckoutRequestID;
        $amount = $callbackData->Body->stkCallback->CallbackMetadata->Item[0]->Value;
        $mpesa_receipt_number = $callbackData->Body->stkCallback->CallbackMetadata->Item[1]->Value;

        $result = [
           "result_code" => $result_code,
           "merchant_request_id" => $merchant_request_id,
           "checkout_request_id" => $checkout_request_id,
           "amount" => $amount,
           "mpesa_receipt_number" => $mpesa_receipt_number,
        ];

        if($result['result_code'] == 0) {
            $mpesaPayments = MpesaPayment::where('checkout_request_id', $result['checkout_request_id'])->get();
            foreach ($mpesaPayments as $payment) {
                $payment->mpesa_receipt_number = $result['mpesa_receipt_number'];
                $payment->save();

                $userItems = UserShopItems::where('mpesa_checkout_string', $result['checkout_request_id'])->get();
                foreach ($userItems as $item) {
                    $item->update([
                        'isPurchased' => true
                    ]);
                }
            }
        }
    }

    public function purchasedItems()
    {
        $purchasedItems = UserShopItems::where('user_id', auth()->user()->id)->where('isPurchased', true)->get();
        $itemsDetails = [];

        foreach ($purchasedItems as $item) {
            array_push($itemsDetails, ShopItem::find($item->shop_item_id));
        }

        return pozzy_httpOk($itemsDetails);
    }
}
