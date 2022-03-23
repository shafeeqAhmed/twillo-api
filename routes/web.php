<?php

use Illuminate\Support\Facades\Route;
use Twilio\Rest\Client;
use Carbon\Carbon;
use App\Http\Traits\CommonHelper;
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



Route::get('helper-check', function () {
    $encodedMessage = CommonHelper::filterAndReplaceLink([
        'message' => 'here is the link please follow the link ',
        'receiver_id' => 30,
        'influencer_id' => 13
    ]);

    dd($encodedMessage);
});


Route::get('info', function () {
    phpinfo();
});

Route::get('/topUser/{co}', [\App\Http\Controllers\Api\FilterController::class, 'findTopUsers']);
Route::get('/send-message', [\App\Http\Controllers\Api\FilterController::class, 'testMessage']);
Route::get('/hook', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'twilioFeedback']);
Route::get('/link', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'generateSignUplink']);
Route::get('/pass', function () {
    return \Illuminate\Support\Facades\Hash::make(123456);
});
Route::get('/send-message', function () {
    $sid = config('general.twilio_sid');
    $token = config('general.twilio_token');
    $client = new Client($sid, $token);



    // $services = $client->messaging->v1->services('MG79f1e88db7e7bb1f3a10da276c895b1e')
    //     ->delete();


    // $service = $client->messaging->v1->services
    //     ->create('scheduled_message', [
    //         "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"
    //     ]);

    // dd($service);

    // $services = $client->messaging->v1->services
    //     ->read(20);
    // dd($services[0]);

    $data =  [
        "body" => 'custom test msg',
        // "from" =>  '+18706176205',
        "from" =>  '+19289854272',
        "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"
    ];
    $data['sendAt'] = Carbon::now()->addMinute(65)->toIso8601String();
    $data['scheduleType'] = 'fixed';
    // dd($data, Carbon::now()->toIso8601String(), Carbon::now()->addMinute(15)->toIso8601String());

    $result = $client->messages->create('+12087792017', $data);
    dd($result, $result->status);
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
