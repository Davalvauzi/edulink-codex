<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'topic')) {
                $table->string('topic', 100)->nullable()->after('title');
            }

            if (! Schema::hasColumn('materials', 'duration')) {
                $table->string('duration', 50)->nullable()->after('topic');
            }
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $columns = array_filter(['topic', 'duration'], fn (string $column) => Schema::hasColumn('materials', $column));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
