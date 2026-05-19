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
            $table->timestamp('ai_tutor_paid_at')->nullable()->after('remember_token');
            $table->timestamp('ai_tutor_payment_requested_at')->nullable()->after('ai_tutor_paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_tutor_paid_at', 'ai_tutor_payment_requested_at']);
        });
    }
};
