<?php

use App\Http\Controllers\ShopItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api', 'prefix' => 'shop', 'as' => 'shop.'], function () {
  Route::get('items/all', [ShopItemController::class, 'allItems'])->name('all');
  Route::post('item/add', [ShopItemController::class, 'addItem'])->name('add');
  Route::delete('item/{id}/delete', [ShopItemController::class, 'deleteItem'])->name('delete');
  Route::post('item/update', [ShopItemController::class, 'updateItem'])->name('update');
});
