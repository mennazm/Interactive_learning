<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'status' => SessionStatus::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

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
