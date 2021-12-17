<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\TwilioNumbers;
use Twilio\Rest\Client;


class TwilioNumbersController extends ApiController
{
     private $client;
    const COUNTRY = 'US';

     public function __construct()
    {
        
        $sid = "AC193fd584652e4c3bb7c3e918f06b065e";
        $token = "f145377b27ea5a13624d4ea6ebf87a57";
        $this->client = new Client($sid, $token);

    }

    public function getTwillioNumbers($nosToBuy){

      return  $this->client->availablePhoneNumbers(self::COUNTRY)->local->read(["areaCode" => 510],$nosToBuy);
        
    }


    public function purchaseTwillioNumbers($nosToBuy)
    {
           
          $twilioPhoneNumbers = $this->getTwillioNumbers($nosToBuy);
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
      

     $this->client->incomingPhoneNumbers->create(
                ['phoneNumber' => $twilioPhoneNumber]
            );
    

      TwilioNumbers::create([
            'phone_no' => $twilioPhoneNumber,
            'status' => 'active'      
        ]);


       return $twilioPhoneNumber;
        
    }

    
}
