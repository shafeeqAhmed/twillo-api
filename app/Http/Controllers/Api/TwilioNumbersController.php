<?php

namespace App\Http\Controllers\Api;


use App\Models\Fan;
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

        if($country->country_sort_name == 'GB') {
            $res = $this->client->availablePhoneNumbers($country->country_sort_name)->mobile->read([], $nosToBuy);
        } else {
            $res = $this->client->availablePhoneNumbers($country->country_sort_name)->local->read([], $nosToBuy);
        }
        return $res;
    }

    public function purchaseTwillioNumbers($nosToBuy, $country_code)
    {
        try {
            $twilioPhoneNumbers = $this->getTwillioNumbers($nosToBuy, $country_code);
            $data = array();
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


            return $this->respond([
                'data' => $data,
                'status'=>true,
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'data' => [],
                'status'=>false,
            ]);
        }
    }

    public function buy($twilioPhoneNumber,$address_sid)
    {
//        if($address_sid!=0){
//            $this->client->incomingPhoneNumbers->create([
//                    'phoneNumber' => $twilioPhoneNumber,
//                    "smsUrl" => "https://text-app.tkit.co.uk/api/api/twilio_webhook",
//                    "addressSid" => $address_sid,
//                ]);
              TwilioNumbers::create([
                    'no' => $twilioPhoneNumber,
                ]);


        return $twilioPhoneNumber;
//    }
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
            sendAndReceiveSms($user->id,'receive');

            $exist_in_fan_club = FanClub::where('is_active', 1)->where('user_id',$user->id)->where('local_number', $mess->from)->exists();
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
                $sender_id = Fan::where('phone_no', $mess->from)->first()->id;
                $receiver_id = User::where('phone_no', $mess->to)->first()->id;
//                dd($sender_id,$receiver_id,$mess->from,$mess->to);
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
        }
        else {
            $sender_id = User::where('phone_no', $mess->from)->first()->id;
            sendAndReceiveSms($sender_id,'send');

//            $receiver_id = Fan::where('phone_no', $mess->to)->first()->id;
//
//            $message_record = [
//                'sms_uuid' => Str::uuid()->toString(),
//                'sender_id' => $sender_id,
//                'receiver_id' => $receiver_id,
//                'message_id' => 0,
//                'message' => $mess->body,
//                'is_seen' => 0,
//                'created_at' => date('d-m-y'),
//                'align' => '',
//                'direction' => $mess->direction,
//            ];
//
//            ChatEvent::dispatch($message_record);
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
