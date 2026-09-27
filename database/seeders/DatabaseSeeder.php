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
            ['code' => 'STU004', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الفاروق', 'is_active' => true],
            ['code' => 'STU005', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الملك خالد', 'is_active' => true],
            ['code' => 'STU006', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الملك خالد', 'is_active' => true],
            ['code' => 'STU007', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الفيصل', 'is_active' => true],
            ['code' => 'STU008', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الفيصل', 'is_active' => true],
            ['code' => 'STU009', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الصديق', 'is_active' => true],
            ['code' => 'STU010', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الصديق', 'is_active' => true],
            ['code' => 'STU011', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية أبها الأولى', 'is_active' => true],
            ['code' => 'STU012', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية أبها الأولى', 'is_active' => true],
            ['code' => 'STU013', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الفاروق', 'is_active' => true],
            ['code' => 'STU014', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الملك خالد', 'is_active' => true],
            ['code' => 'STU015', 'group' => StudentGroup::EXPERIMENTAL, 'school_name' => 'ثانوية الفيصل', 'is_active' => true],
            ['code' => 'STU016', 'group' => StudentGroup::CONTROL,      'school_name' => 'ثانوية أبها الأولى', 'is_active' => true],
            ['code' => 'STU017', 'group' => StudentGroup::CONTROL,      'school_name' => 'ثانوية الفاروق',     'is_active' => true],
            ['code' => 'STU018', 'group' => StudentGroup::CONTROL,      'school_name' => 'ثانوية الملك خالد',  'is_active' => true],
        ];

        foreach ($students as $student) {
            Student::updateOrCreate(['code' => $student['code']], $student);
        }
    }
}
