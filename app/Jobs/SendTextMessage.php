<?php

namespace App\Jobs;

use App\Http\Traits\CommonHelper;
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
    public function __construct($message, $request_data, $type='single')
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
    public function handle()
    {

        if($this->type == 'single'){

            $this->send_twilio_message($this->request_data['receiver_number'],$this->message,request()->user()->phone_no);
        }
        if($this->type == 'multiple'){
            foreach($this->request_data['fans'] as $fan){
                $encodedMessage = CommonHelper::filterAndReplaceLink([
                    'message'=>$this->message,
                    'receiver_id'=>$fan->fan_club_id,
                    'influencer_id'=>$this->request_data['user']->id
                ]);
                $this->send_twilio_message($fan['local_number'],$encodedMessage,$this->request_data['user']->phone_no);
            }
        }

        return true;
    }


    public function send_twilio_message($number, $message, $from){
//        $this->client->messages
//            ->create(
//                $number,
//                ["body" => $message, "from" =>  $from, "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"]
//            );
    }
}
