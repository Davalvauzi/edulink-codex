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
            if (! Schema::hasColumn('users', 'ai_tutor_payment_sender_name')) {
                $table->string('ai_tutor_payment_sender_name')->nullable()->after('ai_tutor_payment_requested_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ai_tutor_payment_sender_name')) {
                $table->dropColumn('ai_tutor_payment_sender_name');
            }
        });
    }
};
