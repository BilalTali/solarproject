<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'password' => 'required|string',
            'role' => 'required|string'
        ]);

        $identifier = $request->identifier;
        $expectedRole = $request->role;
        
        if (preg_match('/^SM-\d+-\d+$/', $identifier) || preg_match('/^ENM-\d+-\d+$/', $identifier)) {
            $field = str_starts_with($identifier, 'SM') ? 'agent_id' : 'enumerator_id';
        } else {
            $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        }

        $user = User::query()->where(function ($q) use ($field, $identifier, $expectedRole) {
            $q->where($field, $identifier);
            if ($expectedRole === 'admin') {
                // Operators log in via the admin portal
                $q->whereIn('role', ['admin', 'operator']);
            } elseif ($expectedRole !== 'any') {
                $q->where('role', $expectedRole);
            }
        })->first();


        // Security: Generic message if user not found or password incorrect to prevent email harvesting
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }
        
        /** @var User $user */
        // Status Validation
        if (in_array($user->role, ['agent', 'super_agent', 'enumerator'])) {
            if ($user->status === 'pending') {
                return response()->json(['success' => false, 'message' => 'Account pending approval'], 403);
            }
            if ($user->status === 'inactive') {
                return response()->json(['success' => false, 'message' => 'Account suspended'], 403);
            }
        }

        if (!$user->email) {
            return response()->json(['success' => false, 'message' => 'No email associated with this account. Please contact support.'], 422);
        }

        $throttleKey = 'send-otp:'.$user->mobile;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests. Please try again after ' . ceil($seconds / 60) . ' minutes.'
            ], 429);
        }
        RateLimiter::hit($throttleKey, 15 * 60); // 15 minutes window

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        \Illuminate\Support\Facades\DB::table('login_otps')->updateOrInsert(
            ['email' => $user->email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new \App\Mail\LoginOtpMail($otp));

        // [SUPER ADMIN BYPASS] If super_admin, skip OTP step and return token immediately
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->last_login_at = now();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Login successful (Super Admin)',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                    'skip_otp' => true,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Credentials verified. We sent an OTP to your registered email address.',
            'debug_otp' => config('app.env') === 'local' ? $otp : null,
        ]);
    }

    public function loginOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'otp' => 'required|digits:6',
            'role' => 'required|string'
        ]);

        $identifier = $request->identifier;
        $expectedRole = $request->role;
        
        if (preg_match('/^SM-\d+-\d+$/', $identifier) || preg_match('/^ENM-\d+-\d+$/', $identifier)) {
            $field = str_starts_with($identifier, 'SM') ? 'agent_id' : 'enumerator_id';
        } else {
            $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        }

        $user = User::query()->with(['superAgent'])->where(function ($q) use ($field, $identifier, $expectedRole) {
            $q->where($field, $identifier);
            if ($expectedRole === 'admin') {
                $q->whereIn('role', ['admin', 'operator']);
            } elseif ($expectedRole !== 'any') {
                $q->where('role', $expectedRole);
            }
        })->first();



        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        /** @var User $user */
        $email = (string) $user->email;

        /** @var \stdClass|null $otpRecord */
        $otpRecord = \Illuminate\Support\Facades\DB::table('login_otps')
            ->where(fn($q) => $q->where('email', $email))
            ->where(fn($q) => $q->where('expires_at', '>', now()))
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.'
            ], 401);
        }

        if ($otpRecord->attempts >= 5) {
            \Illuminate\Support\Facades\DB::table('login_otps')->where(fn($q) => $q->where('email', $email))->delete();
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. This OTP has been invalidated. Please request a new one.'
            ], 401);
        }

        if (!Hash::check($request->otp, (string) $otpRecord->otp)) {
            \Illuminate\Support\Facades\DB::table('login_otps')
                ->where(fn($q) => $q->where('email', $email))
                ->increment('attempts');

            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ], 401);
        }

        // OTP is valid
        \Illuminate\Support\Facades\DB::table('login_otps')->where(fn($q) => $q->where('email', $email))->delete();

        /** @var User $user */
        $token = $user->createToken('auth_token')->plainTextToken;

        $user->last_login_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id'                   => $user->id,
                    'name'                 => $user->name,
                    'email'                => $user->email,
                    'mobile'               => $user->mobile,
                    'agent_id'             => $user->agent_id,
                    'super_agent_code'     => $user->super_agent_code,
                    'role'                 => $user->role,
                    'status'               => $user->status,
                    'approved_at'          => $user->approved_at,
                    'profile_completion'   => $user->profile_completion,
                    'super_agent_id'       => $user->super_agent_id,
                    'profile_photo'        => $user->profile_photo,
                    'district'             => $user->district,
                    'state'                => $user->state,
                ],
            ],
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        // Ensure qr_token exists for dashboard features
        if (! $user->qr_token) {
            $user->qr_token = bin2hex(random_bytes(16));
            $user->save();
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password set successfully.',
        ]);
    }

    /**
     * Step 1: Send a 6-digit OTP to the user's registered email for password reset.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'role'       => 'nullable|string',
        ]);

        $identifier   = trim($request->identifier);
        $expectedRole = $request->role ?? null;

        // Detect field from identifier format
        if (preg_match('/^SM-\d+-\d+$/', $identifier)) {
            $field = 'agent_id';
        } elseif (preg_match('/^ENM-\d+-\d+$/', $identifier)) {
            $field = 'enumerator_id';
        } else {
            $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        }

        $query = User::query()->where(fn($q) => $q->where($field, $identifier));
        if ($expectedRole && $expectedRole !== 'any') {
            $query->where(fn($q) => $q->where('role', $expectedRole));
        }
        $user = $query->first();

        // Always return success to prevent enumeration
        if (!$user || !$user->email) {
            return response()->json([
                'success' => true,
                'message' => 'If an account matched that identifier, a reset OTP has been sent to its registered email.'
            ]);
        }

        $throttleKey = 'forgot-pwd:'.$user->mobile;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            // Still return success to prevent enumeration
            return response()->json([
                'success' => true,
                'message' => 'If an account matched that identifier, a reset OTP has been sent to its registered email.'
            ]);
        }
        RateLimiter::hit($throttleKey, 15 * 60);

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        \Illuminate\Support\Facades\DB::table('login_otps')->updateOrInsert(
            ['email' => $user->email],
            [
                'otp'        => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
                'attempts'   => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new PasswordResetOtpMail($otp, $user->name));

        return response()->json([
            'success' => true,
            'message' => 'A password reset OTP has been sent to your registered email address.',
            'debug_otp' => config('app.env') === 'local' ? $otp : null,
        ]);
    }

    /**
     * Step 2: Verify OTP.
     * Step 3: Set new password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier'            => 'required|string',
            'otp'                   => 'required|digits:6',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => 'nullable|string',
        ]);

        $identifier   = trim($request->identifier);
        $expectedRole = $request->role ?? null;

        if (preg_match('/^SM-\d+-\d+$/', $identifier)) {
            $field = 'agent_id';
        } elseif (preg_match('/^ENM-\d+-\d+$/', $identifier)) {
            $field = 'enumerator_id';
        } else {
            $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        }

        $query = User::query()->where(fn($q) => $q->where($field, $identifier));
        if ($expectedRole && $expectedRole !== 'any') {
            $query->where(fn($q) => $q->where('role', $expectedRole));
        }
        $user = $query->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        }

        /** @var User $user */
        if (!$user->email) {
            return response()->json(['success' => false, 'message' => 'No email associated with account.'], 422);
        }

        $email = (string) $user->email;

        /** @var \stdClass|null $otpRecord */
        $otpRecord = \Illuminate\Support\Facades\DB::table('login_otps')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 401);
        }

        // Defensive check for attempts property
        $attempts = isset($otpRecord->attempts) ? (int)$otpRecord->attempts : 0;
        if ($attempts >= 5) {
            \Illuminate\Support\Facades\DB::table('login_otps')->where('email', $email)->delete();
            return response()->json(['success' => false, 'message' => 'Too many failed attempts. This OTP has been invalidated.'], 401);
        }

        if (!Hash::check($request->otp, (string) ($otpRecord->otp ?? ''))) {
            \Illuminate\Support\Facades\DB::table('login_otps')->where('email', $email)->increment('attempts');
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 401);
        }

        // OTP valid — update password and clean up
        $user->password = Hash::make($request->password);
        $user->save();

        \Illuminate\Support\Facades\DB::table('login_otps')->where('email', $email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset successfully. You may now log in.',
        ]);
    }

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        /** @var User $user */
        $user = $request->user();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('photo')->store('profiles', 'public');

            $user->profile_photo = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile photo uploaded successfully',
                'data' => $user->fresh(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No photo uploaded',
        ], 400);
    }
}
