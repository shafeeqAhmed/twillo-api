<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\CommonHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\TwilioNumbers;
use Twilio\Exceptions\ConfigurationException;
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
use App\Models\MessageLinks;
use App\Jobs\SendTextMessage;


class TwilioChatController extends ApiController
{

    use CommonHelper;
    private $client;

    public function __construct()
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);
    }

    public function getChatMessages(Request $request, $id,$pageSize=5)
    {

        $from = $request->user()->phone_no;
        $receiver_id = $id;

        $to = FanClub::where('id', $receiver_id)->first();
//        $receiver_image=$to->profile_photo_path;
        $to = $to->local_number;
//        dd($from,$to);
//
//
//        if(!$receiver_image){
//              $receiver_image= asset('storage/users/profile/default.png');
//        }
        $receiver_image= asset('storage/users/profile/default.png');
        $messages1 = $this->client->messages
            ->read(
                [
                    "from" => $from,
                    "to" => $to,
                    'order' => 'desc'
                ],
                $pageSize
            );
        $messages2 = $this->client->messages
            ->read(
                [
                    "from" => $to,
                    "to" => $from,
                    'order' => 'desc'
                ],
                $pageSize
            );
        $messages = array_merge($messages1,$messages2);
        $message_history = [];
        foreach($messages as $index => $message) {
            $list = $message->toArray();
//            dd($list);
//            $ar  = [];
//            $ar['from'] = $list['from'];
//            $ar['to'] = $list['to'];
//            $ar['body'] = $list['body'];
//            $ar['dateSentTimeStamp'] = strtotime($list['dateSent']->format('Y-m-d H:i:s'));
//            $ar['dateSent'] = $list['dateSent']->format('Y-m-d H:i:s');
//            $message_history[] = $ar;
            if(!str_contains($list['body'], 'Hey! This is an auto text to let you know')){
            $message_history[$index]['message'] = $list['body'];
            $message_history[$index]['direction'] = $list['direction'];
            $message_history[$index]['align'] = $list['direction'] != 'inbound' ? 'right' : '';
            $message_history[$index] ['timestamp'] = strtotime($list['dateSent']->format('Y-m-d H:i:s'));
            $message_history[$index] ['time'] = Carbon::parse($list['dateSent']->format('Y-m-d H:i:s'))->diffForHumans();
            $message_history[$index]['id'] = 0;
            $message_history[$index]['to'] = $list['to'];
            $message_history[$index]['from'] = $list['from'];
            $message_history[$index]['name'] = '';
            $message_history[$index]['status'] = $list['status'];
            $message_history[$index]['image'] = $list['direction'] != 'inbound' ? $request->user()->profile_photo_path : $receiver_image;
}
        }
        $message_history = Collect($message_history)->sortBy('timestamp',SORT_NATURAL);
        $list = [];
        foreach($message_history as $history) {
            $list[] = $history;
        }


//       $messages = $this->client->messages
//            ->read(
//                [
//                    "from" => $from,
//                    "to" => $to,
//                    'order' => 'desc'
//                ],
//                5
//            );



//        $messages2 = $this->client->messages
//            ->read(
//                [
//                    "from" => $to,
//                    "to" => $from,
//                    'order' => 'desc'
//                ],
//                5
//            );

//        $messages = $this->client->messages->read(['order', 'desc'], 7);
//            $messages = array_merge($messages,$messages2);
//        $message_history = array();
//        foreach ($messages   as $index => $record) {
//
//            $mess = $this->client->messages($record->sid)
//                ->fetch();
//
//            if (($mess->from == $from && $mess->to == $to) || ($mess->from == $to && $mess->to == $from)) {
//
//                if(!str_contains($mess->body, 'You are Welcome In Portal')){
//
//                $message_history[$index]['message'] = $mess->body;
//                $message_history[$index]['direction'] = $mess->direction;
//                // $message_history[$index] ['time'] = $mess->dateSent;
//                 $message_history[$index] ['time'] = '12-4-2022';
//                $message_history[$index]['align'] = $mess->direction != 'inbound' ? 'right' : '';
//                $message_history[$index]['id'] = 0;
//                $message_history[$index]['to'] = $mess->to;
//                $message_history[$index]['from'] = $mess->from;
////                $message_history[$index]['from'] = $mess->direction != 'inbound' ? $mess->from : $mess->to;
//                $message_history[$index]['name'] = '';
//                $message_history[$index]['image'] = $mess->direction != 'inbound' ? $request->user()->profile_photo_path : $receiver_image;
//            }
//        }
//        }
        return $this->respond([
            'data' => $list
        ]);
    }


    public function getInfluencerContacts(Request $request)
    {
        $sender_id = $request->user()->id;
        $users = FanClub::with('fan')->latest()->select('id', 'fan_club_uuid', 'local_number', 'fan_id', 'temp_id', 'created_at')->groupBy('local_number')->where('user_id', $sender_id)->where('is_active', 1)->orderBy('created_at', 'desc')->get();

        return $this->respond([
            'data' => ($users)
        ]);
    }




    public function smsService(Request $request)
    {
        $data = $request->except('receiver_number');
        $data['influencer_id'] = $request->user()->id;
        $encodedMessage = CommonHelper::filterAndReplaceLink($data);

        // date we receive from frontaend calender, for testing putting 10 minutes from now on.
        try {
            $request_data = $request->all();
            $request_data['user']=$request->user();
            dispatch(new SendTextMessage($encodedMessage, $request_data));
        } catch (ConfigurationException $e) {
            \Log::info('----job exception catch');
            \Log::info($e->getMessage());
        }

        $message_record = [
            'sms_uuid' => Str::uuid()->toString(),
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message_id' => 0,
            'message' => $request->message,
            'is_seen' => 0,
            'created_at' =>date('d-m-y'),
        ];

//        ChatEvent::dispatch($message_record);

        return $this->respond([
            'data' => $message_record
        ]);
    }

    


    public function Port()
    {

        /*  $validation_request =  $this->client->validationRequests
                             ->create("+18725298577", // phoneNumber
                                      ["friendlyName" => "18725298577"]
                             );
echo '<pre>';
print_r($validation_request);*/

        $validation_request =  $this->client->validationRequests
            ->create(
                "+923216910563", // phoneNumber
                ["friendlyName" => "923216910563"]
            );
        echo '<pre>';
        print_r($validation_request);
    }
}
