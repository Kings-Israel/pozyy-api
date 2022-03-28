<?php

namespace App\Http\Controllers;

use App\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
}
