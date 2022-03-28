<?php

namespace App\Http\Controllers;

use App\Cart;
use App\ShopItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function getCart()
    {
        $cart = auth()->user()->cartItems();

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
            'item_id' => $request->item_id
        ]);

        return pozzy_httpOk('Item added to cart');
    }

    public function deleteFromCart(Request $request)
    {
        $this->validate($request, [
            'item_id' => ['required']
        ]);

        $deleted = Cart::where('user_id', auth()->user()->id)->where('shop_item_id', $request->item_id)->first();

        if ($deleted->delete()) {
            return pozzy_httpOk('Item removed from cart');
        }

        return pozzy_httpBadRequest('Error deleting item');
    }

    public function checkout(Request $request)
    {
        
    }
}
