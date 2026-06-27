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
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->unique()->nullable();
        $table->string('student_number')->nullable();
        $table->bigInteger('bale_id')->unique()->nullable();      // آیدی بله
        $table->string('bale_username')->nullable();              // یوزرنیم بله
        $table->enum('role', ['student', 'expert', 'admin'])->default('student');
        $table->foreignId('department_id')->nullable()->constrained();
        $table->boolean('is_channel_member')->default(false);
        $table->boolean('is_active')->default(true);
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'phone', 'student_number', 'bale_id',
            'bale_username', 'role', 'department_id',
            'is_channel_member', 'is_active'
        ]);
    });
}
    
};
