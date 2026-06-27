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
        Schema::create('request_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expert_id')->constrained('users');
            $table->text('body')->nullable();          // متن پاسخ
            $table->string('file_path')->nullable();   // فایل پیوست پاسخ
            $table->string('file_name')->nullable();
            $table->boolean('is_referral')->default(false); // ارجاع؟
            $table->foreignId('referred_to')->nullable()->constrained('users'); // ارجاع به کی
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_replies');
    }
};
