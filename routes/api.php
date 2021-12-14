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

Route::get('users', [\App\Http\Controllers\Api\UserController::class, 'userList'])->middleware(['auth:sanctum']);
Route::get('user-detail/{id}', [\App\Http\Controllers\Api\UserController::class, 'getUserDetail']);
Route::get('my-detail', [\App\Http\Controllers\Api\UserController::class, 'myDetail'])->middleware(['auth:sanctum']);

