<?php

namespace App\Jobs;

use App\Http\Traits\CommonHelper;
use App\Models\BroadCastMessage;
use App\Models\Messages;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;

class SendTextMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $request_data;
    protected $type;
    protected $client;
    protected $broadCastMessage;

    /**
     * @var mixed|string
     */

    /**
     * Create a new job instance.
     *
     * @param $message
     * @param $request_data
     * @param string $type
     * @throws ConfigurationException
     */
    public function __construct($message, $request_data, $type = 'single')
    {
        $this->message = $message;
        $this->request_data = $request_data;
        $this->type = $type;
        // twilio client intitialization
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function getValue($type)
    {
        if ($type == 'receiver_id') {
            return  $this->request_data['receiver_id'];
        }
        if ($type == 'receiver') {
            return  $this->request_data['receiver_number'];
        }
        if ($type == 'sender') {
            return  $this->request_data['user']->phone_no;
        }
        if ($type == 'scheduled') {
            return isset($this->request_data['is_scheduled']) ? $this->request_data['is_scheduled'] : false;
        }
        if ($type == 'scheduled_date_time') {
            return isset($this->request_data['scheduled_date_time']) ? Carbon::parse($this->request_data['scheduled_date_time'])->toIso8601String() : Carbon::now()->toIso8601String();
        }
    }
    public function updateLocalMessage($fan_id, $user_id, $message, $status, $broadcast_id, $twilio_msg_id, $stander_time)
    {
        Messages::create([
            'fan_id' => $fan_id,
            'user_id' => $user_id,
            'message' => $message,
            'status' => $status,
            'broadcast_id' => $broadcast_id,
            'twilio_msg_id' => $twilio_msg_id,
            'stander_time' => $stander_time
        ]);
    }
    public function handle()
    {

        if ($this->type == 'single') {

            $this->send_twilio_message($this->getValue('receiver'), $this->message, $this->getValue('sender'), $this->request_data['user']->id, $this->getValue('receiver_id'));
        }
        if ($this->type == 'multiple') {

            //store broad cast message
            $this->broadCastMessage = BroadCastMessage::create([
                'user_id' => $this->request_data['user']->id,
                'message' => $this->message,
                'type' => $this->getValue('scheduled') ? 'schedule' : 'direct',
                'filters' => json_encode($this->request_data['filter']),
                'scheduled_at_local_time' => Carbon::now(),
                'scheduled_at_stander_time' => $this->getValue('scheduled_date_time')
            ]);

            foreach ($this->request_data['fans'] as $fan) {
                $encodedMessage = CommonHelper::filterAndReplaceLink([
                    'message' => $this->message,
                    'receiver_id' => $fan->fan_club_id,
                    'influencer_id' => $this->request_data['user']->id
                ]);
                $this->send_twilio_message($fan['local_number'], $encodedMessage, $this->request_data['user']->phone_no, $this->request_data['user']->id, $fan->fan_id);
            }
        }

        return true;
    }


    public function send_twilio_message($number, $message, $from, $user_id, $fan_id)
    {
        $data =  [
            "body" => $message,
            "from" =>  $from,
            "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"
        ];
        if ($this->getValue('scheduled')) {
            $data['sendAt'] = $this->getValue('scheduled_date_time');
            $data['scheduleType'] = 'fixed';

            $result = $this->client->messages->create($number, $data);
            $this->updateLocalMessage($fan_id, $user_id, $message, $result->status, $this->broadCastMessage->id, $result->sid, $data['sendAt']);
        }
        $result = $this->client->messages->create($number, $data);
        $this->updateLocalMessage($fan_id, $user_id, $message, $result->status, null, $result->sid, null);
    }
}
