<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * این migration داده‌های موجود را از request_replies به request_referrals منتقل می‌کند
     * تا گزارش‌ها برای درخواست‌های قبل از این تغییر هم کامل باشند.
     *
     * نکته مهم: from_expert_id از روی خود ردیف (expert_id) خوانده می‌شود، نه با فرض
     * اینکه «مبدا این مرحله = مقصد مرحله قبل». این فرض در داده‌های واقعی همیشه درست نیست؛
     * مثلا اگر کارشناس A درخواست را به B ارجاع دهد و بعدا (بدون ثبت بازگشت رسمی) دوباره
     * خود A یک ارجاع جدید ثبت کند، زنجیره شکسته است. این موارد به جای جایگزینی حدسی،
     * با type = 'chain_gap' علامت‌گذاری و در جدول request_referral_warnings ثبت می‌شوند
     * تا قابل بازبینی دستی باشند.
     */
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('request_referral_warnings')) {
            DB::getSchemaBuilder()->create('request_referral_warnings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('request_id');
                $table->unsignedBigInteger('request_reply_id')->nullable();
                $table->text('message');
                $table->timestamps();
            });
        }

        $requestIds = DB::table('request_replies')
            ->where('is_referral', true)
            ->distinct()
            ->pluck('request_id');

        foreach ($requestIds as $requestId) {
            $request = DB::table('requests')->where('id', $requestId)->first();
            if (!$request) {
                continue;
            }

            $referralReplies = DB::table('request_replies')
                ->where('request_id', $requestId)
                ->where('is_referral', true)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $stepNo = 0;
            $expectedFromExpertId = null; // مقصد مرحله قبل؛ برای تشخیص شکست زنجیره با expert_id واقعی مقایسه می‌شود

            foreach ($referralReplies as $index => $reply) {
                $stepNo++;

                $toExpertId   = $reply->referred_to;
                $fromExpertId = $reply->expert_id; // همیشه از خود ردیف خوانده می‌شود؛ منبع واقعی، نه حدس

                if (!$toExpertId) {
                    continue;
                }

                // تشخیص شکست زنجیره: اگر این اولین مرحله نیست و فرستنده‌ی این مرحله
                // با گیرنده‌ی مرحله قبل یکی نیست، یعنی یک رویداد ثبت‌نشده بین این دو رخ داده
                // (مثلا بازگشت/recall که در آن زمان ثبت نمی‌شده).
                $chainBroken = $stepNo > 1 && $expectedFromExpertId !== null
                    && (int) $fromExpertId !== (int) $expectedFromExpertId;

                $type = $chainBroken ? 'chain_gap' : 'referral';

                if ($chainBroken) {
                    DB::table('request_referral_warnings')->insert([
                        'request_id'       => $requestId,
                        'request_reply_id' => $reply->id,
                        'message'          => "مرحله {$stepNo}: انتظار می‌رفت فرستنده کارشناس #{$expectedFromExpertId} باشد (مقصد مرحله قبل)، اما ردیف واقعی expert_id=#{$fromExpertId} است. ممکن است یک رویداد ثبت‌نشده (بازگشت دستی، ویرایش مستقیم دیتابیس) بین این دو مرحله رخ داده باشد.",
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);

                    Log::warning("request_referrals backfill: chain gap detected", [
                        'request_id' => $requestId,
                        'step_no'    => $stepNo,
                        'expected_from' => $expectedFromExpertId,
                        'actual_from'   => $fromExpertId,
                    ]);
                }

                $fromDeptId = DB::table('users')->where('id', $fromExpertId)->value('department_id');
                $toDeptId   = DB::table('users')->where('id', $toExpertId)->value('department_id');

                $isLastStep = $index === $referralReplies->count() - 1;

                // اگر درخواست answered شده و این آخرین مرحله است، با answered_at می‌بندیم.
                // در غیر این صورت با started_at مرحله بعدی می‌بندیم.
                $closedAt = null;
                $status   = 'active';

                if (!$isLastStep) {
                    $closedAt = $referralReplies[$index + 1]->created_at;
                    $status   = 'closed';
                } elseif ($request->status === 'answered' && $request->answered_at) {
                    $closedAt = $request->answered_at;
                    $status   = 'closed';
                }

                DB::table('request_referrals')->insert([
                    'request_id'         => $requestId,
                    'request_reply_id'   => $reply->id,
                    'step_no'            => $stepNo,
                    'from_expert_id'     => $fromExpertId,
                    'to_expert_id'       => $toExpertId,
                    'from_department_id' => $fromDeptId,
                    'to_department_id'   => $toDeptId,
                    'type'               => $type,
                    'status'             => $status,
                    'started_at'         => $reply->created_at,
                    'closed_at'          => $closedAt,
                    'created_at'         => $reply->created_at,
                    'updated_at'         => $reply->updated_at,
                ]);

                $expectedFromExpertId = $toExpertId;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('request_referrals')->truncate();
        DB::getSchemaBuilder()->dropIfExists('request_referral_warnings');
    }
};
