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
                'experimental' => ['from' => 1, 'to' => 20],
               
            ],
            2 => [
                'name' => 'ثانوية الموهوبين',
                'experimental' => ['from' => 1, 'to' => 15],
               
            ],
            3 => [
                'name' => 'ثانوية النموذجية',
                'experimental' => ['from' => 1, 'to' => 10],
               
            ],
            4 => [
                'name' => 'ثانوية مدينة سلطان',
                'experimental' => ['from' => 1, 'to' => 10],
                
            ],
            5 => [
                'name' => 'ثانوية العرين',
                'experimental' => ['from' => 1, 'to' => 5],
                
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

            $this->command->info("✅ {$school['name']} (STU{$schoolNum}): {$expCount} تجريبية ");
        }

        $this->command->info("🎓 الإجمالي: {$totalCreated} طالب في " . count($schools) . " مدارس");
    }
}
