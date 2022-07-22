<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
// header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
//header('Access-Control-Allow-Origin: *');

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/address',[App\Http\Controllers\AddressController::class,'foo']);

//Роуты авторизации
Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () {
    Route::post('/auth/login', 'login');
    Route::post('/auth/refresh','refresh');
    Route::post('/auth/registr', 'registr');
    Route::get('/auth/user-profile','profile')->middleware('onlyAuthorized');
});

//почта
Route::controller(App\Http\Controllers\mailController::class)->group(function () {
    Route::get('/sent_code', 'sentMail')->middleware('onlyAuthorized');
    Route::post('/check_code', 'checkMail')->middleware('onlyAuthorized');
});

//Лента
Route::controller(App\Http\Controllers\tapeController::class)->group(function () {
    Route::get('category/get_category', 'getCategory');
    Route::get('city/all_cities', 'setAllCitys');
    Route::post('add_category_to_db', 'addCategoryToDb')->middleware('onlyAuthorized');
    //
});

//фотографии
Route::controller(App\Http\Controllers\postController::class)->group(function () {
    Route::post('/create_post', 'createPost')->middleware('onlyAuthorized');
    Route::delete('/delete_post', 'deletePost')->middleware('onlyAuthorized');
    Route::patch('/change_post', 'changePost')->middleware('onlyAuthorized');
    Route::get('/get_post', 'getPost');
    Route::get('/my_posts', 'myPosts');
    //
});



//











Route::get('/ping', [App\Http\Controllers\userController::class,'test'])->name('ping');

Route::post('/callback', function(Request $request){
    
    
    
    return response()->json($request);
});


Route::controller(App\Http\Controllers\passwordController::class)->group(function () {
    Route::post('/send_password_reset_token', 'sendPasswordResetToken');
    Route::post('/is_valid_token', 'isshowPasswordResetForm')->name('showPasswordResetForm');
    Route::post('/password_reset', 'resetPassword');
});

// Route::get('password-reset', 'PasswordController@showForm'); //I did not create this controller. it simply displays a view with a form to take the email
// Route::post('password-reset', 'PasswordController@sendPasswordResetToken');
// Route::get('reset-password/{token}', 'PasswordController@showPasswordResetForm');
// Route::post('reset-password/{token}', 'PasswordController@resetPassword');