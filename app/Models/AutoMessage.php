<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoMessage extends Model
{
    use HasFactory;
    protected $table = 'auto_messages';
    protected $guarded = ['id'];

    public function getStatusAttribute($key)
    {
        return $key == 1 ? 'Active' : 'Inactive';
    }
}
