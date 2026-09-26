<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Scenario;
use App\Models\Session;
use App\Models\Setting;
use App\Enums\SessionStatus;
use Illuminate\Http\Request;

class ScenarioController extends Controller
{
    /**
     * List scenarios with lock status for the authenticated student.
     */
    public function index(Request $request)
    {
        $student = $request->user();
        $scenarios = Scenario::where('is_active', true)->orderBy('sort_order')->get();

        $result = $scenarios->map(function ($scenario) use ($student) {
            $sessionNumber = $scenario->number;

            // هل الطالب عنده جلسة لهذا السيناريو؟
            $session = Session::where('student_id', $student->id)
                ->where('session_number', $sessionNumber)
                ->first();

            $isCompleted = $session && $session->status === SessionStatus::COMPLETED;
            $isUnlocked = Session::isUnlockedForStudent($student, $sessionNumber);

            $lockStatus = 'locked';
            if ($isCompleted) {
                $lockStatus = 'completed';
            } elseif ($isUnlocked) {
                $lockStatus = 'available';
            }

            return array_merge($scenario->toArray(), [
                'lock_status' => $lockStatus,
                'session_id' => $session?->id,
                'performance_status' => $session?->performance_status?->value,
                'performance_label' => $session?->performance_status?->label(),
                'strength_note' => $session?->strength_note,
                'improvement_note' => $session?->improvement_note,
            ]);
        });

        // التقدم الكلي
        $completedCount = Session::where('student_id', $student->id)
            ->where('status', SessionStatus::COMPLETED)
            ->distinct()
            ->count('session_number');

        return response()->json([
            'data' => $result,
            'progress' => [
                'completed' => $completedCount,
                'total' => 8,
                'current_week' => Setting::getCurrentWeek(),
                'experiment_started' => Setting::getExperimentStartDate() !== null,
            ],
        ]);
    }

    /**
     * View scenario details.
     */
    public function show(Scenario $scenario)
    {
        return response()->json(['data' => $scenario]);
    }
}
