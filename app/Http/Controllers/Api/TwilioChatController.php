<?php

namespace App\Http\Controllers\Api;

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
use App\Notifications\ChatNotfication;
use App\Events\ChatEvent;
use App\Models\FanClub;


class TwilioChatController extends ApiController
{


  private $client;

    public function __construct()
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);
    }

	    public function getChatMessages(Request $request,$id){

     $from=$request->user()->phone_no;
     $receiver_id=$id;

     
       $to = User::where('id',$receiver_id)->first();
      $to= $to->phone_no;

        $messages = $this->client->messages
            ->read(
               ["order" => "desc"],
                20
            );
            
        $message_history = array();
        foreach ($messages as $index => $record) {

            $mess = $this->client->messages($record->sid)
                ->fetch();
            
            if(($mess->from==$from && $mess->to==$to) || ($mess->from==$to && $mess->to==$from)  ){
            $message_history[$index] ['message']= $mess->body;
            $message_history[$index] ['direction'] = $mess->direction; 
           // $message_history[$index] ['time'] = $mess->dateSent;
            $message_history[$index] ['time'] = '12-2-2021';
            $message_history[$index] ['align'] = $mess->direction!='inbound' ? 'right' :'';
            $message_history[$index] ['id'] = 0;  
            $message_history[$index] ['to'] =$mess->to;  
            $message_history[$index] ['name'] = 'talha';  
            $message_history[$index] ['image'] = $mess->direction!='inbound' ? $request->user()->profile_photo_path :asset('storage/users/profile/default.png');  
            } 
        
        }

    
      return $this->respond([
        'data' => $message_history
        ]);

    }


     public function getInfluencerContacts(Request $request){

      $sender_id=$request->user()->id;

      $users=FanClub::with('user')->latest()->select('id','fan_club_uuid','local_number','fan_id','temp_id','created_at')->groupBy('local_number')->where('user_id',$sender_id)->where('is_active',1)->orderBy('created_at', 'desc')->get();
       
        return $this->respond([
        'data' =>  ($users)
        ]);

    }


       public function smsService(Request $request){
          
       
        $message=$this->client->messages
                  ->create($request->receiver_number,
                           ["body" => $request->message, "from" =>  $request->user()->phone_no, "statusCallback" => "https://text-app.tkit.co.uk/api/api/twilio_webhook"]
                  );


                   $message_record=[
                   'sms_uuid'=>Str::uuid()->toString(),
                   'sender_id'=>$request->user()->id,
                   'receiver_id'=>$request->receiver_id,
                    'message_id'=>0,
                    'message'=>$request->message,
                    'is_seen'=>0,
                     'created_at'=>'12-2-2021'
                  ];
        
          ChatEvent::dispatch($message_record);
       
          return $this->respond([
            'data' => $message_record
        ]);
   


    }
    

     public function Port(){
      
      /*  $validation_request =  $this->client->validationRequests
                             ->create("+18725298577", // phoneNumber
                                      ["friendlyName" => "18725298577"]
                             );
echo '<pre>';
print_r($validation_request);*/

  $validation_request =  $this->client->validationRequests
                             ->create("+923216910563", // phoneNumber
                                      ["friendlyName" => "923216910563"]
                             );
echo '<pre>';
print_r($validation_request);

    }

    
}
