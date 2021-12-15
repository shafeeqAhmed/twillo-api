<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\TwilioNumbers;
use Twilio\Rest\Client;


class TwilioNumbersController extends Controller
{
     private $client;
    const COUNTRY = 'US';

     public function __construct()
    {
        
        $sid = "ACe15159f4b96364de664b3ade24833589";
        $token = "8cd35d430129c9831fbb7ff43a84ddaf";
        $this->client = new Client($sid, $token);

    }

    public function getTwillioNumbers($nosToBuy){

      return  $this->client->availablePhoneNumbers(self::COUNTRY)->local->read(["areaCode" => 510],$nosToBuy);
        
    }


    public function purchaseTwillioNumbers($nosToBuy)
    {
           
          $twilioPhoneNumbers = $this->getTwillioNumbers($nosToBuy);
            for ($loop = 0; $loop < $nosToBuy; $loop++) {
              $this->_buy($twilioPhoneNumbers[$loop]->phoneNumber);
            }
    }

        private function _buy($twilioPhoneNumber)
    {
        try {
            $this->client->incomingPhoneNumbers->create(
                ['phoneNumber' => $twilioPhoneNumber]
            );
            echo "<br>Bought successful: {$twilioPhoneNumber}";

      TwilioNumbers::create([
            'phone_no' => $twilioPhoneNumber,
            'status' => 'active'      
        ]);

        } catch (Exception $exception) {
            echo 'ERROR!' . $exception->getMessage();
        }
    }
}
