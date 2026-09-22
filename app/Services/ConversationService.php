<?php

namespace App\Services;

use App\Models\Scenario;
use App\Models\Session;
use App\Models\ConversationTurn;
use App\Enums\FeedbackType;
use App\Enums\SessionStatus;
use App\Services\Contracts\LLMServiceInterface;
use Illuminate\Support\Facades\Log;

class ConversationService
{
    public function __construct(
        private LLMServiceInterface $llmService
    ) {}

    /**
     * Start a session — set status to INTRO and return Ahmad's greeting.
     */
    public function startSession(Session $session): array
    {
        $session->update([
            'status' => SessionStatus::INTRO,
            'started_at' => now(),
        ]);

        $scenario = $session->scenario;

        // Intro greeting: welcome + today's topic (NOT the opening question)
        // The opening question is asked later when entering CONVERSATION phase
        $topicTitle = $scenario ? $scenario->title : 'speaking English';
        $greeting = "Hello! I'm Ahmad, your conversation partner. Welcome! Today, we will practice: {$topicTitle}. First, let me show you some useful words for our conversation.";

        // Save Ahmad's first turn
        ConversationTurn::create([
            'session_id' => $session->id,
            'turn_number' => 1,
            'speaker' => 'avatar',
            'text_content' => $greeting,
            'feedback_type' => FeedbackType::NONE,
        ]);

        return [
            'greeting' => $greeting,
            'reply' => $greeting,
            'scenario' => $scenario ? [
                'title' => $scenario->title,
                'title_ar' => $scenario->title_ar,
                'topic' => $scenario->topic,
                'number' => $scenario->number,
                'communicative_function' => $scenario->communicative_function,
                'completion_criteria' => $scenario->completion_criteria,
                'vocabulary' => $scenario->vocabulary,
                'b1_axes' => $scenario->b1_axes,
            ] : null,
            'session' => $session->fresh(),
            'phase' => $this->getPhaseInfo($session->fresh()),
        ];
    }

    /**
     * Advance session to next phase.
     */
    public function advancePhase(Session $session): array
    {
        $currentStatus = $session->status;
        $nextPhase = $currentStatus->next();

        if (!$nextPhase) {
            return $this->endSession($session);
        }

        $session->update(['status' => $nextPhase]);
        $session->refresh();

        // Generate phase-specific Ahmad message
        $phaseMessage = $this->getPhaseMessage($nextPhase, $session);

        if ($phaseMessage) {
            $turnCount = ConversationTurn::where('session_id', $session->id)->count();
            ConversationTurn::create([
                'session_id' => $session->id,
                'turn_number' => $turnCount + 1,
                'speaker' => 'avatar',
                'text_content' => $phaseMessage,
                'feedback_type' => FeedbackType::NONE,
            ]);
        }

        return [
            'phase' => $this->getPhaseInfo($session),
            'message' => $phaseMessage,
            'session' => $session,
        ];
    }

    /**
     * Get phase info with timing data.
     */
    public function getPhaseInfo(Session $session): array
    {
        $status = $session->status;
        $startedAt = $session->started_at;
        $elapsed = $startedAt ? (int) $startedAt->diffInSeconds(now()) : 0;
        $totalDuration = SessionStatus::totalDurationSeconds(); // 40 min

        // Calculate phase timeline
        $phases = SessionStatus::sessionPhases();
        $phaseTimeline = [];
        $cumulative = 0;
        $currentPhaseIndex = 0;

        foreach ($phases as $i => $phase) {
            $duration = $phase->durationSeconds();
            $phaseTimeline[] = [
                'key' => $phase->value,
                'label' => $phase->label(),
                'duration_seconds' => $duration,
                'starts_at' => $cumulative,
                'ends_at' => $cumulative + $duration,
                'is_current' => $phase === $status,
                'is_done' => $this->isPhaseCompleted($phase, $status),
            ];
            if ($phase === $status) $currentPhaseIndex = $i;
            $cumulative += $duration;
        }

        // Calculate time remaining in current phase
        $currentPhaseStart = $phaseTimeline[$currentPhaseIndex]['starts_at'] ?? 0;
        $currentPhaseDuration = $status->durationSeconds();
        $elapsedInPhase = max(0, $elapsed - $currentPhaseStart);
        $remainingInPhase = max(0, $currentPhaseDuration - $elapsedInPhase);

        return [
            'current_phase' => $status->value,
            'current_phase_label' => $status->label(),
            'phase_duration' => $currentPhaseDuration,
            'elapsed_total' => $elapsed,
            'elapsed_in_phase' => $elapsedInPhase,
            'remaining_in_phase' => $remainingInPhase,
            'remaining_total' => max(0, $totalDuration - $elapsed),
            'timeline' => $phaseTimeline,
            'should_auto_advance' => $remainingInPhase <= 0 && $status !== SessionStatus::COMPLETED,
        ];
    }

