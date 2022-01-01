<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;
    protected $table='messages';
    protected $fillable=['sms_uuid','sender_id','receiver_id','message_id','message','is_seen','chat_user_id'];


     public function user(){
        return $this->belongsTo('\App\Models\User','sender_id','id');
    }

     

    public function isSender(){
        return (auth()->id() == $this->sender_id);
    }
}
