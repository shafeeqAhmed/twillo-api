<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadCastMessage extends Model
{
    use HasFactory;
    protected $table = 'broadcast_message';
    protected $guarded = ['id'];
    public function responseRate()
    {
        // return $this->hasOne('')
    }
}
