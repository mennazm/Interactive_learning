<?php

namespace App\Models;

use App\Enums\FeedbackType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationTurn extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'turn_number',
        'speaker',
        'text_content',
        'audio_url',
        'asr_confidence',
        'feedback_type',
        'feedback_text',
        'is_correct',
        'attempt_number',
        'llm_response_json',
        'latency_ms',
    ];

    protected $casts = [
        'feedback_type' => FeedbackType::class,
        'asr_confidence' => 'float',
        'is_correct' => 'boolean',
        'attempt_number' => 'integer',
        'llm_response_json' => 'array',
        'latency_ms' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }
}
