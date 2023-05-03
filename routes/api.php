<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\PozyyTvController;
use App\Http\Controllers\GameNightController;
use App\Http\Controllers\JambopayPaymentController;
use App\Http\Controllers\Video\videocontroller;

Route::resource('/blogs', 'BlogController');
Route::post('/blogs/update', 'BlogController@updateBlog');

Route::get('/all/category', 'CategoryController@index');
Route::post('/category', 'CategoryController@create');
Route::get('/category/delete/{id}', 'CategoryController@delete');

Route::get('/activity/all', 'ActivityController@index');
Route::post('/activity', 'ActivityController@store');
Route::get('/activity/{id}', 'ActivityController@edit');
Route::post('/activity/{id}', 'ActivityController@update');
Route::delete('/activity/{id}', 'ActivityController@delete');

Route::get('/add', 'UserController@index')->name('user');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('school_login', 'AuthController@school_login');
    Route::post('parent_login', 'AuthController@parent_login');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');
    Route::post('parent_register', 'AuthController@parent_register');
    Route::post('/forgot-password', 'AuthController@forgotPassword');
    Route::post('/reset-password', 'AuthController@resetPassword');
    // Route::post('school_register', 'AuthController@school_register');
});

Route::post('/game-night/payment/callback', [GameNightController::class, 'gameNightPaymentCallback'])->name('game-night.payment.callback');

Route::get('/game-night/game-days', [GameNightController::class, 'getGameDays']);
Route::get('/game-night/creators-challenges', [GameNightController::class, 'getCreatorsChallenges']);

