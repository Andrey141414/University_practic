<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
header("Set-Cookie: cross-site-cookie=whatever; SameSite=None; Secure");

//Роуты авторизации
Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () {
    Route::post('/auth/login', 'login');
    Route::get('/auth/login', 'login')->name('api/auth/login');
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
    Route::get('category/all_categories', 'getAllCategory');
    Route::get('city/all_cities', 'getAllCitys');
    Route::post('add_category_to_db', 'addCategoryToDb')->middleware('onlyAuthorized');
    //
});

//фотографии и посты 

Route::controller(App\Http\Controllers\postController::class)->group(function () {
    
    Route::get('/similar_posts', 'similarPosts');
    Route::post('/create_post1', 'createPost');
    Route::post('/create_post', 'createPost')->middleware('onlyAuthorized');
    Route::delete('/delete_post', 'deletePost')->middleware('onlyAuthorized');
    Route::patch('/change_post', 'changePost')->middleware('onlyAuthorized');

    Route::get('/get_post', 'getPost');
    Route::get('/get_post_for_change','getPostForChange')->middleware('onlyAuthorized');
    Route::get('/get_post_likes', 'favoritePostsCount')->middleware('onlyAuthorized');
    Route::get('/my_posts', 'userPostsData')->middleware('onlyAuthorized');
    Route::get('/all_posts', 'allPostsData');

    Route::get('/user_posts', 'getUserPosts');

    Route::get('get_phone_number','getPhoneNumber')->middleware('onlyAuthorized');
    Route::get('get_address','getAddress')->middleware('onlyAuthorized');
    Route::get('get_contact','getContact')->middleware('onlyAuthorized');
    
    //
    Route::patch('change_post_active','changePostActive')->middleware('onlyAuthorized');

    Route::get('load_preview_to_heroku','loadPreviewToHeroku');

    //тест
    Route::get('delete_all_posts_from_heroku','loadPreviewToHerokuTest');
});



//любимые посты

Route::controller(App\Http\Controllers\favoritePostsController::class)->group(function () {
    Route::post('/add_post_to_favorive', 'addPostToFavorive')->middleware('onlyAuthorized');
    Route::delete('/delete_post_from_favorite', 'deletePostFromFavorite')->middleware('onlyAuthorized');
    Route::get('/all_favorite_posts', 'allFavoritePosts')->middleware('onlyAuthorized');
    Route::get('/all_favorite_posts_id', 'allFavoritePostsID')->middleware('onlyAuthorized');
});



Route::controller(App\Http\Controllers\AddressController::class)->group(function () {
    Route::post('/add_new_address', 'addNewAddress')->middleware('onlyAuthorized');
    Route::delete('/delete_address', 'deleteAddress')->middleware('onlyAuthorized');
    Route::patch('/change_address', 'changeAddress')->middleware('onlyAuthorized');
    Route::get('/is_posts_for_address', 'isPostsForAddress')->middleware('onlyAuthorized');
});






Route::post('/ping', [App\Http\Controllers\userController::class,'test'])->name('ping');
Route::delete('/delete_account', [App\Http\Controllers\userController::class,'deleteAccount'])->middleware('onlyAuthorized');

Route::controller(App\Http\Controllers\passwordController::class)->group(function () {
    Route::post('/send_password_reset_token', 'sendPasswordResetToken');
    Route::post('/is_valid_token', 'isshowPasswordResetForm')->name('showPasswordResetForm');
    Route::post('/password_reset', 'resetPassword');
});


Route::controller(App\Http\Controllers\reviewController::class)->group(function () {
    Route::post('/create_review', 'createReviews');
    Route::get('/get_user_reviews', 'getReviews');
    Route::get('/get_my_reviews', 'getMyReviews');
});

Route::post('/callback', function(Request $request){
    return response()->json($request);
});
