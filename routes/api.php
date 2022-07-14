<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CityController;
use App\Models\CityModel;

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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/address',[App\Http\Controllers\AddressController::class,'foo']);



Route::post('/auth/login', [App\Http\Controllers\Auth\LoginController::class,'login']);
Route::post('/auth/refresh', [App\Http\Controllers\Auth\LoginController::class,'refresh']);
Route::post('/auth/register', [App\Http\Controllers\Auth\LoginController::class,'register']);

Route::get('/auth/user-profile', [App\Http\Controllers\Auth\LoginController::class,'profile']);


Route::get('city/all_cities', [App\Http\Controllers\CityController::class,'setAllCitys']);
// Route::group(['prefix' => 'oauth'
//     ], function () {
//         Route::post('/login', ['App\Http\Controllers\Auth\AuthController','login']);
//         Route::post('/register', ['App\Http\Controllers\Auth\AuthController','register']);
 
//  Route::post('/refresh', [LoginController::class, 'refresh'])->name('refresh');

 
//     Route::group(['middleware' => 'auth:api'], function() {
//  Route::get('logout', 'Auth\AuthController@logout');
//  Route::get('user', 'Auth\AuthController@user');
//  });
//});


Route::get('/ping', function () {
    return 'Hi';
});

