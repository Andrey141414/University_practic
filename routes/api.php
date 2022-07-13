<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/address',[App\Http\Controllers\AddressController::class,'foo']);



Route::post('/oauth/token', [LoginController::class, 'token'])->name('token');
       
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

