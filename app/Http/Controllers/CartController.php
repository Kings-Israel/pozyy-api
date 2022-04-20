<?php

namespace App\Http\Controllers;

use App\Cart;
use App\ShopItem;
use Illuminate\Http\Request;

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

        $item = Cart::where('user_id', auth()->user()->id)->where('shop_item_id', $request->item_id)->first();

        if ($item) {
            $item->delete();
            return pozzy_httpOk('Item removed from cart');
        }

        return pozzy_httpBadRequest('The item was not found in your cart');
    }

    public function checkout(Request $request)
    {

    }
}
