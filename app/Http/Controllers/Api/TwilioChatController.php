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

class TwilioChatController extends ApiController
{

	    public function getChatMessages(Request $request,$id){

     $sender_id=$request->user()->id;
     $receiver_id=$id;

     $messages=ChatUsers::with('chat_messages.user')->where(['sender_id'=>$sender_id,'receiver_id'=>$receiver_id])->first();


          
        return $this->respond([
        'data' =>  new ChatUserResource($messages)
        ]);

    }


     public function getInfluencerContacts(Request $request){

     $sender_id=$request->user()->id;

     $users=User::role('influencer')->where('id','!=',$sender_id)->get();

     dd($users);
          
        return $this->respond([
        'data' =>  new ChatUserResource($messages)
        ]);

    }


       public function smsService(Request $request){
          
       
        /*$message=$this->client->messages
                  ->create($request->receiver_number,
                           ["body" => $request->message, "from" =>  $request->user()->phone_no, "statusCallback" => "https://text-app.tkit.co.uk/api/api/twilio_webhook"]
                  );*/
     
       DB::transaction(function() use ($request){

    $chat_users=ChatUsers::where(['sender_id'=>$request->user()->id,'receiver_id'=>$request->receiver_id])->orWhere(['receiver_id'=>$request->user()->id,'sender_id'=>$request->receiver_id])->get();
           

        if( count($chat_users)==0){
            $chat_user_record=ChatUsers::create([
            'sender_id'=>$request->user()->id,
            'receiver_id'=>$request->receiver_id,
            'is_active'=>1,
             'type'=> 'one-to-one'
           ]);

        $chat_user_id=$chat_user_record->id;
        }else{
          $chat_user_id=$chat_users[0]->id;
        }


         $message_record=Messages::create([
           'sms_uuid'=>Str::uuid()->toString(),
           'sender_id'=>$request->user()->id,
           'receiver_id'=>$request->receiver_id,
            'message_id'=>0,
            'message'=>$request->message,
            'is_seen'=>0,
            'chat_user_id'=>$chat_user_id
          ]);

          return $this->respond([
            'data' => $message_record->id
        ]);

          });


    }
    
}