    /**
     * End session and calculate duration.
     */
    public function endSession(Session $session): array
    {
        $startedAt = $session->started_at ?? now();
        $duration = (int) $startedAt->diffInSeconds(now());

        // Generate closing message
        $closingMessage = $this->generateClosingMessage($session);

        if ($closingMessage) {
            $turnCount = ConversationTurn::where('session_id', $session->id)->count();
            ConversationTurn::create([
                'session_id' => $session->id,
                'turn_number' => $turnCount + 1,
                'speaker' => 'avatar',
                'text_content' => $closingMessage,
                'feedback_type' => FeedbackType::NONE,
            ]);
        }

        $session->update([
            'status' => SessionStatus::COMPLETED,
            'ended_at' => now(),
            'duration_seconds' => $duration,
        ]);

        return [
            'phase' => ['current_phase' => 'completed', 'remaining_total' => 0],
            'message' => $closingMessage,
            'session' => $session->fresh(),
            'done' => true,
        ];
    }

    private function isPhaseCompleted(SessionStatus $phase, SessionStatus $current): bool
    {
        $order = ['intro', 'vocab', 'conversation', 'feedback', 'closing', 'completed'];
        return array_search($phase->value, $order) < array_search($current->value, $order);
    }

    /**
     * Get Ahmad's message for a specific phase transition.
     */
    private function getPhaseMessage(SessionStatus $phase, Session $session): ?string
    {
        $scenario = $session->scenario;

        return match($phase) {
            SessionStatus::VOCAB => "Great! Before we start our conversation, let me share some useful words for today's topic. Take a moment to review them.",
            SessionStatus::CONVERSATION => $scenario && $scenario->scenario_module
                ? (preg_match('/Opening question:\s*"([^"]+)"/i', $scenario->scenario_module, $m) ? $m[1] : "Let's start our conversation now!")
                : "Let's start our conversation now!",
            SessionStatus::FEEDBACK => "Well done! Let me share some feedback on how you did today.",
            SessionStatus::CLOSING => "Great session! Let me give you a quick summary.",
            SessionStatus::COMPLETED => null,
            default => null,
        };
    }

    /**
     * Generate closing message with performance summary.
     */
    private function generateClosingMessage(Session $session): string
    {
        $studentTurns = ConversationTurn::where('session_id', $session->id)
            ->where('speaker', 'student')->count();
        $totalTurns = ConversationTurn::where('session_id', $session->id)->count();

        return "Good work today! You completed this speaking session. You had {$studentTurns} speaking turns. Keep practicing and I will see you next time!";
    }

