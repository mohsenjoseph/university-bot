<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
{
    Schema::create('requests', function (Blueprint $table) {
        $table->id();
        $table->string('tracking_code')->unique();   // کد پیگیری
        $table->foreignId('requester_id')->constrained('users');  // درخواست‌دهنده
        $table->string('applicant_phone')->nullable(); // شماره نفر دیگه (اگه برای دیگران)
        $table->string('applicant_student_number')->nullable();
        $table->foreignId('department_id')->constrained(); // حوزه
        $table->foreignId('assigned_expert_id')->nullable()->constrained('users'); // کارشناس
        $table->text('body');                        // متن درخواست
        $table->enum('status', [
            'pending',      // در انتظار بررسی
            'seen',         // مشاهده شده
            'answered',     // پاسخ داده شده
            'referred'      // ارجاع داده شده
        ])->default('pending');
        $table->boolean('is_for_other')->default(false); // برای دیگران؟
        $table->timestamp('seen_at')->nullable();
        $table->timestamp('answered_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
