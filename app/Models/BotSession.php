<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSession extends Model
{
    protected $fillable = ['bale_id', 'step', 'data'];

    protected $casts = [
        'data' => 'array',
    ];
}