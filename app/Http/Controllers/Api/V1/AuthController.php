<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login student with symbolic code.
     */
    public function login(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Assuming symbolic_code is unique per student
        $student = Student::where('code', $request->code)->where('is_active', true)->first();

        if (!$student) {
            return response()->json(['message' => 'Invalid symbolic code'], 401);
        }

        // Create token using Sanctum
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
