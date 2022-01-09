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
use App\Events\ChatEvent;

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

        //   dd($twilioPhoneNumbers);
        $address_id=0;

        if($twilioPhoneNumbers[0]->addressRequirements!='none'){
            $address =  $this->client->addresses
                ->create($twilioPhoneNumbers[0]->friendlyName,
                    "123",
                    $twilioPhoneNumbers[0]->locality ?? 'California',
                    $twilioPhoneNumbers[0]->region ?? $twilioPhoneNumbers[0]->isoCountry,
                    $twilioPhoneNumbers[0]->postalCode ?? '00501',
                    $twilioPhoneNumbers[0]->isoCountry
                );

            $address_id=$address->sid;

        }

        $data['number'] = $this->buy($twilioPhoneNumbers[0]->phoneNumber,$address_id);
        /*for ($loop = 0; $loop < $nosToBuy; $loop++) {
            $data['number'] = $this->buy($twilioPhoneNumbers[$loop]->phoneNumber);
        }*/

        return $this->respond([
            'data' => $data
        ]);
    }

    public function buy($twilioPhoneNumber,$address_sid)
    {
        if($address_sid!=0){
            $this->client->incomingPhoneNumbers->create(
                [
                    'phoneNumber' => $twilioPhoneNumber,
                    "smsUrl" => "https://text-app.tkit.co.uk/api/api/twilio_webhook",
                    "addressSid" => $address_sid,

                ]
            );
        }else{
            $this->client->incomingPhoneNumbers->create(
                [
                    'phoneNumber' => $twilioPhoneNumber,
                    "smsUrl" => "https://text-app.tkit.co.uk/api/api/twilio_webhook",

                ]
            );
        }


        DB::table('twilio_numbers')->insert(
            ['no' => $twilioPhoneNumber]
        );

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
        $input = (file_get_contents('php://input'));

        DB::table('twilio_response')->insert([
            'body_' => $input
        ]);
        $this->twilioFeedback($input);
    }
    public function insertInFanClub($influencer_id,$fan_phon_number,$uuid) {
        FanClub::create();
    }
    public function generateSignUplink($uuid) {
        $url = config('general.front_app_url').'/account/register?id='.$uuid;
        return $url;
    }
    public function twilioFeedback($input = '')
    {
        //$input = 'ToCountry=US&ToState=GA&SmsMessageSid=SMfa9adb16915f1265fb74047f2c9451c4&NumMedia=0&ToCity=FITZGERALD&FromZip=&SmsSid=SMfa9adb16915f1265fb74047f2c9451c4&FromState=NY&SmsStatus=received&FromCity=Manhattan&Body=pkrt&FromCountry=US&To=%2B12293480700&ToZip=31750&AddOns=%7B%22status%22%3A%22successful%22%2C%22message%22%3Anull%2C%22code%22%3Anull%2C%22results%22%3A%7B%22message_tone%22%3A%7B%22request_sid%22%3A%22XR37139dd8624de15f2e846a8f509f4df0%22%2C%22status%22%3A%22successful%22%2C%22message%22%3Anull%2C%22code%22%3Anull%2C%22result%22%3A%7B%22document_tone%22%3A%7B%22tone_categories%22%3A%5B%7B%22tones%22%3A%5B%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22anger%22%2C%22tone_name%22%3A%22Anger%22%7D%2C%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22disgust%22%2C%22tone_name%22%3A%22Disgust%22%7D%2C%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22fear%22%2C%22tone_name%22%3A%22Fear%22%7D%2C%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22joy%22%2C%22tone_name%22%3A%22Joy%22%7D%2C%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22sadness%22%2C%22tone_name%22%3A%22Sadness%22%7D%5D%2C%22category_id%22%3A%22emotion_tone%22%2C%22category_name%22%3A%22Emotion+Tone%22%7D%2C%7B%22tones%22%3A%5B%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22analytical%22%2C%22tone_name%22%3A%22Analytical%22%7D%2C%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22confident%22%2C%22tone_name%22%3A%22Confident%22%7D%2C%7B%22score%22%3A0.0%2C%22tone_id%22%3A%22tentative%22%2C%22tone_name%22%3A%22Tentative%22%7D%5D%2C%22category_id%22%3A%22language_tone%22%2C%22category_name%22%3A%22Language+Tone%22%7D%2C%7B%22tones%22%3A%5B%7B%22score%22%3A0.068939%2C%22tone_id%22%3A%22openness_big5%22%2C%22tone_name%22%3A%22Openness%22%7D%2C%7B%22score%22%3A0.145665%2C%22tone_id%22%3A%22conscientiousness_big5%22%2C%22tone_name%22%3A%22Conscientiousness%22%7D%2C%7B%22score%22%3A0.270129%2C%22tone_id%22%3A%22extraversion_big5%22%2C%22tone_name%22%3A%22Extraversion%22%7D%2C%7B%22score%22%3A0.461938%2C%22tone_id%22%3A%22agreeableness_big5%22%2C%22tone_name%22%3A%22Agreeableness%22%7D%2C%7B%22score%22%3A1.14E-4%2C%22tone_id%22%3A%22emotional_range_big5%22%2C%22tone_name%22%3A%22Emotional+Range%22%7D%5D%2C%22category_id%22%3A%22social_tone%22%2C%22category_name%22%3A%22Social+Tone%22%7D%5D%7D%7D%7D%7D%7D&NumSegments=1&MessageSid=SMfa9adb16915f1265fb74047f2c9451c4&AccountSid=AC193fd584652e4c3bb7c3e918f06b065e&From=%2B13322427816&ApiVersion=2010-04-01';
        //$input='SmsSid=SMb937992a991240608f2893dd3e13ffcc&SmsStatus=delivered&MessageStatus=delivered&To=%2B13322427816&MessageSid=SMb937992a991240608f2893dd3e13ffcc&AccountSid=AC193fd584652e4c3bb7c3e918f06b065e&From=%2B12293480700&ApiVersion=2010-04-01';

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

        $phone_number =  $this->client->lookups->v1->phoneNumbers($mess->from)
            ->fetch(["type" => ["carrier"]]);
        $lookup=explode('-',$phone_number->carrier['name'])[0];


        if ($lookup !== 'Twilio ') {

            $user = User::where('phone_no', $mess->to)->first();
            $exist_in_fan_club = FanClub::where('is_active', 1)
                ->where('user_id', $user->id)
                ->where('local_number', $mess->from)
                ->exists();


            if (!$exist_in_fan_club) {

                $uuid = \Illuminate\Support\Str::uuid()->toString();
                if ($user->count() != 0) {

                    FanClub::where('is_active', 0)
                        ->where('local_number', $mess->from)
                        ->where('user_id', $user->id)
                        ->delete();

                    FanClub::create([
                        'fan_club_uuid' => Str::uuid()->toString(),
                        'user_id' => $user->id,
                        'local_number' => $mess->from,
                        'fan_id' => 0,
                        'temp_id' => $uuid,
                        'is_active' => 0,
                        'temp_id_date_time' => date('Y-m-d H:i:s')
                    ]);

                    $body = 'You are Welcome In Portal.To continue further please sign up from below link:   ' . $this->generateSignUplink($uuid);
                    $message = $this->client->messages
                        ->create(
                            $mess->from,
                            ["body" => $body, "from" =>  $mess->to, "statusCallback" => "https://text-app.tkit.co.uk/api/api/twilio_webhook"]
                        );
                }
            }else{
                $sender_id = User::where('phone_no', $mess->from)->first()->id;
                $receiver_id = User::where('phone_no', $mess->to)->first()->id;

                $message_record = [
                    'sms_uuid' => Str::uuid()->toString(),
                    'sender_id' => $sender_id,
                    'receiver_pid' => $receiver_id,
                    'message_id' => 0,
                    'message' => $mess->body,
                    'is_seen' => 0,
                    'created_at' => date('d-m-y'),
                    'align' => '',
                    'direction' => $mess->direction,
                ];

                ChatEvent::dispatch($message_record);
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
                'created_at' => date('d-m-y'),
                'align' => '',
                'direction' => $mess->direction,
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

    public function twilioFeedbackBackup()
    {


        $input = DB::table('twilio_response')->where('id',9)->first();

        $data = explode('&', $input->body_)[0];
        $data = explode('=', $data);


        if($data[0]=='ToCountry'){

            $record=explode('&', $input->body_)[2];
            $record = explode('=', $record);

            $msg_id =$record[1];
        }else{
            $msg_id = explode('&', $input->body_)[4];
            $msg_id = explode('=', $msg_id);
            $msg_id =$msg_id[1];
        }



        $mess = $this->client->messages($msg_id)
            ->fetch();



        if($mess->direction=='outbound-api'){

            $fan_club=FanClub::where('is_active',1)->where('local_number',$mess->from)->orWhere('local_number',$mess->to)->get();
            $wordCount = $fan_club->count();

            if($wordCount==0){

                $uuid = \Illuminate\Support\Str::uuid()->toString();
                $user = User::where('phone_no',$mess->from)->first();


                if($user->count()!=0){


                    FanClub::create([
                        'fan_club_uuid'=>0,
                        'user_id'=> $user->id,
                        'local_number'=> $mess->to,
                        'fan_id'=> 0,
                        'temp_id'=>Str::uuid()->toString(),
                        'is_active'=>0,
                        'temp_id_date_time'=>date('Y-m-d H:i:s')
                    ]);

                    $body='You are Welcome In Portal.To continue further please sign up from below link:   '.$this->generateSignUplink($uuid);
                    $message=$this->client->messages
                        ->create($mess->to,
                            ["body" => $body, "from" =>  $mess->from, "statusCallback" => "https://text-app.tkit.co.uk/api/api/twilio_webhook"]
                        );
                }
            }
        }else{
            $sender_id=User::where('phone_no',$mess->from)->first()->id;

            $receiver_id=User::where('phone_no',$mess->to)->first()->id;
            $message_record=[
                'sms_uuid'=>Str::uuid()->toString(),
                'sender_id'=>$sender_id,
                'receiver_id'=>$receiver_id,
                'message_id'=>0,
                'message'=>$mess->body,
                'is_seen'=>0,
                'created_at'=>'12-2-2021',
                'align'=>'',
                'direction'=>'inbound',

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
            echo '<br>'. $value->id;
            echo '<br>to= ' . $mess->to;
            echo '<br>from= ' . $mess->from;

            $fan_club=FanClub::where('active',1)->where(['from'=>$from,'to'=>$to])->get();

            $messages = $this->client->messages
                ->read(
                    [
                        "from" => $mess->from,
                    ],
                    100
                );

            echo '<pre>';
            print_r(count( $messages));


        }

    }


}
