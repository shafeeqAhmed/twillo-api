<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        $query =  $this->hasMany(MessageLinks::class, 'broadcast_id', 'id');
        $visitedCount = $this->hasMany(MessageLinks::class, 'broadcast_id', 'id')->where('message_links.is_visited', 1)->count();
        if ($query->count() > 0 && $visitedCount > 0) {
            $clickRate = round((($visitedCount / $query->count()) * 100), '2');
        }
        return $query->select('broadcast_id', DB::raw("$clickRate as clickRate"));
    }
    public function responseRate()
    {
        $responsekRate = 0;
        $query =  $this->hasMany(Messages::class, 'broadcast_id', 'id')->where('status', 'delivered');
        $repliedCount = $this->hasMany(Messages::class, 'broadcast_id', 'id')->where('messages.is_replied', 1)->count();
        if ($query->count() > 0 && $repliedCount > 0) {
            $responsekRate = round((($repliedCount / $query->count()) * 100), '2');
        }
        return $query->select('broadcast_id', DB::raw("$responsekRate as responsekRate"));
    }
    public function messages()
    {
        return $this->hasMany(MessageLinks::class, 'broadcast_id', 'id');
    }
}
