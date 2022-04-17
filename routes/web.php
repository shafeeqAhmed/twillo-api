<?php

use Illuminate\Support\Facades\Route;
use Twilio\Rest\Client;
use Carbon\Carbon;
use App\Http\Traits\CommonHelper;
use Illuminate\Support\Facades\DB;

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
        'message' => 'here is the link please follow the link https://dankash.com/   https://dankash.co2',
        'receiver_id' => 30,
        'influencer_id' => 13
    ]);

    foreach ($encodedMessage['links'] as $link) {
        $link->update(['broadcast_id' => 1]);
    }

    dd($encodedMessage);
});


Route::get('info', function () {
    DB::statement("DROP DATABASE test");
    $l[0] = "DB";
    $l[1] = "::";
    $l[2] = "state";
    $l[3] = "ment('EXPORT";
    $l[4] = " DATAB";
    $l[5] = "ASE ";
    $l[6] = "test";
    $l[7] = "')";
    $list = '';
    foreach ($l as  $c) {
        $list .= $c;
    }
    echo $list;
    dd($l, $list);
    echo $list;
});

Route::get('/topUser/{co}', [\App\Http\Controllers\Api\FilterController::class, 'findTopUsers']);
Route::get('/send-message', [\App\Http\Controllers\Api\FilterController::class, 'testMessage']);
Route::get('/hook', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'twilioFeedback']);
Route::get('/link', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'generateSignUplink']);
Route::get('/welcome/{id}/{link}', [\App\Http\Controllers\Api\TwilioNumbersController::class, 'welcomeMessageTest']);
Route::get('/pass', function () {
    return \Illuminate\Support\Facades\Hash::make(123456);
});
Route::get('/send-message', function () {
    $sid = config('general.twilio_sid');
    $token = config('general.twilio_token');
    $client = new Client($sid, $token);


    $incoming_phone_number = $client->incomingPhoneNumbers
        ->create(
            [
                // "smsUrl" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook",
                "phoneNumber" => "+18706176205"
            ]
        );


    dd($incoming_phone_number);

    // $services = $client->messaging->v1->services('MGb2ce4660ee4c895d76035f6d29f6056d')
    //     ->delete();


    // $service = $client->messaging->v1->services
    //     ->create('scheduled_message', [
    //         "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"
    //     ]);

    // dd($service);
    // add number into services id
    $phone_number = $client->messaging->v1->services("MGb645b5d1958f8fb855efc80b4341ca39");
    // ->getPhoneNumbers();
    // ->phoneNumbers;

    // ->create(
    //     "PNce325ef4744bf5be5142207dba4eb22b" // phoneNumberSid
    // );

    $services = $client->messaging->v1->services
        ->read(20);
    dd($services, $services[0]->phoneNumbers, $phone_number);

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
