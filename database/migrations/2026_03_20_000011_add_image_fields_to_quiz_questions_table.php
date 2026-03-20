<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('explanation');
            $table->string('image_name')->nullable()->after('image_path');
            $table->text('image_url')->nullable()->after('image_name');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_name', 'image_url']);
        });
    }
};
