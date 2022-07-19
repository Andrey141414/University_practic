<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\ImageController;
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
    Route::get('category/get_category', 'getCategory')->middleware('onlyAuthorized');
    Route::get('city/all_cities', 'setAllCitys')->middleware('onlyAuthorized');
    Route::post('add_icon_to_db', 'addIconToDb')->middleware('onlyAuthorized');
    //
});

//





Route::post('/load_image', [App\Http\Controllers\ImageController::class,'load_image']);





Route::get('/ping', function () {
    return auth('api')->user()->id;
})->name('ping');

Route::post('/callback', function(Request $request){
    
    
    
    return response()->json($request);
});
