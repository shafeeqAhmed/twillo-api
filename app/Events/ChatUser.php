<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\FanClub;

use App\Models\User;

class ChatUser  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
   public $data;
    /** 
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->data=$user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {

        $user_id=FanClub::where('local_number',$this->data['phone_no'])->first()->user_id;
        $user_uuid=User::find($user_id)->user_uuid;
    
        return  new Channel('user.'.$user_uuid);
    }


    public function broadcastAs()
    {
        return 'user.event';
    }
}
