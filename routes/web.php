<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/php', function() {
    return view('php');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/event/{ticket_id}', [EventController::class, 'viewTicket']);

Route::get('/ticket', function() {
    return view('ticket');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/config-cache', function() {
    $execute = Artisan::call('config:cache');
    return '<h1>Cache facade value cleared</h1>';
});

Route::get('route-clear', function() {
    $execute = Artisan::call('route:clear');
    return '<h1>Routes cached</h1>';
});

Route::get('/store', function() {
    Artisan::call('storage:link');
});
