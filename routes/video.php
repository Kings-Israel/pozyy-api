<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => 'api'], function() {
    Route::post('/add/video', 'Video\videocontroller@admin_add_video');
    Route::post('/update/video', 'Video\videocontroller@admin_update_video');
    Route::post('/delete/video', 'Video\videocontroller@admin_delete_video');
    Route::get('/videos', 'Video\videocontroller@admin_show_videos');
    Route::get('/video/{id}', 'Video\videocontroller@admin_show_video');
    Route::get('/count/videos', 'Video\videocontroller@count_videos');
});
Route::group(['prefix' => 'school', 'middleware' => 'api'], function() {
    Route::post('/add/video', 'Video\videocontroller@school_add_video');
    Route::get('/videos', 'Video\videocontroller@school_show_videos');
    Route::get('/video/{id}', 'Video\videocontroller@school_show_video');
    Route::post('/update/video', 'Video\videocontroller@school_update_video');
    Route::post('/delete/video', 'Video\videocontroller@school_delete_video');
    Route::get('/count/videos', 'Video\videocontroller@school_count_videos');
});
Route::get('/channel/{id}', 'Video\videocontroller@show_channel');
Route::post('/add/channel', 'Video\videocontroller@add_channel');
Route::post('/add/subchannel', 'Video\videocontroller@add_subchannel');
Route::post('/update/channel', 'Video\videocontroller@update_channel');
Route::post('/update/subchannel/{id}', 'Video\videocontroller@update_subchannel');
Route::get('/all/channels', 'Video\videocontroller@all_channel');
Route::post('/channel/videos', 'Video\videocontroller@channel_video');
Route::post('/subchannel/videos', 'Video\videocontroller@subchannel_videos');