Route::group(['middleware' => 'jwt.auth'], function ($router) {
    // Admin Game Night
    Route::get('/admin/game-nights', [GameNightController::class, 'index']);
    Route::post('/game-night/create', [GameNightController::class, 'store']);
    Route::post('/game-night/{id}/update', [GameNightController::class, 'update']);
    Route::delete('/game-night/{id}/delete', [GameNightController::class, 'destroy']);
    Route::get('game-nights/trashed', [GameNightController::class, 'trashed']);

    Route::post('/game/to/game-night', [GamesController::class, 'addToGameNight']);
    Route::post('/game/from/game-night', [GamesController::class, 'removeFromGameNight']);

    // Trivia
    Route::get('/trivias', 'GamesController@getAllTrivias');
    Route::get('/trivia/categories', 'GamesController@getTriviaCategories');
    Route::get('/trivia/{id}/questions', 'GamesController@getTriviaQuestions');
    Route::delete('/trivia/{id}/delete', 'GamesController@deleteTrivia');
    Route::post('/trivia/category', 'GamesController@addTriviaCategory');
    Route::get('/trivia/category/{id}/delete', 'GamesController@deleteTriviaCategory');
    Route::post('/add/trivia', 'GamesController@addTrivia');
    Route::post('/trivia/update', 'GamesController@updateTrivia');
    Route::post('/add/trivia/question', 'GamesController@addTriviaQuestions');
    Route::delete('/trivia/question/{id}/delete', 'GamesController@deleteTriviaQuestion');

    Route::get('/trivia/{id}/question/new', 'GamesController@getNewTriviaQuestion');
    Route::post('/trivia/save', 'GamesController@saveSolvedTriviaQuestion')->middleware('throttle:200,1');

    // User Game Night
    Route::get('/game-nights', [GameNightController::class, 'getGameNights']);
    Route::get('/game-night/categories', [GameNightController::class, 'getCategories']);
    Route::get('/game-night/{id}', [GameNightController::class, 'getGameNight']);

    Route::get('/game-night/{id}/games', [GameNightController::class, 'getGameNightGames']);

    Route::post('/game-night/payment', [GameNightController::class, 'gameNightPayment']);

    // Two Pictures One Word
    Route::get('/twopicsgames', 'GamesController@getPicsGames');
    Route::post('/twopicsgame', 'GamesController@addPicsGame');
    Route::delete('/twopicsgame/{id}/delete', 'GamesController@deleteTwoPicsGame');
    Route::post('/twopicsgame/update', 'GamesController@updateTwoPicsGame');

    Route::get('/picture/game/new', 'GamesController@getNewPicGame');
    Route::post('/picture/game/save', 'GamesController@saveSolvedPicGame');

    // Spot the difference
    Route::get('/spotdifference/all', 'GamesController@getSpotDifferenceGames');
    Route::post('/spotdifference/add', 'GamesController@addSpotDifferenceGame');
    Route::delete('/spotdifference/{id}/delete', 'GamesController@deleteSpoDifferenceGame');
    Route::post('/spotdifference/update', 'GamesController@updateSpotDifferenceGame');

    Route::get('/spotdifference/new', 'GamesController@getNewSpotDifferenceGame');
    Route::post('/spotdifference/save', 'GamesController@saveSpotDifferenceResponse');

    // Leaderboard
    Route::get('/leaderboard', 'GamesController@leaderboard');
    Route::get('/leaderboard/school/{id}', 'GamesController@school_leaderboard');

    //Grade
    Route::resource('/grades', 'GradeController');

    //Subject
    Route::resource('/subjects', 'SubjectController')->middleware('jwt.auth');
    Route::get('/grade/{id}/subjects', 'SubjectController@getGradeSubjects')->middleware('jwt.auth');

    //Topic
    Route::resource('/topics', 'TopicController');

    //Subtopic
    Route::resource('/subtopics', 'SubtopicController');

    //Test
    Route::resource('/tests', 'TestController');
    Route::post('get-test-question', 'TestController@getQuestions');

    Route::get('/test-categories', 'TestController@testCategories');

    //Question
    Route::resource('/questions', 'QuestionController');
    Route::get('/questions-by-user', 'QuestionController@questionsByUser');

    //Users
    Route::resource('users', 'UserController');
    Route::get('system-users', 'UserController@system_users');
    Route::get('app-users', 'UserController@app_users');
    Route::post('user/add', 'UserController@store');
    Route::delete('user/{id}/delete', 'UserController@destroy');
    Route::get('block/{id}', 'UserController@block_user');
    Route::get('unblock/{id}', 'UserController@unblock_user');

    Route::delete('user/{id}', 'UserController@deleteAccount');

    //Roles & permissions
    Route::resource('roles', 'RoleController');
    // Route::get('role/permission/{id}', 'RoleController@rolePermissions')->name('role.permission');
    // Route::post('role/set_permission', 'RoleController@setPermission')->name('role.setPermission');

    Route::group(['prefix' => 'books'], function() {
        Route::get('/all_books', 'bookcontroller@all_books');
        Route::post('/add/book', 'bookcontroller@add_book');
        Route::post('/delete/{id}', 'bookcontroller@delete_book');
        Route::post('/suspend/{id}', 'bookcontroller@suspend_book');
        Route::post('/unsuspend/{id}', 'bookcontroller@unsuspend_book');
        Route::post('/edit/book/{isn_no}', 'bookcontroller@edit_book');
        Route::get('/total_books', 'bookcontroller@total_books');
    });
    Route::get('/total_questions', 'QuestionController@total_questions');
    Route::get('/total_tests', 'QuestionController@total_tests');
    Route::get('/total_users', 'HomeController@getAppUsers');
    Route::get('/user_data_rates', 'HomeController@getUserRegistrationRate');
    Route::get('/total_schools', 'HomeController@getSchoolsCount');
    Route::get('/registered_questions', 'QuestionController@all_questions');
    Route::get('/generate/questions', 'GeneratedQuestionsController@generated_questions');
    Route::get('/system/test', 'GeneratedQuestionsController@get_generated_questions');
    Route::post('/edit/school', 'schoolcontroller@edit_school');
    Route::post('/suspend/school/{id}', 'schoolcontroller@suspend_school');
    Route::post('/assign/code/school', 'schoolcontroller@assignCode');
    Route::delete('/delete/school/{id}', 'schoolcontroller@delete_school');
    Route::get('/all_schools', 'schoolcontroller@all_schools');
    Route::group(['prefix' => 'school'], function() {
        Route::get('/dashboard_data', );
        Route::get('/users', 'schoolcontroller@school_data');
        Route::post('/add/class', 'schoolcontroller@add_class');
        Route::delete('/class/{id}/delete', 'schoolcontroller@delete_class');
        Route::post('/add/stream', 'schoolcontroller@add_stream');
        Route::get('/grades', 'schoolcontroller@all_grades');
        Route::get('/grades/{id}','schoolcontroller@get_grade');
        Route::get('/grades/{id}/streams','schoolcontroller@get_grade_streams');
        Route::get('/grade/{id}/students', 'schoolcontroller@getGradeStudents');
        Route::get('/teachers','schoolcontroller@all_teachers');
        Route::post('/teacher/subjects','schoolcontroller@all_teacher_subject');
        Route::get('/teacher/streams','schoolcontroller@all_teacher_streams');
        Route::get('/teacher/clubs','schoolcontroller@all_teacher_clubs');
        Route::post('/add/teacher','schoolcontroller@add_teacher');
        Route::post('/stream/add/teacher','schoolcontroller@add_teacher_to_Stream');
        Route::get('/tests', 'schoolcontroller@get_tests');
        Route::get('/count/tests', 'schoolcontroller@count_tests');
        Route::get('/clubs', 'schoolcontroller@get_clubs');
        Route::get('/club/{id}', 'schoolcontroller@get_club');
        Route::get('/clubs/teacher', 'schoolcontroller@get_clubs_teacher');
        Route::post('/add/club', 'schoolcontroller@add_club');
        Route::post('/add/club/activity', 'schoolcontroller@add_club_activity');
        Route::post('/reassign', 'schoolcontroller@reassign_teacher');
        // Route::get('/performance/week', 'schoolcontroller@week');
    });
    Route::group(['prefix' => 'teachers'], function() {
        Route::get('/questions', 'TeachersController@all_questions');
        Route::get('/grade', 'TeachersController@all_grades');
        Route::post('/subjects/{grade_id}', 'TeachersController@get_subjects');
        Route::post('/topics/{subject_id}', 'TeachersController@get_topics');
        Route::post('/subtopics/{topic_id}', 'TeachersController@get_subtopics');
        Route::post('/filter/questions', 'TeachersController@filter_questions');
        Route::post('/create/exam', 'TeachersController@create_exam');
        Route::get('/tests', 'TeachersController@get_tests');
    });

    // Pozyy Tv
    Route::prefix('pozyy/tv')->group(function () {
        Route::get('/all', [PozyyTvController::class, 'getVideos']);
        Route::get('/{id}', [PozyyTvController::class, 'getVideo']);
        Route::post('/add', [PozyyTvController::class, 'adminAddVideo']);
        Route::post('/{id}/update', [PozyyTvController::class, 'adminUpdateVideo']);
        Route::delete('/{id}/delete', [PozyyTvController::class, 'delete']);
    });

    // Payments
    Route::get('/payments', PaymentsController::class);
});

