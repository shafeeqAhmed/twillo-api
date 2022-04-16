<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FanClub extends Model
{
    use HasFactory;
    protected $table = 'fan_clubs';
    protected $fillable = ['fan_club_uuid', 'user_id', 'temp_id', 'local_number', 'fan_id', 'is_active', 'temp_id_date_time'];

    public function fan()
    {
        return $this->belongsTo('\App\Models\Fan', 'fan_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('\App\Models\User', 'user_id', 'id');
    }
    public static function updateFanClub($column, $value, $data)
    {
        return self::where($column, $value)
            ->where('is_active', 0)
            ->update($data);
    }
    public static function getValue($column, $value, $attribute)
    {
        return FanClub::join('fans as f', 'f.id', '=', 'fan_clubs.fan_id')
            ->where("fans_clubs.$column", '=', $value)
            ->value($attribute);
    }
    public static function deleteRecord($where)
    {
        return FanClub::where($where)->delete();
    }
    public function getIsActiveAttribute($key)
    {
        return $key == 1 ? 'Active' : 'Blocked';
    }
}
