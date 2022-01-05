<?php

namespace App\Http\Controllers\Api;


use App\Models\FanClub;
use Illuminate\Http\Request;
use App\Models\TwilioNumbers;
use Twilio\Rest\Client;
use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\ChatUsers;
use App\Models\Messages;
use Illuminate\Support\Str;
use App\Http\Resources\ChatUserResource;

class TwilioNumbersController extends ApiController
{
    private $client;

    public function __construct()
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);
    }

    public function getTwillioNumbers($nosToBuy, $country_id)
    {

        $country = Country::find($country_id);
        return $this->client->availablePhoneNumbers($country->country_sort_name)->local->read([], $nosToBuy);
    }


    public function purchaseTwillioNumbers($nosToBuy, $country_code)
    {
        $twilioPhoneNumbers = $this->getTwillioNumbers($nosToBuy, $country_code);
        $data = array();
        for ($loop = 0; $loop < $nosToBuy; $loop++) {
            $data['number'] = $this->buy($twilioPhoneNumbers[$loop]->phoneNumber);
        }

        return $this->respond([
            'data' => $data
        ]);
    }

    public function buy($twilioPhoneNumber)
    {

        //     $this->client->incomingPhoneNumbers->create(
        //                ['phoneNumber' => $twilioPhoneNumber]
        //            );
        sleep(2);

        //      TwilioNumbers::create([
        //            'phone_no' => $twilioPhoneNumber,
        //            'status' => 'active'
        //        ]);


        return $twilioPhoneNumber;
    }

    public function msgTracking(Request $request)
    {
        $from = User::where('user_uuid', $request->uuid)->value('phone_no');
        $messages = $this->client->messages
            ->read(
                [
                    "from" => $from,
                ],
                20
            );

        $data['total_messages'] = count($messages);
        $message_history = array();
        foreach ($messages as $index => $record) {

            $mess = $this->client->messages($record->sid)
                ->fetch();
            $message_history['to'][$index] = $mess->to;
            $message_history['body'][$index] = $mess->body;
            $message_history['status'][$index] = $mess->status;
        }


        $data['message_history'] = $message_history;


        return $this->respond([
            'data' => $data
        ]);
    }


    public function twilioWebhook()
    {
        //temp_id
        $uuid = \Illuminate\Support\Str::uuid()->toString();
        //user_id of twillio number
        $user = User::where('phone_no','03077020163')->first();
        //local number
        $local_number = '03077020163';
        //insert into fan_club
            //send message from twillio number to local with signup link and welcome
            //if fan signup it will become know contact

        $input = (file_get_contents('php://input'));
        DB::table('twilio_response')->insert([
            'body_' => $input
        ]);
    }
    public function insertInFanClub($influencer_id,$fan_phon_number,$uuid) {
//        FanClub::create();
    }
    public function generateSignUplink($temp_id) {
        $url = config('general.front_app_url').'/account/register?id='.$temp_id;
        return $url;
    }
    public function twilioFeedback()
    {
        $input = DB::table('twilio_response')->get();

        $input = $input->toArray();
        foreach ($input as $key => $value) {


            echo '<pre>';
            $to = explode('&', $value->body_)[3];

            $to = explode('=', $to);
            //echo '<br>'.$to[1];


            $from = explode('&', $value->body_)[6];
            $from = explode('=', $from);
            //echo '<br>'.$from[1];


            $msg_id = explode('&', $value->body_)[4];
            $msg_id = explode('=', $msg_id);
            echo '<br>' . $msg_id[1];

            echo '<br>';

            $mess = $this->client->messages($msg_id[1])
                ->fetch();
            echo '<br>to= ' . $mess->to;
            echo '<br>from= ' . $mess->from;
            echo '<br>body= ' . $mess->body;


        }

    }


}
