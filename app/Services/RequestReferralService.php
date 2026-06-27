<?php

namespace App\Services;

use App\Models\RequestModel;
use App\Models\RequestReferral;
use App\Models\RequestReply;
use App\Models\User;

class RequestReferralService
{
    /**
     * یک مرحله جدید ارجاع را ثبت می‌کند و مرحله‌ی قبلی (اگر باز بود) را می‌بندد.
     * این متد باید هر بار که اختصاص کارشناس یک درخواست تغییر می‌کند صدا زده شود:
     * هنگام ارجاع عادی (refer)، بازگشت توسط ارجاع‌دهنده (recall) یا عودت به ارجاع‌دهنده (return).
     */
    public function recordStep(
        RequestModel $request,
        ?User $fromExpert,
        User $toExpert,
        string $type = 'referral',
        ?RequestReply $reply = null,
    ): RequestReferral {
        $now = now();

        // بستن آخرین مرحله‌ی باز (اگر وجود دارد)
        $openStep = RequestReferral::where('request_id', $request->id)
            ->where('status', 'active')
            ->latest('step_no')
            ->first();

        if ($openStep) {
            $openStep->update([
                'status'    => 'closed',
                'closed_at' => $now,
            ]);
        }

        $nextStepNo = ($openStep?->step_no ?? 0) + 1;

        return RequestReferral::create([
            'request_id'          => $request->id,
            'request_reply_id'    => $reply?->id,
            'step_no'             => $nextStepNo,
            'from_expert_id'      => $fromExpert?->id,
            'to_expert_id'        => $toExpert->id,
            'from_department_id'  => $fromExpert?->department_id,
            'to_department_id'    => $toExpert->department_id,
            'type'                => $type,
            'status'              => 'active',
            'started_at'          => $now,
        ]);
    }

    /**
     * هنگامی که درخواست بالاخره پاسخ نهایی می‌گیرد (status = answered)،
     * آخرین مرحله‌ی باز ارجاع را می‌بندیم تا گزارش زمان‌بندی کامل شود.
     */
    public function closeOpenStep(RequestModel $request): void
    {
        RequestReferral::where('request_id', $request->id)
            ->where('status', 'active')
            ->update([
                'status'    => 'closed',
                'closed_at' => now(),
            ]);
    }
}
