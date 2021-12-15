<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwilioNumbers extends Model
{
    use HasFactory;
    protected $table='twilio_numbers';
     protected $fillable = [
        'phone_no',
        'status'
    ];
}
