<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Auth;
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

Route::get('/jambopay/{user_id}/{type}/{id}', function($user_id, $type, $id) {
    return view('jambopay')->with([
        'id' => $id,
        'type' => $type,
        'user_id' => $user_id,
        'url' => route('jambopay.checkout'),
    ]);
});

Route::get('/jambopay/success', function() {
    return view('jambopay-success');
});

