<?php

namespace App\Http\Controllers;

use App\Cart;
use App\MpesaPayment;
use App\ShopItem;
use App\UserShopItems;
use Illuminate\Http\Request;
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
        $this->validate($request, [
            'item_id' => ['required']
        ]);

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
        $this->validate($request, [
            'item_id' => ['required']
        ]);

        $item = Cart::where('user_id', auth()->user()->id)->where('id', $request->item_id)->first();

        if ($item) {
            $item->delete();
            return pozzy_httpOk('Item removed from cart');
        }

        return pozzy_httpBadRequest('The item was not found in your cart');
    }

    public function deleteItemsFromCart(Request $request)
    {
        $this->validate($request, [
            'items' => ['required', 'array']
        ], [
            'items' => 'Please select item(s) to delete'
        ]);

        collect($request->items)->each(function($item) {
            info($item);
            $cartItem = Cart::where('user_id', auth()->user()->id)->where('id', $item)->first();

            if ($cartItem) {
                $cartItem->delete();
            }
        });

        return pozzy_httpOk('Items removed from cart');
    }

    public function checkout(Request $request)
    {
        $this->validate($request, [
            'items' => ['required', 'array'],
        ]);

        // Get total amount from items chosen
        $totalAmount = 0;
        foreach ($request->items as $key => $item) {
            $cartItem = Cart::find($item);
            $shopItem = ShopItem::find($cartItem->shop_item_id);
            $totalAmount += $shopItem->price;
        }

        $phone_number = auth()->user()->phone_number;
        if (strlen($request->phone_number) == 9) {
            $phone_number = '254'.$request->phone_number;
        } else {
            $phone_number = '254'.substr($request->phone_number, -9);
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

        if ($results['response_code'] === 0) {
            $shop_item = ShopItem::find($request->item_id);
            $mpesa_payable_type = ShopItem::class;
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
    }

    public function purchaseItemCallback(Request $request)
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
            $mpesaPayment = MpesaPayment::where('checkout_request_id', $result['checkout_request_id'])->first();
            $mpesaPayment->mpesa_receipt_number = $result['mpesa_receipt_number'];
            $mpesaPayment->save();

            $userItem = UserShopItems::where('mpesa_checkout_string', $result['checkout_request_id'])->first();
            $userItem->update([
                'isPurchased' => true
            ]);
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
