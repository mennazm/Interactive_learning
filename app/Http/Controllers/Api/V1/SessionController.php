<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\ConversationTurn;
use App\Enums\SessionStatus;
use App\Services\ConversationService;

class SessionController extends Controller
{
    /**
     * List student's sessions or create a new session for a scenario.
     */
    public function index(Request $request)
    {
        $sessions = Session::where('student_id', $request->user()->id)
            ->with('scenario')
            ->orderBy('session_number', 'asc')
            ->get();

        return response()->json(['data' => $sessions]);
    }

    /**
     * Create/Select a session for a scenario.
     */
    public function store(Request $request)
    {
        $request->validate([
            'scenario_id' => 'required|exists:scenarios,id',
            'session_number' => 'nullable|integer|min:1|max:10',
        ]);

        $sessionNumber = $request->input('session_number', 1);

        $session = Session::create([
            'student_id' => $request->user()->id,
            'scenario_id' => $request->scenario_id,
            'session_number' => $sessionNumber,
            'status' => SessionStatus::NOT_STARTED,
        ]);

        $session->load('scenario');

        return response()->json([
            'message' => 'Session created successfully',
            'data' => $session,
        ], 201);
    }

    /**
     * Start a session.
     */
    public function start(Request $request, Session $session, ConversationService $conversationService)
    {
        if ($session->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = $conversationService->startSession($session);

        // Generate audio for Ahmad's greeting
        $tts = app(\App\Services\Contracts\TTSServiceInterface::class);
        $audioBase64 = $tts->synthesize($result['reply'] ?? '');
        $result['audio'] = $audioBase64;
        $result['audio_format'] = !empty($audioBase64) ? 'mp3' : null;

        return response()->json([
            'message' => 'Session started',
            'data' => $result,
            'session' => $session->fresh(),
        ]);
    }

    /**
     * End a session.
     */
    public function end(Request $request, Session $session)
    {
        if ($session->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $startedAt = $session->started_at ?? now();
        $duration = (int) $startedAt->diffInSeconds(now());

        $session->update([
            'status' => SessionStatus::COMPLETED,
            'ended_at' => now(),
            'duration_seconds' => $duration,
        ]);

        // Calculate real stats
        $totalTurns = ConversationTurn::where('session_id', $session->id)->count();
        $studentTurns = ConversationTurn::where('session_id', $session->id)
            ->where('speaker', 'student')->count();
        $correctTurns = ConversationTurn::where('session_id', $session->id)
            ->where('speaker', 'avatar')
            ->where('is_correct', true)->count();

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;

        return response()->json([
            'message' => 'Session completed successfully',
            'session' => $session->fresh(),
            'stats' => [
                'total_turns' => $totalTurns,
                'student_turns' => $studentTurns,
                'correct_turns' => $correctTurns,
                'duration_seconds' => $duration,
                'duration_formatted' => "{$minutes}:{$seconds}",
            ],
        ]);
    }

    /**
     * Get session details with conversation turns.
     */
    public function show(Request $request, Session $session)
    {
        if ($session->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $session->load('scenario');
        $turns = ConversationTurn::where('session_id', $session->id)
            ->orderBy('turn_number', 'asc')
            ->get();

        return response()->json([
            'data' => [
                'session' => $session,
                'turns' => $turns,
            ]
        ]);
    }

    /**
     * Get current phase info and timing.
     */
    public function phase(Request $request, Session $session, ConversationService $conversationService)
    {
        if ($session->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $conversationService->getPhaseInfo($session),
        ]);
    }

    /**
     * Advance to the next phase.
     */
    public function advancePhase(Request $request, Session $session, ConversationService $conversationService)
    {
        if ($session->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = $conversationService->advancePhase($session);

        // Generate audio for Ahmad's message in this phase
        if (!empty($result['message'])) {
            $tts = app(\App\Services\Contracts\TTSServiceInterface::class);
            $audioBase64 = $tts->synthesize($result['message']);
            $result['audio'] = $audioBase64;
            $result['audio_format'] = !empty($audioBase64) ? 'mp3' : null;
        }

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Get Simli session token (proxy to protect API key).
     */
    public function simliToken(Request $request)
    {
        $apiKey = config('services.simli.api_key', env('SIMLI_API_KEY'));
        $faceId = config('services.simli.face_id', env('SIMLI_FACE_ID'));

        if (!$apiKey || !$faceId) {
            return response()->json(['error' => 'Simli not configured'], 500);
        }

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 15, 'verify' => false]);
            $response = $client->post('https://api.simli.ai/compose/token', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-simli-api-key' => $apiKey,
                ],
                'json' => [
                    'faceId' => $faceId,
                    'maxSessionLength' => 2400, // 40 minutes
                    'maxIdleTime' => 300, // 5 minutes idle
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'session_token' => $data['session_token'] ?? null,
                'face_id' => $faceId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get Simli token',
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Get Simli ICE servers (proxy to protect API key).
     */
    public function simliIce(Request $request)
    {
        $apiKey = config('services.simli.api_key', env('SIMLI_API_KEY'));

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10, 'verify' => false]);
            $response = $client->get('https://api.simli.ai/compose/ice', [
                'headers' => [
                    'x-simli-api-key' => $apiKey,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get ICE servers',
                'message' => $e->getMessage(),
            ], 502);
        }
    }
}
