<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Enums\StudentGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin / Researcher Account
        User::updateOrCreate(
            ['email' => 'researcher@kku.edu.sa'],
            [
                'name' => 'Ahmad Al-Hayani',
                'password' => Hash::make('password123'),
            ]
        );

        // Seed 8 Scenarios
        $this->call(ScenarioSeeder::class);

        // Seed Initial Test Students
        $students = [
            ['code' => 'STU001', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية أبها الأولى', 'is_active' => true],
            ['code' => 'STU002', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية أبها الأولى', 'is_active' => true],
            ['code' => 'STU003', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الفاروق', 'is_active' => true],
        ];

        foreach ($students as $student) {
            Student::updateOrCreate(['code' => $student['code']], $student);
        }
    }
}
