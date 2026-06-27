<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestModel extends Model
{
    protected $table = 'requests';

protected $fillable = [
    'tracking_code',
    'requester_id',
    'applicant_phone',
    'applicant_student_number',
    'department_id',
    'assigned_expert_id',
    'body',
    'status',
    'is_for_other',
    'seen_at',
    'answered_at',
    'referred_at',
];

protected $casts = [
    'is_for_other' => 'boolean',
    'seen_at'      => 'datetime',
    'answered_at'  => 'datetime',
    'referred_at'  => 'datetime',
];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function files()
    {
        return $this->hasMany(RequestFile::class, 'request_id');
    }

    public function replies()
    {
        return $this->hasMany(RequestReply::class, 'request_id');
    }

    public function assignedExpert()
{
    return $this->belongsTo(User::class, 'assigned_expert_id');
}
}