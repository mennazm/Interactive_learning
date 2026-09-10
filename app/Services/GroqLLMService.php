<?php

namespace App\Services;

use App\Services\Contracts\LLMServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GroqLLMService implements LLMServiceInterface
{
    /**
     * Generate response from LLM based on system prompt and history.
     */
    public function generateResponse(string $systemPrompt, array $conversationHistory, string $studentInput): array
    {
        $apiKey = config('services.groq.api_key', env('GROQ_API_KEY'));

        // If no API key configured, use intelligent mock simulator for testing
        if (empty($apiKey) || $apiKey === 'your-groq-api-key') {
            return $this->simulateConversationalResponse($studentInput, $conversationHistory, $systemPrompt);
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($conversationHistory as $turn) {
            $messages[] = [
                'role' => $turn['role'],
                'content' => $turn['content']
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $studentInput
        ];

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'openai/gpt-oss-120b',
                    'messages' => $messages,
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
                Log::error('Groq JSON Decode Error: ' . json_last_error_msg(), ['content' => $content]);
            } else {
                Log::error('Groq API Error: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Groq Exception: ' . $e->getMessage());
        }

        return $this->simulateConversationalResponse($studentInput, $conversationHistory, $systemPrompt);
    }

    /**
     * Intelligent conversation simulator that follows scenario steps.
     * Tracks progress through conversation history to avoid repetition.
     */
    private function simulateConversationalResponse(string $studentInput, array $conversationHistory, string $systemPrompt = ''): array
    {
        $input = trim(strtolower($studentInput));

        // Count student turns only (not ahmad's turns)
        $studentTurns = 0;
        foreach ($conversationHistory as $turn) {
            if ($turn['role'] === 'user') $studentTurns++;
        }

        // ─── Grammar Error Detection ───
        $errors = [];
        $feedbackType = 'none';
        $feedbackText = null;

        // Check common B1-level errors
        $errorPatterns = [
            // Age errors
            ['pattern' => '/i have (\d+) years?/i', 'fix' => 'I am $1 years old', 'error' => 'Use "I am ... years old" not "I have ... years"'],
            // Missing "is" in "My name is"
            ['pattern' => '/my name (\w+)$/i', 'fix' => 'My name is $1', 'error' => 'Say "My name is ..." with the verb "is"'],
            // "I am go" instead of "I go"
            ['pattern' => '/i am go\b/i', 'fix' => 'I go', 'error' => 'Use "I go" (simple present) not "I am go"'],
            // "He go" without -s
            ['pattern' => '/\b(he|she|it) (go|like|play|want|need|have|do|watch|read|eat|drink|come|live|work|study)\b/i', 'fix' => '$1 $2s', 'error' => 'Third person (he/she/it) needs -s: "$2s"'],
            // "Yesterday I go" - past tense
            ['pattern' => '/yesterday.*\bi (go|play|eat|watch|come|see|do|make|take)\b/i', 'fix' => 'past tense needed', 'error' => 'Use past tense with "yesterday": went, played, ate...'],
            // "I am like" 
            ['pattern' => '/i am like\b/i', 'fix' => 'I like', 'error' => 'Say "I like" not "I am like"'],
            // Very short answer (less than 3 words)
        ];

        foreach ($errorPatterns as $ep) {
            if (preg_match($ep['pattern'], $input)) {
                $errors[] = $ep['error'];
                if (!$feedbackType || $feedbackType === 'none') {
                    $feedbackType = 'recast';
                    $feedbackText = $ep['error'];
                }
                break;
            }
        }

        // Too short answer
        if (strlen($input) < 5 && empty($errors)) {
            $feedbackType = 'clarification';
            $feedbackText = 'Try to answer in a full sentence.';
        }

        // ─── Scenario Step Progression ───
        // Analyze what info we already collected from conversation history
        $collectedInfo = $this->analyzeCollectedInfo($conversationHistory, $input);

        // Build a smart reply based on what step we're at
        $reply = $this->getNextStepReply($studentTurns, $collectedInfo, $input, $feedbackType, $feedbackText);

        // Calculate task completion
        $infoCount = count(array_filter($collectedInfo));
        $totalSteps = 5;
        $completion = min(1.0, round($infoCount / $totalSteps, 2));

        return [
            'reply' => $reply,
            'is_correct' => empty($errors),
            'errors' => $errors,
            'feedback_type' => $feedbackType,
            'feedback_text' => $feedbackText,
            'task_completion' => $completion,
            'should_move_next' => $completion >= 1.0 || $studentTurns >= 7,
        ];
    }

    /**
     * Analyze conversation history to find what info student already shared.
     */
    private function analyzeCollectedInfo(array $history, string $currentInput): array
    {
        $allStudentText = $currentInput . ' ';
        foreach ($history as $turn) {
            if ($turn['role'] === 'user') {
                $allStudentText .= $turn['content'] . ' ';
            }
        }
        $text = strtolower($allStudentText);

        return [
            'name' => (bool) preg_match('/my name is|i\'m |i am (?!from|a student|interested|happy|\d)/i', $text),
            'age' => (bool) preg_match('/\d{1,2} years|i\'m \d{1,2}|i am \d{1,2}/i', $text),
            'city' => (bool) preg_match('/from |live in |i\'m in |abha|riyadh|jeddah|mecca|medina|dammam|khamis|taif/i', $text),
            'hobby' => (bool) preg_match('/like |love |enjoy |play |watch |read |football|soccer|gaming|swim|cook|draw|music|sport/i', $text),
            'opinion' => (bool) preg_match('/i think|i believe|my favorite|i prefer|because|interesting|boring|amazing|best/i', $text),
        ];
    }

    /**
     * Generate the next appropriate reply based on conversation progress.
     */
    private function getNextStepReply(int $studentTurns, array $info, string $input, string &$feedbackType, ?string &$feedbackText): string
    {
        $input_lower = strtolower($input);

        // Handle recast — incorporate correction naturally
        if ($feedbackType === 'recast') {
            if (preg_match('/i have (\d+) years/i', $input, $m)) {
                $base = "Oh, so you are {$m[1]} years old! That's great.";
                if (!$info['city']) return $base . " Where do you live?";
                if (!$info['hobby']) return $base . " What do you enjoy doing in your free time?";
                return $base . " Tell me something interesting about yourself!";
            }
        }

        // Step-by-step conversation flow — only ask what we DON'T know yet
        if (!$info['name']) {
            // First response to greeting
            if ($studentTurns === 0) {
                return "Nice to meet you! Can you tell me your name?";
            }
            return "I'd love to know your name! What should I call you?";
        }

        // Extract name for personalization
        $name = "my friend";
        if (preg_match('/my name is (\w+)|i\'m (\w+)|i am (\w+)/i', $input, $m)) {
            $name = ucfirst($m[1] ?: ($m[2] ?: $m[3]));
        }

        if (!$info['age'] && !$info['city']) {
            return "Great to meet you, {$name}! Where are you from? And how old are you?";
        }

        if (!$info['city']) {
            return "That's nice, {$name}! Which city do you live in?";
        }

        if (!$info['age']) {
            return "Cool! And how old are you, {$name}?";
        }

        if (!$info['hobby']) {
            return "Wonderful! What do you like to do in your free time? Any hobbies or sports?";
        }

        if (!$info['opinion']) {
            // Ask follow-up about the hobby they mentioned
            if (preg_match('/football|soccer/i', $input)) {
                return "Football is very popular! Who is your favorite team? And why do you like them?";
            }
            if (preg_match('/gaming|game/i', $input)) {
                return "Gaming is fun! What's your favorite game? Tell me why you like it.";
            }
            if (preg_match('/read|book/i', $input)) {
                return "Reading is wonderful! What kind of books do you prefer? Why?";
            }
            return "That sounds interesting! Why do you enjoy that? What makes it special for you?";
        }

        // All info collected — wrap up with encouragement
        $wrapUps = [
            "Excellent job! You've shared so much about yourself. Your English is really good! It was great talking to you.",
            "Wonderful conversation! You expressed yourself very clearly. I enjoyed learning about you. Great job!",
            "Fantastic! You did a great job telling me about yourself. Keep practicing and you'll get even better!",
        ];

        return $wrapUps[$studentTurns % count($wrapUps)];
    }
}
