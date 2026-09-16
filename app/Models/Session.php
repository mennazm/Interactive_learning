<?php

namespace App\Models;

use App\Enums\PerformanceStatus;
use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;

    protected $table = 'learning_sessions';

    protected $fillable = [
        'student_id',
        'scenario_id',
        'session_number',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'notes',
        'performance_status',
        'strength_note',
        'improvement_note',
        'current_phase',
        'is_compensation',
        'resume_point',
    ];

    protected $casts = [
        'status' => SessionStatus::class,
        'performance_status' => PerformanceStatus::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'is_compensation' => 'boolean',
        'resume_point' => 'array',
    ];

    /**
     * Check if a session number is unlocked for a student.
     * Conditions: (1) Week schedule allows it (2) Previous session completed
     */
    public static function isUnlockedForStudent(Student $student, int $sessionNumber): bool
    {
        $availableNumbers = Setting::getAvailableSessionNumbers();
        if (!in_array($sessionNumber, $availableNumbers)) {
            return false;
        }

        if ($sessionNumber === 1) {
            return true;
        }

        // الجلسة السابقة لازم تكون مكتملة
        return self::where('student_id', $student->id)
            ->where('session_number', $sessionNumber - 1)
            ->where('status', SessionStatus::COMPLETED)
            ->exists();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    public function conversationTurns(): HasMany
    {
        return $this->hasMany(ConversationTurn::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function scopeInProgress($query)
    {
        return $query->whereNotIn('status', [SessionStatus::NOT_STARTED, SessionStatus::COMPLETED, SessionStatus::INTERRUPTED]);
    }
}
