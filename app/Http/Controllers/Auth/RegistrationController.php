<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Mail\RegistrationOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Step 1: Send OTP to the provided email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'admin_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'school_level' => 'required|string',
        ]);

        $otp = rand(100000, 999999);
        
        // Store details and OTP in cache for 15 minutes
        Cache::put('reg_otp_' . $request->email, [
            'otp' => $otp,
            'school_details' => $request->only(['school_name', 'admin_name', 'school_level']),
        ], now()->addMinutes(15));

        // Send Email (Queued)
        try {
            Mail::to($request->email)->queue(new RegistrationOtpMail($otp));
        } catch (\Exception $e) {
            // Log the error but proceed for dev if using log driver
            \Log::error("Failed to send registration OTP: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Verification code sent to your email.',
            'email' => $request->email
        ]);
    }

    /**
     * Step 2: Verify OTP.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $data = Cache::get('reg_otp_' . $request->email);

        if (!$data || $data['otp'] != $request->otp) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        return response()->json(['message' => 'Email verified successfully.']);
    }

    /**
     * Step 3: Final Registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data = Cache::get('reg_otp_' . $request->email);

        if (!$data || $data['otp'] != $request->otp) {
            return response()->json(['message' => 'Verification session expired. Please restart.'], 422);
        }

        $schoolDetails = $data['school_details'];

        // Map school level
        $levelMap = [
            'Primary' => 1,
            'Secondary' => 2,
            'Integrated' => 4, // Mixed
        ];

        // 1. Create School
        $school = School::create([
            'name' => $schoolDetails['school_name'],
            'email' => $request->email,
            'status' => 'active',
            'school_type_id' => 5, // Private default
            'school_level_id' => $levelMap[$schoolDetails['school_level']] ?? 1,
        ]);

        // 2. Create User (School Admin)
        $user = User::create([
            'name' => $schoolDetails['admin_name'],
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => $school->id,
            'status' => 'active',
        ]);

        // Assign Role
        $user->assignRole('school_admin');

        // Clear cache
        Cache::forget('reg_otp_' . $request->email);

        // Login the user
        auth()->login($user);

        return response()->json([
            'message' => 'Registration successful!',
            'redirect' => route('dashboard')
        ]);
    }
}
