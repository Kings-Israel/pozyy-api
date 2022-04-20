<?php

namespace App\Http\Controllers;

use App\MpesaPayment;
use App\ShopItem;
use App\UserShopItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopItemController extends Controller
{
    private function deleteFile($filePath, $folder)
    {
        $file = collect(explode('/', $filePath));
        Storage::disk('shop')->delete($folder.'/'.$file->last());
    }

    public function allItems()
    {
        $items = ShopItem::all();

        return pozzy_httpOk($items);
    }

    public function addItem(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
            'description' => ['required'],
            'price' => ['required'],
            'product_image' => ['required']
        ]);

        $item = new ShopItem;
        $item->name = $request->name;
        $item->description = strip_tags($request->description);
        $item->price = $request->price;
        $item->product_image = config('services.app_url.url').'/storage/shop/product/image/'.pathinfo($request->product_image->store('product/image', 'shop'), PATHINFO_BASENAME);

        if ($item->save()) {
            return pozzy_httpCreated($item);
        }

        return pozzy_httpBadRequest('Invalid details');
    }

    public function updateItem(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
            'description' => ['required'],
            'price' => ['required'],
        ]);

        $item = ShopItem::find($request->id);
        $item->name = $request->name;
        $item->description = strip_tags($request->description);
        $item->price = $request->price;

        if ($request->hasFile('product_image')) {
            $this->deleteFile($item->product_image, 'product/image');
            $item->product_image = config('services.app_url.url').'/storage/shop/product/image/'.pathinfo($request->product_image->store('product/image', 'shop'), PATHINFO_BASENAME);
        }

        if ($item->save()) {
            return pozzy_httpOk($item);
        }

        return pozzy_httpBadRequest('Invalid details');
    }

    public function deleteItem($id)
    {
        $item = ShopItem::find($id);

        $this->deleteFile($item->product_image, 'product/image');

        $item->delete();

        return pozzy_httpOk($item);
    }

    public function singleItem($id)
    {
        $item = ShopItem::find($id);

        return pozzy_httpOk($item);
    }

    public function purchaseItem(Request $request)
    {
        $this->validate($request, [
            'items' => ['required', 'array'],
        ]);

        // Get total amount from items chosen
        $items = collect($request->items)->each(function($item) {
            return ShopItem::find($item);
        });

        return response()->json($items, 200);

        $phone_number = Auth::user()->phone_number;
        if (strlen($request->phone_number) == 9) {
            $phone_number = '254'.$request->phone_number;
        } else {
            $phone_number = '254'.substr($request->phone_number, -9);
        }

        $account_number = Str::upper(Str::random(3)).time().Str::upper(Str::random(3));
        $transaction = new MpesaPaymentController;
        $results = $transaction->stkPush(
            $phone_number,
            $request->amount,
            // route('shop.item.purchase.callback'),
            'https://pozzy.com/api/ticket/callback',
            $account_number,
            'Purchase of Shop Item'
        );

        if ($results['response_code'] === 0) {
            $shop_item = ShopItem::find($request->item_id);
            $mpesa_payable_type = ShopItem::class;
            MpesaPayment::create([
                'user_id' => Auth::user()->id,
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
        $purchasedItems = auth()->user()->purchasedItems;
        $itemsDetails = [];

        foreach ($purchasedItems as $item) {
            array_push($itemsDetails, ShopItem::find($item->shop_item_id));
        }

        return pozzy_httpOk($itemsDetails);
    }
}
