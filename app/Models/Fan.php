<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Fan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function fanClub()
    {
        return $this->hasOne(FanClub::class, 'fan_id', 'id');
    }
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
    public function getDobAttribute($value)
    {
        return  Carbon::parse($value)->diff(Carbon::now())->y . ' Year';
    }
}
