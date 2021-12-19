<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\TwilioNumbers;
use Twilio\Rest\Client;
use App\Models\Country;


class TwilioNumbersController extends ApiController
{
     private $client;

     public function __construct()
    {



        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);

    }

    public function getTwillioNumbers($nosToBuy,$country_id){

      $country=Country::find($country_id);
      return  $this->client->availablePhoneNumbers($country->country_sort_name)->local->read([],$nosToBuy);

    }


    public function purchaseTwillioNumbers($nosToBuy,$country_id)
    {
         $twilioPhoneNumbers = $this->getTwillioNumbers($nosToBuy,$country_id);
             $data=array();
            for ($loop = 0; $loop < $nosToBuy; $loop++) {
           $data['number']=  $this->buy($twilioPhoneNumbers[$loop]->phoneNumber);
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

    public function msgTracking(Request $request){


$messages = $this->client->messages
                   ->read([

                              "from" => $request->from,

                          ],
                          20
                   );

$data['total_messages']=count($messages);
$message_history=array();
foreach ($messages as $index =>$record) {

$mess= $this->client->messages($record->sid)
                  ->fetch();
$message_history['to'][$index]=$mess->to;
$message_history['body'][$index]=$mess->body;
$message_history['status'][$index]=$mess->status;

}


$data['message_history']=$message_history;


 return $this->respond([
            'data' => $data
        ]);

    }


}
