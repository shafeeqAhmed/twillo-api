<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadCastMessage extends Model
{
    use HasFactory;
    protected $table = 'broadcast_message';
    protected $guarded = ['id'];
    public function links()
    {
        return $this->hasMany(MessageLinks::class, 'broadcast_id', 'id');
    }




    public function clickRate()
    {
        $clickRate = 0;
        $totalCount =  $this->hasMany(MessageLinks::class, 'broadcast_id', 'id')->count();
        $visitedCount = $this->hasMany(MessageLinks::class, 'broadcast_id', 'id')->where('message_links.is_visited', 1)->count();
        if ($totalCount > 0 && $visitedCount > 0) {
            $clickRate = round((($visitedCount / $totalCount) * 100), '2');
        }
        return $clickRate;
    }
    public function responseRate()
    {
        $responsekRate = 0;
        $totalCount =  $this->hasMany(Messages::class, 'broadcast_id', 'id')->where('status', 'delivered')->count();
        $repliedCount = $this->hasMany(Messages::class, 'broadcast_id', 'id')->where('messages.is_replied', 1)->count();
        if ($totalCount > 0 && $repliedCount > 0) {
            $responsekRate = round((($repliedCount / $totalCount) * 100), '2');
        }
        return $responsekRate;
    }
}
