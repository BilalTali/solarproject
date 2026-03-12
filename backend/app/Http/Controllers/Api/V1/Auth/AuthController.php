<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function adminLogin(Request $request)
    {
        return $this->performLogin($request, 'admin');
    }

    public function agentLogin(Request $request)
    {
        return $this->performLogin($request, 'agent');
    }

    public function superAgentLogin(Request $request)
    {
        return $this->performLogin($request, 'super_agent');
    }

    protected function performLogin(Request $request, string $expectedRole)
    {
        $request->validate([
            'identifier' => 'required',
            'password' => 'required',
        ]);

        $identifier = $request->identifier;
        if ($expectedRole === 'agent' && preg_match('/^SM-\d+-\d+$/', $identifier)) {
             $field = 'agent_id';
        } else {
             $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        }

        $user = User::where($field, $identifier)->where('role', $expectedRole)->first();

        \Log::info('Login Attempt', [
            'identifier' => $identifier,
            'field' => $field,
            'expectedRole' => $expectedRole,
            'user_found' => (bool)$user,
            'role_match' => $user ? ($user->role === $expectedRole) : 'n/a',
            'password_match' => $user ? Hash::check($request->password, $user->password) : 'n/a',
        ]);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Status Validation (for Agent and Super Agent)
        if (in_array($user->role, ['agent', 'super_agent'])) {
            if ($user->status === 'pending') {
                return response()->json(['success' => false, 'message' => 'Account pending approval'], 403);
            }
            if ($user->status === 'inactive') {
                return response()->json(['success' => false, 'message' => 'Account suspended'], 403);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        
        $user->last_login_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'agent_id' => $user->agent_id,
                    'role' => $user->role,
                    'status' => $user->status,
                    'approved_at' => $user->approved_at,
                    'profile_completion' => $user->profile_completion,
                ],
                'requires_password_set' => is_null($user->password),
            ]
        ]);
    }

    public function login(Request $request)
    {
        // Legacy unified login - optional, but let's keep it or redirect it
        $request->validate(['identifier' => 'required', 'password' => 'required']);
        $user = User::where('email', $request->identifier)->orWhere('mobile', $request->identifier)->orWhere('agent_id', $request->identifier)->first();
        if ($user) {
            return $this->performLogin($request, $user->role);
        }
        return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        // Ensure qr_token exists for dashboard features
        if (!$user->qr_token) {
            $user->qr_token = bin2hex(random_bytes(16));
            $user->save();
        }
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password set successfully.'
        ]);
    }

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = $request->user();
        
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && \Storage::disk('public')->exists($user->profile_photo)) {
                \Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('photo')->store('profiles', 'public');
            
            $user->profile_photo = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile photo uploaded successfully',
                'data' => $user->fresh()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No photo uploaded'
        ], 400);
    }
}
