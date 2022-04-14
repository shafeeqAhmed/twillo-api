<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AutoMessage extends Model
{
    use HasFactory;
    protected $table = 'auto_messages';
    protected $guarded = ['id'];

    // protected function status(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => $value == 1 ? 'Active' : 'Inactive',
    //         set: fn ($value) => $value == 'Active' ? true : false,
    //     );
    // }

    public function getStatusAttribute($key)
    {
        return $key == 1 ? 'Active' : 'Inactive';
    }
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i');
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i');
    }
}
