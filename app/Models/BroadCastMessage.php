<?php

namespace App\Models;

use Carbon\Carbon;
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
    public function messageLinks()
    {
        return   $this->hasMany(MessageLinks::class, 'broadcast_id', 'id');
    }
    public function messages()
    {
        return  $this->hasMany(Messages::class, 'broadcast_id', 'id');
    }
    public function getScheduledAtLocalTimeAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i');
    }
}
