<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * هشدارهایی که هنگام backfill تاریخچه ارجاع تولید می‌شوند:
 * مواردی که زنجیره‌ی ارجاع شکسته بوده (مثلا یک بازگشت دستی که در زمان خودش
 * ثبت نشده) و نیاز به بازبینی انسانی دارند.
 */
class RequestReferralWarning extends Model
{
    protected $table = 'request_referral_warnings';

    protected $fillable = [
        'request_id',
        'request_reply_id',
        'message',
    ];

    public function request()
    {
        return $this->belongsTo(RequestModel::class, 'request_id');
    }
}
