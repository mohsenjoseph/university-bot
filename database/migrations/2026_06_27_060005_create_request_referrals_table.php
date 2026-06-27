<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * این جدول تاریخچه‌ی کامل ارجاع هر درخواست را نگه می‌دارد.
     * هر ردیف = یک «مرحله» ارجاع (از یک کارشناس به کارشناس دیگر).
     * این جدول مکمل request_replies است، نه جایگزین آن:
     * request_replies همچنان متن پاسخ/یادداشت هر مرحله را نگه می‌دارد،
     * در حالی که request_referrals برای گزارش‌گیری ساختاریافته
     * (مسیر ارجاع، زمان هر مرحله، تعداد ارجاع) بهینه شده است.
     */
    public function up(): void
    {
        Schema::create('request_referrals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')->constrained()->cascadeOnDelete();

            // ردیف ایجاد‌کننده‌ی این مرحله در request_replies (اختیاری، برای ردیابی متن/فایل ارجاع)
            $table->foreignId('request_reply_id')->nullable()
                ->constrained('request_replies')->nullOnDelete();

            // شماره مرحله در مسیر ارجاع این درخواست (۱، ۲، ۳، ...)
            $table->unsignedInteger('step_no');

            // مبدا و مقصد ارجاع
            $table->foreignId('from_expert_id')->nullable()->constrained('users');
            $table->foreignId('to_expert_id')->constrained('users');

            // حوزه‌ی مبدا و مقصد در لحظه‌ی ارجاع (snapshot، چون department_id کاربر می‌تواند بعدا تغییر کند)
            $table->foreignId('from_department_id')->nullable()->constrained('departments');
            $table->foreignId('to_department_id')->nullable()->constrained('departments');

            // نوع این مرحله: ارجاع عادی، بازگشت توسط ارجاع‌دهنده (recall)، عودت به ارجاع‌دهنده (return)،
            // یا chain_gap (فقط برای داده‌های تاریخی backfill‌شده که بین دو مرحله یک رویداد ثبت‌نشده افتاده)
            $table->enum('type', ['referral', 'recall', 'return', 'chain_gap'])->default('referral');

            // وضعیت این مرحله: همچنان نزد کارشناس مقصد است (active) یا بسته شده چون مرحله بعدی شروع شده یا پاسخ نهایی داده شده (closed)
            $table->enum('status', ['active', 'closed'])->default('active');

            // زمان شروع این مرحله (لحظه‌ی ثبت ارجاع) و زمان پایان آن (وقتی مرحله بعدی باز شود یا پاسخ نهایی ثبت شود)
            $table->timestamp('started_at');
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(['request_id', 'step_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_referrals');
    }
};
