<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Session;
use App\Services\ConversationService;
use App\Services\Contracts\TTSServiceInterface;

use App\Enums\SessionStatus;

class ConversationController extends Controller
{
    public function __construct(
        private TTSServiceInterface $tts,
    ) {}

    /**
     * Process student speech and return Ahmad's reply with optional audio.
     */
    public function speak(Request $request, Session $session, ConversationService $conversationService)
    {
        $request->validate([
            'text' => 'required|string',
            'confidence' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($session->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if another device is already using this session
        $currentTokenId = $request->user()->currentAccessToken()->id;
        if (
            $session->active_token_id &&
            $session->active_token_id !== $currentTokenId &&
            $session->status === SessionStatus::IN_PROGRESS
        ) {
            return response()->json([
                'message' => 'This session is currently active on another device. Please close it there first.',
                'error_code' => 'SESSION_ACTIVE_ON_OTHER_DEVICE',
            ], 409);
        }

        $confidence = $request->input('confidence', 1.0);
        $result = $conversationService->processStudentInput($session, $request->text, $confidence);

        // Generate audio from ElevenLabs (empty string if not configured)
        $audioBase64 = $this->tts->synthesize($result['reply'] ?? '');
        $result['audio'] = $audioBase64;
        $result['audio_format'] = !empty($audioBase64) ? 'mp3' : null;

        return response()->json(['data' => $result]);
    }

    /**
     * Standalone TTS endpoint — convert text to audio.
     */
    public function synthesize(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $audioBase64 = $this->tts->synthesize($request->text);

        if (empty($audioBase64)) {
            return response()->json([
                'data' => [
                    'audio' => '',
                    'audio_format' => null,
                    'message' => 'TTS not configured. Use browser speech synthesis.',
                ]
            ]);
        }

        return response()->json([
            'data' => [
                'audio' => $audioBase64,
                'audio_format' => 'mp3',
            ]
        ]);
    }
}
