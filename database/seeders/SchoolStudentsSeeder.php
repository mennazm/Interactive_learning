<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Enums\StudentGroup;

class SchoolStudentsSeeder extends Seeder
{
    /**
     * Create experimental students across 5 schools.
     * Code format: STU{school}-{number} (e.g., STU1-001, STU2-015)
     *
     * School 1: ثانوية الملك عبدالله  — 20 طالب (001-020)
     * School 2: ثانوية الموهوبين       — 15 طالب (001-015)
     * School 3: ثانوية النموذجية       — 10 طالب (001-010)
     * School 4: ثانوية مدينة سلطان    — 10 طالب (001-010)
     * School 5: ثانوية العرين          — 5 طالب  (001-005)
     *
     * Total: 60 students
     */
    public function run(): void
    {
        // حذف الطلبة القدام أولاً
        $oldCount = Student::count();
        Student::query()->delete();
        $this->command->info("🗑️ تم حذف {$oldCount} طالب قديم");

        $schools = [
            1 => [
                'name' => 'ثانوية الملك عبدالله',
                'from' => 1, 'to' => 20,
            ],
            2 => [
                'name' => 'ثانوية الموهوبين',
                'from' => 1, 'to' => 15,
            ],
            3 => [
                'name' => 'ثانوية النموذجية',
                'from' => 1, 'to' => 10,
            ],
            4 => [
                'name' => 'ثانوية مدينة سلطان',
                'from' => 1, 'to' => 10,
            ],
            5 => [
                'name' => 'ثانوية العرين',
                'from' => 1, 'to' => 5,
            ],
        ];

        $totalCreated = 0;

        foreach ($schools as $schoolNum => $school) {
            $schoolCount = 0;

            for ($i = $school['from']; $i <= $school['to']; $i++) {
                $code = sprintf('STU%d-%03d', $schoolNum, $i);

                Student::updateOrCreate(
                    ['code' => $code],
                    [
                        'school_name' => $school['name'],
                        'group' => StudentGroup::EXPERIMENTAL,
                        'is_active' => true,
                    ]
                );
                $schoolCount++;
            }

            $totalCreated += $schoolCount;
            $this->command->info("✅ {$school['name']} (STU{$schoolNum}): {$schoolCount} طالب");
        }

        $this->command->info("🎓 الإجمالي: {$totalCreated} طالب في " . count($schools) . " مدارس");
    }
}