    /**
     * Process student speech and generate Ahmad's response.
     */
    public function processStudentInput(Session $session, string $text, float $confidence = 0.0): array
    {
        $scenario = $session->scenario;
        $systemPrompt = $this->buildSystemPrompt($scenario, $session);
        $conversationHistory = $this->getConversationHistory($session);

        // Get current turn count for this session
        $turnCount = ConversationTurn::where('session_id', $session->id)->count();

        // Save student turn
        $studentTurn = ConversationTurn::create([
            'session_id' => $session->id,
            'turn_number' => $turnCount + 1,
            'speaker' => 'student',
            'text_content' => $text,
            'asr_confidence' => $confidence,
        ]);

        // Generate Ahmad's response
        $startTime = microtime(true);
        $response = $this->llmService->generateResponse($systemPrompt, $conversationHistory, $text);
        $latencyMs = (int) ((microtime(true) - $startTime) * 1000);

        // Map feedback_level / next_action to FeedbackType
        $feedbackType = $this->mapFeedbackType($response);

        // Save Ahmad's turn
        $ahmadTurn = ConversationTurn::create([
            'session_id' => $session->id,
            'turn_number' => $turnCount + 2,
            'speaker' => 'avatar',
            'text_content' => $response['reply'] ?? '',
            'feedback_type' => $feedbackType,
            'feedback_text' => $response['text_show'] ?? $response['feedback_text'] ?? null,
            'is_correct' => ($response['feedback_level'] ?? 0) === 0,
            'attempt_number' => $this->getCurrentAttempt($session),
            'llm_response_json' => $response,
            'latency_ms' => $latencyMs,
        ]);

        return [
            'reply' => $response['reply'] ?? '',
            'emotion' => $response['emotion'] ?? 'neutral',
            'feedback_type' => $feedbackType->value,
            'feedback_text' => $response['text_show'] ?? $response['feedback_text'] ?? null,
            'feedback_target' => $response['feedback_target'] ?? 'none',
            'mastery_state' => $response['mastery_state'] ?? 'not_yet',
            'task_completion' => $response['task_completion'] ?? 0.0,
            'done_phase' => $response['done_phase'] ?? false,
            'next_action' => $response['next_action'] ?? 'ask',
            'turn_number' => $ahmadTurn->turn_number,
            'score_hint' => $response['score_hint'] ?? null,
        ];
    }

    /**
     * Map response to FeedbackType enum.
     */
    private function mapFeedbackType(array $response): FeedbackType
    {
        // Check next_action field (new schema)
        $action = $response['next_action'] ?? null;
        if ($action === 'recast') return FeedbackType::RECAST;
        if ($action === 'clarify') return FeedbackType::CLARIFICATION;
        if ($action === 'model') return FeedbackType::MODEL;

        // Check feedback_type field (old schema fallback)
        $type = $response['feedback_type'] ?? 'none';
        if ($type === 'recast') return FeedbackType::RECAST;
        if ($type === 'clarification') return FeedbackType::CLARIFICATION;
        if ($type === 'model') return FeedbackType::MODEL;

        // Check feedback_level (new schema)
        $level = $response['feedback_level'] ?? 0;
        if ($level === 1) return FeedbackType::RECAST;
        if ($level === 2) return FeedbackType::CLARIFICATION;
        if ($level === 3) return FeedbackType::MODEL;

        return FeedbackType::NONE;
    }

    /**
     * Get the current attempt number for the active phase.
     */
    private function getCurrentAttempt(Session $session): int
    {
        $lastAttempt = ConversationTurn::where('session_id', $session->id)
            ->where('speaker', 'avatar')
            ->whereNotNull('attempt_number')
            ->orderBy('id', 'desc')
            ->value('attempt_number');

        return min(3, ($lastAttempt ?? 0) + 1);
    }

