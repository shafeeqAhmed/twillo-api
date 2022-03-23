<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLinks extends Model
{
    use HasFactory;
    protected $table = 'message_links';
    protected $guarded = ['id'];

    public function fan()
    {
        return $this->belongsTo('\App\Models\FanClub', 'fanclub_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('\App\Models\User', 'influencer_id', 'id');
    }
}
