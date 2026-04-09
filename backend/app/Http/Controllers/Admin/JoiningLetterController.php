<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JoiningLetterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class JoiningLetterController extends Controller
{
    protected $joiningLetterService;

    public function __construct(JoiningLetterService $joiningLetterService)
    {
        $this->joiningLetterService = $joiningLetterService;
    }

    /**
     * Get a temporary signed URL for downloading the joining letter
     */
    public function getDownloadUrl(Request $request)
    {
        $user = $request->user();

        // Check if approved and profile is 100% complete
        if ($user->role !== 'admin' && ($user->status !== 'active' || $user->profile_completion < 75)) {
            return response()->json([
                'message' => 'Joining letter is available only for approved users with at least 75% complete profiles.',
            ], 403);
        }

        $url = URL::temporarySignedRoute(
            'joining-letter.download',
            now()->addMinutes(15),
            ['userId' => $user->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Download URL generated.',
            'data' => [
                'download_url' => $url,
            ],
        ]);
    }

    /**
     * Direct download of joining letter (Signed Route)
     */
    public function download(Request $request, $userId)
    {
        // 1. Verify Signature
        if (! $request->hasValidSignature()) {
            abort(403, 'Link expired or invalid signature.');
        }

        $user = User::query()->findOrFail($userId);

        // 2. Security Check (Admin or the user themselves)
        $authUser = $request->user() ?: auth('sanctum')->user();
        if ($authUser && $authUser->role !== 'admin' && $authUser->id !== $user->id) {
            abort(403, 'Unauthorized access to this document.');
        }

        // 3. Status Check
        if (! $user->approved_at && ($authUser && $authUser->role !== 'admin')) {
            // We fallback to generated logic if approved_at is missing for legacy but status is active
            if ($user->status !== 'active') {
                abort(403, 'Document not yet generated.');
            }
        }

        return $this->joiningLetterService->generatePdf($user);
    }

    /**
     * Admin Preview Route (Immediate PDF stream)
     */
    public function preview(Request $request, User $user)
    {
        $authUser = $request->user();
        if (! $authUser || $authUser->role !== 'admin') {
            abort(403);
        }

        // For preview, we temporarily mock approved_at and letter_number if not set
        if (! $user->approved_at) {
            $user->approved_at = now();
            if (! $user->letter_number) {
                $user->letter_number = $this->joiningLetterService->generateLetterNumber($user);
            }
        }

        return $this->joiningLetterService->generatePdf($user, false);
    }
}
