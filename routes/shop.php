<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ShopItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'shop', 'as' => 'shop.'], function () {
    // Shop Controller
    Route::get('items/all', [ShopItemController::class, 'allItems'])->name('all');
    Route::post('item/add', [ShopItemController::class, 'addItem'])->name('add');
    Route::delete('item/{id}/delete', [ShopItemController::class, 'deleteItem'])->name('delete');
    Route::post('item/update', [ShopItemController::class, 'updateItem'])->name('update');

    Route::get('item/{id}', [ShopItemController::class, 'singleItem'])->name('single');

    // Cart Controller
    Route::get('/cart', [CartController::class, 'getCart'])->name('cart');
    Route::post('/item/cart/add', [CartController::class, 'addToCart'])->name('add-to-cart');
    Route::post('/item/cart/delete', [CartController::class, 'deleteFromCart'])->name('delete-from-cart');
    Route::post('/items/cart/delete', [CartController::class, 'deleteItemsFromCart'])->name('delete-items-from-cart');
    Route::post('/checkout', [CartController::class, 'checkout']);
    Route::get('/items/purchased', [CartController::class, 'purchasedItems'])->name('items.purchased');

    Route::post('/jambopay/checkout', function(Request $request) {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        return view('shop-jambopay-checkout')
            ->with([
                'items' => $request->items,
                'user_id' => auth()->id(),
                'url' => route('shop.jambopay.checkout'),
            ]);
    });

});

Route::post('/shop/jambopay', [CartController::class, 'jambopayCheckout'])->name('shop.jambopay.checkout');
Route::post('/shop/jambopay/callback', [CartController::class, 'jambopayCallback'])->name('shop.jambopay.checkout.callback');

Route::post('shop/checkout/callback', [CartController::class, 'purchasedItemCallback'])->name('shop.item.purchase.callback');
