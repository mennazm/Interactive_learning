<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\Session as LearningSession;
use App\Models\ConversationTurn;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStudents = Student::where('is_active', true)->count();
        $experimentalCount = Student::where('group', 'experimental')->where('is_active', true)->count();
        $controlCount = Student::where('group', 'control')->where('is_active', true)->count();

        $totalSessions = LearningSession::count();
        $completedSessions = LearningSession::where('status', 'completed')->count();

        $totalTurns = ConversationTurn::count();
        $correctTurns = ConversationTurn::where('is_correct', true)->count();

        $avgDuration = LearningSession::where('status', 'completed')
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');
        $avgMin = $avgDuration ? round($avgDuration / 60, 1) : 0;

        return [
            Stat::make('إجمالي الطلبة', $totalStudents)
                ->description("تجريبية: {$experimentalCount} | ضابطة: {$controlCount}")
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('الجلسات المكتملة', "{$completedSessions} / {$totalSessions}")
                ->description('من إجمالي الجلسات')
                ->icon('heroicon-o-academic-cap')
                ->color('success'),

            Stat::make('متوسط مدة الجلسة', "{$avgMin} دقيقة")
                ->description('للجلسات المكتملة')
                ->icon('heroicon-o-clock')
                ->color('info'),

            Stat::make('نسبة الإجابات الصحيحة', $totalTurns > 0 ? round(($correctTurns / $totalTurns) * 100) . '%' : '0%')
                ->description("{$correctTurns} صحيحة من {$totalTurns} دور")
                ->icon('heroicon-o-check-circle')
                ->color('warning'),
        ];
    }
}
