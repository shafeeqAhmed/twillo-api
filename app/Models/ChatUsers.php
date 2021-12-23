<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatUsers extends Model
{
    use HasFactory;
    protected $table='chat_users';
    protected $fillable=['sender_id','receiver_id','is_active','type'];


    public function chat_messages(){
        return $this->hasMany('\App\Models\Messages','chat_user_id','id');
    }

   
}
