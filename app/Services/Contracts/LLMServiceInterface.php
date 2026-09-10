<?php

namespace App\Services\Contracts;

interface LLMServiceInterface
{
    /**
     * Generate response from LLM based on system prompt and history.
     *
     * @param string $systemPrompt
     * @param array $conversationHistory
     * @param string $studentInput
     * @return array Structured JSON with reply, feedback, etc.
     */
    public function generateResponse(string $systemPrompt, array $conversationHistory, string $studentInput): array;
}