    /**
     * Build the complete system prompt: Master Prompt + Scenario Module + Session State.
     * Based on the design documents' 3-layer architecture.
     */
    private function buildSystemPrompt(?Scenario $scenario, Session $session): string
    {
        // ═══ Layer 1: Master Prompt (ثابت لكل الجلسات) ═══
        $masterPrompt = <<<'PROMPT'
You are Ahmad, a friendly, patient, and encouraging AI conversational partner designed for Saudi high school students learning English at CEFR B1 level.

## Identity & Personality
- Patient, encouraging, clear, professional, never sarcastic
- Never compare students to each other
- Respect thinking time — do not rush or interrupt
- You are a conversational PARTNER, not an examiner or teacher replacement

## Language Rules
- Communicate in English only, at CEFR B1 level
- Use clear, natural, age-appropriate language with familiar vocabulary
- Arabic is ONLY allowed for brief technical guidance in Session 1 if the mic/interface fails

## Dialogue Rules (R1-R10)
- R1: Ask ONE clear question per turn only. Never ask multiple questions.
- R2: Keep your responses SHORT. Priority is for the student to speak.
- R3: Give reasonable thinking time. Do not interrupt.
- R4: If the student gives a very short answer, ask a follow-up before correcting.
- R5: If speech is unclear, ask for clarification instead of guessing.
- R6: If off-topic, acknowledge briefly then redirect: "That is interesting. Let's return to today's speaking task."
- R7: Never provide a long model paragraph before the student's first attempt.
- R8: Never show scores, rankings, or competitive comparisons.
- R9: Arabic only for brief technical help in Session 1.
- R10: Never ask for or store sensitive personal data (full name, ID, phone, address).

## Feedback Protocol (3-Level Graduated Scaffolding)
- Level 1 (Recast): Reformulate the error naturally in your response. Use when the error is minor and doesn't block meaning.
- Level 2 (Clarification): Point out where to improve and give a hint or two choices. Use when the error persists or communication breaks down.
- Level 3 (Model): Provide the correct phrase and ask the student to rephrase in their own words. Use on the 3rd attempt.
- Maximum 3 attempts per task. Reduce support immediately after success (fading).
- Correct at most 1-2 points per feedback cycle.

## Response Format
You MUST respond in valid JSON with this exact schema:
{
  "reply": "Ahmad's spoken response in English",
  "emotion": "neutral | smile | encourage | think",
  "feedback_level": 0,
  "feedback_target": "none | grammar_vocab | discourse | pronunciation | interactive",
  "text_show": "optional short support text for display",
  "done_phase": false,
  "mastery_state": "not_yet | partial | met",
  "score_hint": {
    "grammar_vocab": 0,
    "discourse": 0,
    "pronunciation": 0,
    "interactive": 0
  },
  "next_action": "ask | recast | clarify | model | retry | close"
}

Notes:
- "reply" = what Ahmad says aloud (English)
- "emotion" = Ahmad's facial expression
- "feedback_level" = 0 (none), 1 (recast), 2 (clarification), 3 (model)
- "score_hint" = internal operational indicator (0-5), NOT the student's research grade
- "next_action" = what to do next in the conversation flow
- "done_phase" = true when the current task/phase is complete
- "mastery_state" = "met" when the student has achieved the completion criteria
PROMPT;

        // ═══ Layer 2: Scenario Module (متغير حسب السيناريو) ═══
        if ($scenario) {
            $masterPrompt .= "\n\n## Current Scenario: {$scenario->title} ({$scenario->title_ar})\n";
            $masterPrompt .= "Scenario Code: SC0{$scenario->number}\n";
            $masterPrompt .= "Topic: {$scenario->topic}\n";
            $masterPrompt .= "Communicative Goal: {$scenario->communicative_function}\n";

            if (!empty($scenario->system_prompt)) {
                $masterPrompt .= "\n### Scenario Instructions\n{$scenario->system_prompt}\n";
            }

            if (!empty($scenario->scenario_module)) {
                $masterPrompt .= "\n### Conversation Flow\n{$scenario->scenario_module}\n";
            }

            if (!empty($scenario->completion_criteria)) {
                $masterPrompt .= "\n### Completion Criteria\n{$scenario->completion_criteria}\n";
            }

            if (!empty($scenario->vocabulary) && is_array($scenario->vocabulary)) {
                $vocabList = implode(', ', $scenario->vocabulary);
                $masterPrompt .= "\n### Target Vocabulary\n{$vocabList}\n";
            }

            if (!empty($scenario->b1_axes) && is_array($scenario->b1_axes)) {
                $axesList = implode(', ', $scenario->b1_axes);
                $masterPrompt .= "\n### Assessment Axes\n{$axesList}\n";
            }
        }

        // ═══ Layer 3: Session State (ديناميكي) ═══
        $turnCount = ConversationTurn::where('session_id', $session->id)->count();
        $studentTurns = ConversationTurn::where('session_id', $session->id)->where('speaker', 'student')->count();
        $masterPrompt .= "\n\n## Session State\n";
        $masterPrompt .= "- Session #{$session->session_number}\n";
        $masterPrompt .= "- Total turns so far: {$turnCount}\n";
        $masterPrompt .= "- Student responses so far: {$studentTurns}\n";

        return $masterPrompt;
    }

    /**
     * Get formatted conversation history for context.
     */
    private function getConversationHistory(Session $session): array
    {
        $turns = ConversationTurn::where('session_id', $session->id)
            ->orderBy('turn_number', 'asc')
            ->get();

        $history = [];
        foreach ($turns as $turn) {
            $history[] = [
                'role' => $turn->speaker === 'student' ? 'user' : 'assistant',
                'content' => $turn->text_content ?? '',
            ];
        }

        return $history;
    }
}
