<?php

namespace App\Http\Controllers\Api;

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

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware(['auth:sanctum', 'throttle:6,1']);
// Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'VerificationEmail']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [AuthController::class, 'resetPassword']);
// Route::post('/update-profile', [UserController::class, 'updateProfile']);



Route::get('buy-twillio-numbers/{no}/{country_id}/{state}', [TwilioNumbersController::class, 'purchaseTwillioNumbers']);

Route::get('users/{user_uuid}', [UserController::class, 'getUserDetail']);


Route::post('create-influencer', [InfluencerController::class, 'createInfluencer']);

Route::post('update-influencer', [InfluencerController::class, 'updateInfluencer']);
Route::get('get-influencers', [UserController::class, 'getInfluencersList']);
Route::get('get-influencers-dropdowns', [DropDownController::class, 'getCountriesTWillioNumbers']);


Route::post('msg-tracking', [TwilioNumbersController::class, 'msgTracking']);

Route::post('upload-single-file', [MediaController::class, 'uploadSingleFile']);
Route::post('update-profile', [UserController::class, 'updateProfile']);



//chat routes



Route::post('twilio_webhook', [TwilioNumbersController::class, 'twilioWebhook']);
Route::post('incomming_message_webhook', [TwilioNumbersController::class, 'inCommingMessageWebhook']);

Route::get('twilio_feedback', [TwilioNumbersController::class, 'twilioFeedback']);


Route::get('port', [TwilioChatController::class, 'Port']);


Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('users', [UserController::class, 'userList']);

    Route::get('my-detail', [UserController::class, 'myDetail']);
    Route::post('get-filter-member-count', [FilterController::class, 'getFilterMemberCount']);


    Route::post('sms_service', [TwilioChatController::class, 'smsService']);

    Route::get('get_chat_users/{id}', [TwilioChatController::class, 'getChatMessages']);

    Route::get('get_chat_contacts', [TwilioChatController::class, 'getInfluencerContacts']);
    Route::get('get_influencer_dashboard_info', [UserController::class, 'getInfluencerDashboardInfo']);


    Route::get('recipent_count', [FilterController::class, 'recipientsCount']);

    Route::get('age_filter/{type}/{date1}/{date2?}', [FilterController::class, 'ageFilter']);


    Route::get('duration_filter', [FilterController::class, 'durationFilter']);

    Route::get('get_fan_by_date/{date}/{type}', [FilterController::class, 'getFanByDate']);
    Route::post('send_message_to_contacts', [FilterController::class, 'sendMessageToContacts']);

    Route::get('age-group-stats', [StatsController::class, 'getAgeGroupStats']);
    Route::get('gender-group-stats', [StatsController::class, 'getGenderGroupStats']);
    Route::get('city-group-stats', [StatsController::class, 'getCityGroupStats']);
    Route::get('country-group-stats', [StatsController::class, 'getCountryGroupStats']);
    Route::get('monthly-registration-group-stats', [StatsController::class, 'getMontyRegistrationStats']);
    Route::get('average-click-rate', [StatsController::class, 'averageClickRate']);
    Route::get('average-response-rate', [StatsController::class, 'averageResponseRate']);
    Route::get('fan-reach', [StatsController::class, 'fanReach']);
    Route::get('top-active-contact', [StatsController::class, 'topActiveContact']);
    Route::get('top-in-active-contact', [StatsController::class, 'topInActiveContact']);
    Route::get('no-of-text', [StatsController::class, 'noOfText']);
    Route::get('no-of-contact', [StatsController::class, 'noOfContact']);
    Route::get('broad-cast-messages', [StatsController::class, 'broadCastMessages']);
    Route::get('broad-cast-messages-list', [StatsController::class, 'broadCastMessagesList']);
    Route::post('send-follow-up-message', [FilterController::class, 'sendFollowUpMessage']);
});

Route::get('is-valid-reference/{reference}', [UserController::class, 'isValidReference']);
