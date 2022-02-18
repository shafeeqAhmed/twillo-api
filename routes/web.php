<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/





Route::get('/topUser/{co}', [\App\Http\Controllers\Api\FilterController::class, 'findTopUsers']);
Route::get('/send-message', [\App\Http\Controllers\Api\FilterController::class, 'testMessage']);
Route::get('/hook', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'twilioFeedback']);
Route::get('/link', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'generateSignUplink']);
Route::get('/pass', function () {
    return \Illuminate\Support\Facades\Hash::make(123456);
});
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('redirect_url', [\App\Http\Controllers\Api\LinksController::class, 'redirectUrl'])->name('count_and_redirect');


Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cleared!";
});
