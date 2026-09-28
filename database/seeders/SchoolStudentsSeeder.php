<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class SchoolStudentsSeeder extends Seeder
{
    /**
     * Create 60 students across 5 schools (12 per school).
     * Codes: STU101-STU112, STU201-STU212, ..., STU501-STU512
     */
    public function run(): void
    {
        $schools = [
            1 => 'المدرسة الأولى',
            2 => 'المدرسة الثانية',
            3 => 'المدرسة الثالثة',
            4 => 'المدرسة الرابعة',
            5 => 'المدرسة الخامسة',
        ];

        foreach ($schools as $schoolNum => $schoolName) {
            for ($i = 1; $i <= 12; $i++) {
                $code = sprintf('STU%d%02d', $schoolNum, $i);

                Student::updateOrCreate(
                    ['code' => $code],
                    [
                        'school_name' => $schoolName,
                        'group' => 'experimental',
                        'is_active' => true,
                    ]
                );
            }

            $this->command->info("✅ {$schoolName}: STU{$schoolNum}01 → STU{$schoolNum}12");
        }

        $this->command->info("🎓 Total: 60 students created across 5 schools");
    }
}
