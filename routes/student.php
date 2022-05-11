<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'student', 'middleware' => 'jwt.auth'], function() {
    Route::post('/school/code/verify', 'Students\studentscontroller@verifyCode');
    Route::get('/all/channels','Students\studentscontroller@all_channels');
    Route::post('/all/videos', 'Students\studentscontroller@all_videos');
    Route::post('/school/videos', 'Students\studentscontroller@school_video');
});

Route::group(['prefix' => 'parent', 'middleware' => 'jwt.auth'], function() {
    Route::post('/add/student', 'Students\studentscontroller@add_kid');
    Route::get('/students','Students\studentscontroller@get_kids');
    Route::post('/edit/student/{id}', 'Students\studentscontroller@edit_kid');
    Route::post('/student/login', 'Students\studentscontroller@choose_between_student');
});
