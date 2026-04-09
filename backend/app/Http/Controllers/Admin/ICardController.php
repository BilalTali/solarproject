<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ICardService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class ICardController extends Controller
{
    public function __construct(private ICardService $iCardService) {}

    /**
     * Download the authenticated user's ID card as PDF.
     */
    public function download(Request $request): Response
    {
        // 1. Determine target user
        // We prioritize the ID in the route/query if the request is signed OR if the requester is an Admin.
        $requestedUserId = $request->route('userId') ?? $request->query('userId');
        $user = Auth::user();

        if ($requestedUserId) {
            if ($request->hasValidSignature() || ($user?->isAdmin())) {
                $user = User::findOrFail($requestedUserId);
            }
        }

        if (! $user) {
            \Illuminate\Support\Facades\Log::warning('ICard download failed: User not found or unauthorized', [
                'userId_param' => $requestedUserId,
                'auth_id' => Auth::id()
            ]);
            abort(401, 'Unauthorized or invalid download link');
        }

        return $this->iCardService->generateAndDownload($user);
    }

    /**
     * Admin: download any user's card.
     */
    public function adminDownload(Request $request, int $userId): Response
    {
        $targetUser = User::findOrFail($userId);
        $this->authorize('view', $targetUser);

        return $this->iCardService->generateAndDownload($targetUser);
    }

    /**
     * Get signed download URL for the current user.
     */
    public function getDownloadUrl(Request $request)
    {
        $user = Auth::user();
        $targetUserId = $request->query('userId', $user?->id);

        // Security check: only admins can request other users' iCards
        // Non-admins must be active and have 100% profile completion
        if ($user?->role !== 'admin') {
            if ($targetUserId != $user?->id) {
                abort(403, 'Unauthorized to download this ID card');
            }
            if ($user?->status !== 'active' || $user?->profile_completion < 75) {
                abort(403, 'ID card is available only for approved users with at least 75% complete profiles.');
            }
        }

        $url = URL::temporarySignedRoute(
            'icard.download',
            now()->addMinutes(10),
            ['userId' => $targetUserId]
        );

        return response()->json(['url' => $url]);
    }

    /**
     * Preview icard in browser.
     */
    public function preview(Request $request, int $userId)
    {
        $targetUser = User::findOrFail($userId);
        $this->authorize('view', $targetUser);
        $data = $this->iCardService->buildViewData($targetUser);

        return view('icard.index', $data);
    }
}
