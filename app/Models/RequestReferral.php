<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestReferral extends Model
{
    protected $table = 'request_referrals';

    protected $fillable = [
        'request_id',
        'request_reply_id',
        'step_no',
        'from_expert_id',
        'to_expert_id',
        'from_department_id',
        'to_department_id',
        'type',
        'status',
        'started_at',
        'closed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at'  => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(RequestModel::class, 'request_id');
    }

    public function reply()
    {
        return $this->belongsTo(RequestReply::class, 'request_reply_id');
    }

    public function fromExpert()
    {
        return $this->belongsTo(User::class, 'from_expert_id');
    }

    public function toExpert()
    {
        return $this->belongsTo(User::class, 'to_expert_id');
    }

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * مدت زمانی که این مرحله طول کشیده (به ثانیه).
     * اگر هنوز بسته نشده، تا همین لحظه حساب می‌شود.
     */
    public function getDurationInSecondsAttribute(): int
    {
        $end = $this->closed_at ?? now();
        return $this->started_at->diffInSeconds($end);
    }
}
