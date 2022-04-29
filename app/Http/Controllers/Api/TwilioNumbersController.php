<?php

namespace App\Http\Controllers\Api;


use App\Models\FanClub;
use Illuminate\Http\Request;
use App\Models\TwilioNumbers;
use Twilio\Rest\Client;
use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Messages;
use Illuminate\Support\Str;
use App\Events\ChatEvent;
use App\Jobs\SendTextMessage;
use App\Models\AutoMessage;
use App\Models\Setting;
use App\Models\PersonalSetting;

class TwilioNumbersController extends ApiController
{
    private $client;

    public function __construct()
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);
    }
    public function getTwillioNumbers($nosToBuy, $country_id, $state)
    {

        $region = [];
        if ($state != '' && $state != 0) {
            $region = ["inRegion" => $state];
        }
        $country = Country::find($country_id);

        if ($country->country_sort_name == 'GB') {
            $res = $this->client->availablePhoneNumbers($country->country_sort_name)->mobile->read([], $nosToBuy);
        } else {
            $res = $this->client->availablePhoneNumbers($country->country_sort_name)->local->read($region, $nosToBuy);
        }
        return $res;
    }

    public function purchaseTwillioNumbers($nosToBuy, $country_code, $state)
    {
        try {
            $twilioPhoneNumbers = $this->getTwillioNumbers($nosToBuy, $country_code, $state);

            $data = array();
            $address_id = 0;

            if ($twilioPhoneNumbers[0]->addressRequirements != 'none') {
                $address =  $this->client->addresses
                    ->create(
                        $twilioPhoneNumbers[0]->friendlyName,
                        "123",
                        $twilioPhoneNumbers[0]->locality ?? 'California',
                        $twilioPhoneNumbers[0]->region ?? $twilioPhoneNumbers[0]->isoCountry,
                        $twilioPhoneNumbers[0]->postalCode ?? '00501',
                        $twilioPhoneNumbers[0]->isoCountry
                    );

                $address_id = $address->sid;
            }

            $data['number'] = $this->buy($twilioPhoneNumbers[0]->phoneNumber, $address_id);


            return $this->respond([
                'data' => $data,
                'status' => true,
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'data' => [],
                'status' => false,
            ]);
        }
    }

    public function buy($twilioPhoneNumber, $address_sid)
    {
        //  if($address_sid!=0){
        $this->client->incomingPhoneNumbers->create([
            'phoneNumber' => $twilioPhoneNumber,
            "smsUrl" => config('general.web_hook'),
            "addressSid" => $address_sid,
        ]);
        TwilioNumbers::create([
            'no' => $twilioPhoneNumber,
        ]);


        return $twilioPhoneNumber;
        //}
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


    public function inCommingMessageWebhook()
    {
        $input = (file_get_contents('php://input'));

        DB::table('twilio_response')->insert([
            'body_' => 'in-comming-message' . $input
        ]);
    }


    public function twilioWebhook()
    {
        $input = (file_get_contents('php://input'));

        DB::table('twilio_response')->insert([
            'body_' => $input
        ]);
        if (!empty($input)) {
            $this->twilioFeedback($input);
        }
    }

    public function insertInFanClub($influencer_id, $fan_phon_number, $uuid)
    {
        //        FanClub::create();
    }
    public function generateSignUplink($uuid)
    {
        $url = config('general.front_app_url') . '/account/register?id=' . $uuid;
        return $url;
    }
    public function twilioFeedback($input = '')
    {
        $data = explode('&', $input)[0];
        $data = explode('=', $data);

        if ($data[0] == 'ToCountry') {

            $record = explode('&', $input)[2];
            $record = explode('=', $record);

            $msg_id = $record[1];
        } else {
            $msg_id = explode('&', $input)[4];
            $msg_id = explode('=', $msg_id);
            $msg_id = $msg_id[1];
        }

        $mess = $this->client->messages($msg_id)
            ->fetch();
        //inbound mean received message from non twillo number
        // if (strtolower(trim($lookup, ' ')) != 'twilio') {
        //incoming messages
        if ($mess->direction == 'inbound') {

            $user = User::where('phone_no', $mess->to)->first();

            //check fan is blocked or not
            $fan_club_instance = FanClub::where('user_id', $user->id)->where('local_number', $mess->from)->first();
            if ($fan_club_instance && $fan_club_instance->is_blocked) {
                exit;
            }

            $sender = FanClub::where('is_active', 1)->where('user_id', $user->id)->where('local_number', $mess->from)->first();

            //new fan
            if (!$sender) {
                //generate temp id and send this via message
                $uuid = Str::uuid()->toString();

                //delete the previous token
                FanClub::deleteRecord(['is_active' => 0, 'local_number' => $mess->from, 'user_id' => $user->id]);

                // create new token
                FanClub::create([
                    'fan_club_uuid' => Str::uuid()->toString(),
                    'user_id' => $user->id,
                    'local_number' =>  $mess->from,
                    'fan_id' => 0,
                    'temp_id' => $uuid,
                    'is_active' => 0,
                    'temp_id_date_time' => date('Y-m-d H:i:s')
                ]);
                //generate a new messae with link to register as a fan from influencer 
                //welcome message

                // $message = 'Hey! This is an auto text to let you know I received your message, to join my colony and receive messages from me, please sign up by clicking the link:   ' . $this->generateSignUplink($uuid);
                $message = getWelcomeMessage($user->id, $this->generateSignUplink($uuid));
                $request_data['user'] = $user;
                $request_data['receiver_number'] = $mess->from;
                $request_data['receiver_id'] = $sender?->fan_id;
                dispatch(new SendTextMessage($message, $request_data));
            } else {
                //if fan already exist inside fan club

                //create pusher list for append message
                $message_record = [
                    'sms_uuid' => Str::uuid()->toString(),
                    'sender_id' => $sender->fan_id,
                    'receiver_id' => $user->id,
                    'message_id' => 0,
                    'message' => $mess->body,
                    'is_seen' => 0,
                    'created_at' => date('d-m-y'),
                    'timestamp' =>  date('Y-m-d H:i:s'),
                    'align' => '',
                    'direction' => $mess->direction,
                ];
                ChatEvent::dispatch($message_record);

                //update all the replies
                updateFanReplies($sender->fan_id, $user->id);
                //store message inside message table

                updateLocalMessage(
                    $sender->fan_id,
                    $user->id,
                    'receive',
                    $mess->body,
                    $mess->status,
                    null,
                    $mess->sid,
                    null,
                );
                //auto reply 
                $autoMessage = AutoMessage::where('keyword', $mess->body)->where('user_id', $user->id)->where('status', 1)->first();
                if ($autoMessage) {
                    $message = $autoMessage->text;
                    $request_data['user'] = $user;
                    $request_data['receiver_number'] = $mess->from;
                    $request_data['receiver_id'] = $sender?->fan_id;
                    dispatch(new SendTextMessage($message, $request_data));
                }
            }
        } else {
            // msg send by twilio update his status 
            Messages::updateData('twilio_msg_id', $mess->sid, ['status' => $mess->status]);
        }
    }
    public function welcomeMessageTest($user_id, $link)
    {
        dd(getSignupConfirmationMessage(14));
    }
    public function twilioFeedbackBackup()
    {


        $input = DB::table('twilio_response')->where('id', 9)->first();

        $data = explode('&', $input->body_)[0];
        $data = explode('=', $data);


        if ($data[0] == 'ToCountry') {

            $record = explode('&', $input->body_)[2];
            $record = explode('=', $record);

            $msg_id = $record[1];
        } else {
            $msg_id = explode('&', $input->body_)[4];
            $msg_id = explode('=', $msg_id);
            $msg_id = $msg_id[1];
        }



        $mess = $this->client->messages($msg_id)
            ->fetch();



        if ($mess->direction == 'outbound-api') {

            $fan_club = FanClub::where('is_active', 1)->where('local_number', $mess->from)->orWhere('local_number', $mess->to)->get();
            $wordCount = $fan_club->count();

            if ($wordCount == 0) {

                $uuid = \Illuminate\Support\Str::uuid()->toString();
                $user = User::where('phone_no', $mess->from)->first();


                if ($user->count() != 0) {


                    FanClub::create([
                        'fan_club_uuid' => 0,
                        'user_id' => $user->id,
                        'local_number' => $mess->to,
                        'fan_id' => 0,
                        'temp_id' => Str::uuid()->toString(),
                        'is_active' => 0,
                        'temp_id_date_time' => date('Y-m-d H:i:s')
                    ]);

                    $body = 'You are Welcome In Portal.To continue further please sign up from below link:   ' . $this->generateSignUplink($uuid);
                    $message = $this->client->messages
                        ->create(
                            $mess->to,
                            ["body" => $body, "from" =>  $mess->from, "statusCallback" => config('general.web_hook')]
                        );
                }
            }
        } else {
            $sender_id = User::where('phone_no', $mess->from)->first()->id;

            $receiver_id = User::where('phone_no', $mess->to)->first()->id;
            $message_record = [
                'sms_uuid' => Str::uuid()->toString(),
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'message_id' => 0,
                'message' => $mess->body,
                'is_seen' => 0,
                'created_at' => '12-2-2021',
                'align' => '',
                'direction' => 'inbound',

            ];


            ChatEvent::dispatch($message_record);
        }



        exit;

        $input = $input->toArray();


        foreach ($input as $key => $value) {

            $to = explode('&', $value->body_)[3];
            $to = explode('=', $to);

            $from = explode('&', $value->body_)[6];
            $from = explode('=', $from);

            $msg_id = explode('&', $value->body_)[4];
            $msg_id = explode('=', $msg_id);



            $mess = $this->client->messages($msg_id[1])
                ->fetch();
            echo '<br>';
            echo '<br>' . $value->id;
            echo '<br>to= ' . $mess->to;
            echo '<br>from= ' . $mess->from;

            $fan_club = FanClub::where('active', 1)->where(['from' => $from, 'to' => $to])->get();

            $messages = $this->client->messages
                ->read(
                    [
                        "from" => $mess->from,
                    ],
                    100
                );

            echo '<pre>';
            print_r(count($messages));
        }
    }
}
