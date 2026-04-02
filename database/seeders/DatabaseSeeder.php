<?php

namespace Database\Seeders;

use App\Models\Subject;
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
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@edulink.test'],
            [
                'name' => 'Admin EduLink',
                'role' => 'admin',
                'kelas' => null,
                'password' => 'password',
            ]
        );

        $guru = User::query()->updateOrCreate(
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
                'kelas' => User::GENERAL_KELAS,
                'password' => 'password',
            ]
        );

        foreach (['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris'] as $subjectName) {
            Subject::query()->updateOrCreate(
                [
                    'name' => $subjectName,
                    'kelas' => User::GENERAL_KELAS,
                ],
                [
                    'created_by' => $guru->id ?? $admin->id ?? null,
                ]
            );
        }
    }
}
