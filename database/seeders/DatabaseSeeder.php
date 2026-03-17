<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@edulink.test'],
            [
                'name' => 'Admin EduLink',
                'role' => 'admin',
                'kelas' => null,
                'password' => 'password',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'guru@edulink.test'],
            [
                'name' => 'Guru EduLink',
                'role' => 'guru',
                'kelas' => null,
                'password' => 'password',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'siswa@edulink.test'],
            [
                'name' => 'Siswa EduLink',
                'role' => 'siswa',
                'kelas' => '12',
                'password' => 'password',
            ]
        );
    }
}
