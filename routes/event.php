<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api', 'prefix' => 'event', 'as' => 'event.'], function () {
  Route::get('/all', [EventController::class, 'allEvents'])->name('all');
  Route::post('/add', [EventController::class, 'addEvent'])->name('add');
  Route::delete('{id}/delete', [EventController::class, 'deleteEvent'])->name('delete');
  Route::post('update', [EventController::class, 'updateEvent'])->name('update');
});
