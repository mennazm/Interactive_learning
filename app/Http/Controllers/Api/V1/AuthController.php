<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login student with symbolic code.
     * Rate limited to 5 attempts/minute via route middleware.
     */
    public function login(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $student = Student::where('code', $request->code)->where('is_active', true)->first();

        if (!$student) {
            Log::warning('Failed login attempt', [
                'code' => $request->code,
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Invalid symbolic code'], 401);
        }

        // Delete old tokens to prevent accumulation (keep max 1 active token)
        $student->tokens()->delete();

        // Create new token using Sanctum
        $token = $student->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'student' => $student,
            'token' => $token
        ]);
    }

    /**
     * Logout student.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
