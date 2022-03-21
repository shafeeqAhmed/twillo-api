<?php

namespace App\Jobs;

use App\Http\Traits\CommonHelper;
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
    public function handle()
    {

        if ($this->type == 'single') {
            $this->send_twilio_message($this->getValue('receiver'), $this->message, $this->getValue('sender'));
            // $this->send_twilio_message($this->request_data['receiver_number'],$this->message,$this->request_data['user']->phone_no);
        }
        if ($this->type == 'multiple') {
            foreach ($this->request_data['fans'] as $fan) {
                $encodedMessage = CommonHelper::filterAndReplaceLink([
                    'message' => $this->message,
                    'receiver_id' => $fan->fan_club_id,
                    'influencer_id' => $this->request_data['user']->id
                ]);
                $this->send_twilio_message($fan['local_number'], $encodedMessage, $this->request_data['user']->phone_no);
            }
        }

        return true;
    }


    public function send_twilio_message($number, $message, $from)
    {
        $data =  [
            "body" => $message,
            "from" =>  $from,
            "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"
        ];
        if ($this->getValue('scheduled')) {
            $data['sendAt'] = $this->getValue('scheduled_date_time');
            $data['scheduleType'] = 'fixed';
        }
        $this->client->messages->create($number, $data);
    }
}
