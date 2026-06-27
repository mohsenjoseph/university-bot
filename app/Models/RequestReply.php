<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestReply extends Model
{
    protected $table = 'request_replies';

    protected $fillable = [
        'request_id',
        'expert_id',
        'body',
        'file_path',
        'file_name',
        'is_referral',
        'referred_to',
    ];

    protected $casts = [
        'is_referral' => 'boolean',
    ];

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function referredTo()
    {
        return $this->belongsTo(User::class, 'referred_to');
    }
}