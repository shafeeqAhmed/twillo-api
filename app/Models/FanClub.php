<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FanClub extends Model
{
    use HasFactory;
    protected $table='fan_clubs';
    protected $fillable=['fan_uuid','user_id','temp_id','local_number','fan_id','is_active','temp_id_date_time'];
}
