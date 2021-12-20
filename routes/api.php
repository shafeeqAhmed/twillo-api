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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);

// Route::post('email/verification-notification', [\App\Http\Controllers\Api\AuthController::class, 'resendVerificationEmail'])->middleware(['auth:sanctum', 'throttle:6,1']);
// Route::post('/email/verify/{id}/{hash}', [\App\Http\Controllers\Api\AuthController::class, 'VerificationEmail']);
Route::post('/forgot-password', [\App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [\App\Http\Controllers\Api\AuthController::class, 'resetPassword']);
// Route::post('/update-profile', [\App\Http\Controllers\Api\UserController::class, 'updateProfile']);
Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware(['auth:sanctum']);


Route::get('buy-twillio-numbers/{no}/{country_id}', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'purchaseTwillioNumbers']);
Route::get('users', [\App\Http\Controllers\Api\UserController::class, 'userList'])->middleware(['auth:sanctum']);
Route::get('users/{user_uuid}', [\App\Http\Controllers\Api\UserController::class, 'getUserDetail']);

Route::get('my-detail', [\App\Http\Controllers\Api\UserController::class, 'myDetail'])->middleware(['auth:sanctum']);
Route::post('create-influencer', [\App\Http\Controllers\Api\InfluencerController::class, 'createInfluencer']);

Route::post('update-influencer', [\App\Http\Controllers\Api\InfluencerController::class, 'updateInfluencer']);
Route::get('get-influencers', [\App\Http\Controllers\Api\UserController::class, 'getInfluencersList']);
Route::get('get-influencers-dropdowns', [\App\Http\Controllers\Api\DropDownController::class, 'getCountriesTWillioNumbers']);


Route::post('msg-tracking', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'msgTracking']);

Route::post('upload-single-file', [\App\Http\Controllers\Api\MediaController::class, 'uploadSingleFile']);
Route::post('update-profile', [\App\Http\Controllers\Api\UserController::class, 'updateProfile']);