//Jambopay
Route::post('jambopay/pay', [JambopayPaymentController::class, 'getAccessToken'])->name('jambopay.checkout');
Route::post('/jambopay/callback', [JambopayPaymentController::class, 'callback'])->name('jambopay.callback');
Route::post('/jambopay/cancel', function() {
    return view('jambopay-cancel');
})->name('jambopay.cancel');

Route::post('/add/school', 'schoolcontroller@add_school');

Route::prefix('admin_questions')->group(function() {
    Route::get('/today', 'QuestionController@today_quiz');
    Route::get('/week', 'QuestionController@week_quiz');
    Route::get('/month', 'QuestionController@month_quiz');
});
Route::post('/edit/{id}/topic', 'TopicController@edit_topic');
Route::post('/edit/{id}/subtopic', 'SubtopicController@edit_subtopic');

Route::get('/performance/week', 'schoolcontroller@week');

Route::get('/mobile/media/sections', 'MobileMediaController@getSections');
Route::get('/mobile/media/section/{name}', 'MobileMediaController@getThumbnail');
Route::get('/mobile/media/sections/thumbnails', 'MobileMediaController@getSectionThumbnails');
Route::post('/mobile/media/thumbnail/update', 'MobileMediaController@updateThumbnail');

Route::get('/banks', [\App\Http\Controllers\schoolcontroller::class, 'banks']);
