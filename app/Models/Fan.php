<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function fanClub()
    {
        return $this->hasOne(FanClub::class, 'fan_id', 'id');
    }
}
