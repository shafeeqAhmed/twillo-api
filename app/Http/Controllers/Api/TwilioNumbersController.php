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
 

        $input = (file_get_contents('php://input'));
       
        $msg_id = explode('&', $input->body_)[4];
            $msg_id = explode('=', $msg_id);

              
            if($msg_id[0]=='MessageSid'){
            
            $mess = $this->client->messages($msg_id[1])
                ->fetch();
    
        
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
             'is_active'=>0
           ]);

            $body='You are Welcome In Portal.To continue further please sign up from below link:   '.$this->generateSignUplink($uuid);
            $message=$this->client->messages
                  ->create($mess->to,
                           ["body" => $body, "from" =>  $mess->from, "statusCallback" => "https://text-app.tkit.co.uk/api/api/twilio_webhook"]
                  );

                  


           }
 
         }

            }

        DB::table('twilio_response')->insert([
            'body_' => $input
        ]);
    }
    public function insertInFanClub($influencer_id,$fan_phon_number,$uuid) {
        FanClub::create();
    }
    public function generateSignUplink($uuid) {
        $url = config('general.front_app_url').'/account/register?id='.$uuid;
        return $url;
    }
    public function twilioFeedback()
    {


        $input = DB::table('twilio_response')->where('id',8)->first();
        
            $msg_id = explode('&', $input->body_)[4];
            $msg_id = explode('=', $msg_id);

              
            if($msg_id[0]=='MessageSid'){
                       $mess = $this->client->messages($msg_id[1])
                ->fetch();
    
            
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
