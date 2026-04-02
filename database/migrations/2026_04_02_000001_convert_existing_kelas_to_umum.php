<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('kelas', ['10', '11', '12'])
            ->update(['kelas' => 'umum']);

        DB::table('subjects')
            ->whereIn('kelas', ['10', '11', '12'])
            ->update(['kelas' => 'umum']);

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY kelas ENUM('umum') NULL");
        DB::statement("ALTER TABLE subjects MODIFY kelas ENUM('umum') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY kelas ENUM('10', '11', '12') NULL");
        DB::statement("ALTER TABLE subjects MODIFY kelas ENUM('10', '11', '12') NOT NULL");
    }
};
