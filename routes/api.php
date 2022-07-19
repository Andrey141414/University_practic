<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\mailController;
use App\Models\CityModel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use App\Mail\testMailClass;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/address',[App\Http\Controllers\AddressController::class,'foo']);



Route::post('/auth/login', [App\Http\Controllers\Auth\LoginController::class,'login']);
Route::post('/auth/refresh', [App\Http\Controllers\Auth\LoginController::class,'refresh']);
Route::post('/auth/registr', [App\Http\Controllers\Auth\LoginController::class,'registr']);

Route::get('/auth/user-profile', [App\Http\Controllers\Auth\LoginController::class,'profile']);


Route::get('/city/all_cities', [App\Http\Controllers\CityController::class,'setAllCitys']);

Route::post('/load_image', [App\Http\Controllers\ImageController::class,'load_image']);


//почта
Route::get('/sent_code', [App\Http\Controllers\mailController::class,'sentMail']);
Route::post('/check_code', [App\Http\Controllers\mailController::class,'checkMail']);
//


Route::get('/ping', function () {
    return auth('api')->user()->id;
})->name('ping');

//Route::get('/profile',[App\Http\Controllers\Auth\AuthController::class,'getIdByAccessToken']);

Route::post('/callback', function(Request $request){
    //$input = $request::all();
    return response()->json($request);
});
