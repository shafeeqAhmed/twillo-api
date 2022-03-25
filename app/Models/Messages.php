<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;
    protected $table = 'messages';
    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo('\App\Models\User', 'sender_id', 'id');
    }



    public function isSender()
    {
        return (auth()->id() == $this->sender_id);
    }
    public static function updateData($column, $value, $data)
    {
        return  Messages::where($column, $value)->update($data);
    }
    public function fan()
    {
        return $this->belongsTo(Fan::class, 'fan_id', 'id');
    }
}
