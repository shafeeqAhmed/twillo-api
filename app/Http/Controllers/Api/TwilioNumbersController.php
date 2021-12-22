<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\TwilioNumbers;
use Twilio\Rest\Client;
use App\Models\Country;
use App\Models\User;

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
        return  $this->client->availablePhoneNumbers($country->country_sort_name)->local->read([], $nosToBuy);
    }


    public function purchaseTwillioNumbers($nosToBuy, $country_code)
    {
        $twilioPhoneNumbers = $this->getTwillioNumbers($nosToBuy, $country_code);
        $data = array();
        for ($loop = 0; $loop < $nosToBuy; $loop++) {
            $data['number'] =  $this->buy($twilioPhoneNumbers[$loop]->phoneNumber);
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
        $from = User::where('user_uuid',$request->uuid)->value('phone_no');
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

        public function smsService(Request $request){
        $message=$this->client->messages
                  ->create($request->to,
                           ["body" => $request->sms, "from" => $request->from, "statusCallback" => "https://text-app.tkit.co.uk/api/api/twilio_webhook"]
                  );
         print($message->sid);
    }
    
    
    public function twilioWebhook(){
          $input = (file_get_contents('php://input'));
          DB::table('twilio_response')->insert([
                'body_' =>$input
            ]);
          
    }

        public function twilioFeedback(){
        $input=DB::table('twilio_response')->get();
        $input=$input->toArray();
        foreach($input as $key=>$value)
        {
            
            
         echo '<pre>';  
         $to=explode('&',$value->body_ )[3];
         
         $to=explode('=',$to);
         //echo '<br>'.$to[1];
         
         
         $from=explode('&',$value->body_ )[6];
          $from=explode('=',$from);
         //echo '<br>'.$from[1];
         
         
          $msg_id=explode('&',$value->body_ )[4];
           $msg_id=explode('=',$msg_id);
         echo '<br>'.$msg_id[1];
         
         echo '<br>';
         
          $mess = $this->client->messages($msg_id[1])
                ->fetch();
           echo  '<br>to= '. $mess->to;
           echo  '<br>from= '. $mess->from;
               echo  '<br>body= '. $mess->body;

         
        }
        
    }


}
