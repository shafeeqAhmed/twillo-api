<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

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

Broadcast::routes(['middleware' => ['auth:sanctum']]);


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



Route::get('buy-twillio-numbers/{no}/{country_id}/{state}', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'purchaseTwillioNumbers']);

Route::get('users/{user_uuid}', [\App\Http\Controllers\Api\UserController::class, 'getUserDetail']);


Route::post('create-influencer', [\App\Http\Controllers\Api\InfluencerController::class, 'createInfluencer']);

Route::post('update-influencer', [\App\Http\Controllers\Api\InfluencerController::class, 'updateInfluencer']);
Route::get('get-influencers', [\App\Http\Controllers\Api\UserController::class, 'getInfluencersList']);
Route::get('get-influencers-dropdowns', [\App\Http\Controllers\Api\DropDownController::class, 'getCountriesTWillioNumbers']);


Route::post('msg-tracking', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'msgTracking']);

Route::post('upload-single-file', [\App\Http\Controllers\Api\MediaController::class, 'uploadSingleFile']);
Route::post('update-profile', [\App\Http\Controllers\Api\UserController::class, 'updateProfile']);



//chat routes



Route::post('twilio_webhook', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'twilioWebhook']);

Route::get('twilio_feedback', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'twilioFeedback']);


Route::get('port', [\App\Http\Controllers\Api\TwilioChatController::class, 'Port']);


Route::group(['middleware' => 'auth:sanctum'], function () {

Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

Route::get('users', [\App\Http\Controllers\Api\UserController::class, 'userList']);

Route::get('my-detail', [\App\Http\Controllers\Api\UserController::class, 'myDetail']);
Route::post('get-filter-member-count', [\App\Http\Controllers\Api\FilterController::class, 'getFilterMemberCount']);


Route::post('sms_service', [\App\Http\Controllers\Api\TwilioChatController::class, 'smsService']);

Route::get('get_chat_users/{id}', [\App\Http\Controllers\Api\TwilioChatController::class, 'getChatMessages']);

Route::get('get_chat_contacts', [\App\Http\Controllers\Api\TwilioChatController::class, 'getInfluencerContacts']);
Route::get('get_influencer_dashboard_info', [\App\Http\Controllers\Api\UserController::class, 'getInfluencerDashboardInfo']);


Route::get('recipent_count', [\App\Http\Controllers\Api\FilterController::class, 'recipientsCount']);

Route::get('age_filter/{type}/{date1}/{date2?}', [\App\Http\Controllers\Api\FilterController::class, 'ageFilter']);


Route::get('duration_filter', [\App\Http\Controllers\Api\FilterController::class, 'durationFilter']);

Route::get('get_fan_by_date/{date}/{type}', [\App\Http\Controllers\Api\FilterController::class, 'getFanByDate']);
Route::post('send_message_to_contacts', [\App\Http\Controllers\Api\FilterController::class, 'sendMessageToContacts']);

});

Route::get('is-valid-reference/{reference}', [\App\Http\Controllers\Api\UserController::class, 'isValidReference']);



