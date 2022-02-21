<?php

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

<<<<<<< HEAD
Route::get('/', function () {
    return view('welcome');
});

Route::get('/php', function() {
	return view('php');
=======
Route::get('/php', function() {
    return view('php');
});

Route::get('/', function () {
    return view('welcome');
>>>>>>> 6e5e6a46d81753dd0fdd1e5ae931679e87419a02
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
