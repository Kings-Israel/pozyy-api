<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(['prefix' => 'student', 'middleware' => 'jwt.auth'], function() {
    Route::post('/school/code/verify', 'Students\studentscontroller@verifyCode');
    Route::get('/all/channels','Students\studentscontroller@all_channels');
    Route::post('/all/videos', 'Students\studentscontroller@all_videos');
    Route::get('/school/{id}/videos', 'Students\studentscontroller@school_video');
    Route::get('/details/{id}', 'Students\studentscontroller@getKid');
    Route::post('/performance/save', 'Students\studentscontroller@addKidPerformance');
    Route::post('/grade/assign', 'Students\studentscontroller@assignGrade');
});

Route::group(['prefix' => 'parent', 'middleware' => 'jwt.auth'], function() {
    Route::post('/add/student', 'Students\studentscontroller@add_kid');
    Route::get('/students','Students\studentscontroller@get_kids');
    Route::post('/edit/student/{id}', 'Students\studentscontroller@edit_kid');
    Route::post('/student/login', 'Students\studentscontroller@choose_between_student');

    Route::get('/account/delete', [UserController::class, 'deleteAccount']);
});
