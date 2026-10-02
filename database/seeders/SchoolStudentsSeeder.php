<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Enums\StudentGroup;

class SchoolStudentsSeeder extends Seeder
{
    /**
     * Create students across 5 schools with varying sizes.
     * Code format: STU{school}-{number} (e.g., STU1-001, STU2-015)
     *
     * School 1: ثانوية الملك عبدالله  — 15 تجريبية (001-015) + 15 ضابطة (016-030) = 30
     * School 2: ثانوية الموهوبين       — 15 تجريبية (001-015) + 15 ضابطة (016-030) = 30
     * School 3: ثانوية النموذجية       — 10 تجريبية (001-010) + 10 ضابطة (011-020) = 20
     * School 4: ثانوية مدينة سلطان    — 10 تجريبية (001-010) + 10 ضابطة (011-020) = 20
     * School 5: ثانوية العرين          — 5 تجريبية  (001-005) + 5 ضابطة  (006-010) = 10
     *
     * Total: 110 students
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
                'experimental' => ['from' => 1, 'to' => 15],
                'control' => ['from' => 16, 'to' => 30],
            ],
            2 => [
                'name' => 'ثانوية الموهوبين',
                'experimental' => ['from' => 1, 'to' => 15],
                'control' => ['from' => 16, 'to' => 30],
            ],
            3 => [
                'name' => 'ثانوية النموذجية',
                'experimental' => ['from' => 1, 'to' => 10],
                'control' => ['from' => 11, 'to' => 20],
            ],
            4 => [
                'name' => 'ثانوية مدينة سلطان',
                'experimental' => ['from' => 1, 'to' => 10],
                'control' => ['from' => 11, 'to' => 20],
            ],
            5 => [
                'name' => 'ثانوية العرين',
                'experimental' => ['from' => 1, 'to' => 5],
                'control' => ['from' => 6, 'to' => 10],
            ],
        ];

        $totalCreated = 0;

        foreach ($schools as $schoolNum => $school) {
            $schoolCount = 0;

            // المجموعة التجريبية
            for ($i = $school['experimental']['from']; $i <= $school['experimental']['to']; $i++) {
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

            $expCount = $school['experimental']['to'] - $school['experimental']['from'] + 1;

            // المجموعة الضابطة
            for ($i = $school['control']['from']; $i <= $school['control']['to']; $i++) {
                $code = sprintf('STU%d-%03d', $schoolNum, $i);

                Student::updateOrCreate(
                    ['code' => $code],
                    [
                        'school_name' => $school['name'],
                        'group' => StudentGroup::CONTROL,
                        'is_active' => true,
                    ]
                );
                $schoolCount++;
            }

            $ctrlCount = $school['control']['to'] - $school['control']['from'] + 1;
            $totalCreated += $schoolCount;

            $this->command->info("✅ {$school['name']} (STU{$schoolNum}): {$expCount} تجريبية + {$ctrlCount} ضابطة = {$schoolCount} طالب");
        }

        $this->command->info("🎓 الإجمالي: {$totalCreated} طالب في " . count($schools) . " مدارس");
    }
}
