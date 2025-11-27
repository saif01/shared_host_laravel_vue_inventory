<?php

namespace App\Http\Controllers\Api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Log failed login
            $this->logLoginAttempt($request, null, false, 'invalid_credentials');
            
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Log successful login
        $this->logLoginAttempt($request, $user, true);

        $token = $user->createToken('admin-token')->plainTextToken;

        // Load roles with permissions relationship
        $user->load(['roles.permissions']);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Log login attempt
     */
    private function logLoginAttempt(Request $request, $user, $success, $failureReason = null)
    {
        LoginLog::create([
            'user_id' => $user ? $user->id : null,
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $success ? 'success' : 'failed',
            'failure_reason' => $success ? null : ($failureReason ?? 'unknown'),
            'logged_in_at' => $success ? now() : null,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        // Load roles with permissions relationship
        $user->load(['roles.permissions']);

        return response()->json($user);
    }
}
