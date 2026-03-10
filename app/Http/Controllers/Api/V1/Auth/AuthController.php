<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Handle user login.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log failed attempt
            if ($user) {
                $this->logLogin($user, $request, 'failed', 'Invalid credentials');
            }

            return $this->error('Invalid credentials', 401);
        }

        if ($user->status !== 'active') {
            $this->logLogin($user, $request, 'blocked', 'Account not active');
            return $this->error('Your account is not active. Please contact support.', 403);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Log successful login
        $this->logLogin($user, $request, 'success');

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Update login log
        LoginLog::where('user_id', $request->user()->id)
            ->whereNull('logged_out_at')
            ->latest()
            ->first()
            ?->update(['logged_out_at' => now()]);

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['teacher', 'student', 'guardian']);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'status' => $user->status,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'email_verified_at' => $user->email_verified_at,
            'two_factor_enabled' => !is_null($user->two_factor_confirmed_at),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'teacher' => $user->teacher,
            'student' => $user->student,
            'guardian' => $user->guardian,
            'last_login_at' => $user->last_login_at,
        ]);
    }

    /**
     * Refresh the access token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Token refreshed');
    }

    /**
     * Log login attempt.
     */
    private function logLogin(User $user, Request $request, string $status, ?string $reason = null): void
    {
        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->detectDeviceType($request->userAgent()),
            'browser' => $this->detectBrowser($request->userAgent()),
            'platform' => $this->detectPlatform($request->userAgent()),
            'status' => $status,
            'failure_reason' => $reason,
            'logged_in_at' => now(),
        ]);
    }

    private function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';

        if (preg_match('/mobile/i', $userAgent)) return 'mobile';
        if (preg_match('/tablet/i', $userAgent)) return 'tablet';

        return 'desktop';
    }

    private function detectBrowser(?string $userAgent): ?string
    {
        if (!$userAgent) return null;

        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Edge/i', $userAgent)) return 'Edge';

        return 'Other';
    }

    private function detectPlatform(?string $userAgent): ?string
    {
        if (!$userAgent) return null;

        if (preg_match('/Windows/i', $userAgent)) return 'Windows';
        if (preg_match('/Mac/i', $userAgent)) return 'macOS';
        if (preg_match('/Linux/i', $userAgent)) return 'Linux';
        if (preg_match('/Android/i', $userAgent)) return 'Android';
        if (preg_match('/iOS/i', $userAgent)) return 'iOS';

        return 'Other';
    }
}
