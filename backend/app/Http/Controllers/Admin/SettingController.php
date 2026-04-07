<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\UpdateSettingRequest;

class SettingController extends Controller
{
    /**
     * Master Branding keys reserved for Super Admin only.
     */
    public const MASTER_BRANDING_KEYS = [
        'company_registration_no',
        'company_affiliated_with',
        'company_logo_2'
    ];

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $targetUserId = $user->isSuperAdmin() ? null : $user->getRootAdminId();
        $mergedSettings = Setting::getMergedSettings($targetUserId);

        // Group by 'group' as the frontend expects
        $groupedSettings = $mergedSettings->filter(function($setting) use ($user) {
            // Hide Master keys from regular Admins
            if (!$user->isSuperAdmin() && in_array($setting->key, self::MASTER_BRANDING_KEYS)) {
                return false;
            }
            return true;
        })->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => $groupedSettings,
        ]);
    }

    public function updateBulk(UpdateSettingRequest $request)
    {
        try {
            $validated = $request->validated();

            // Key → default group map so new rows always get a meaningful group
            $defaultGroups = [
                'company_' => 'company',
                'hero_' => 'homepage',
                'footer_' => 'company',
                'footer_link_' => 'branding',
                'eligibility_' => 'homepage',
                'calculator_' => 'homepage',
                'welcome_' => 'portal',
                'terms_' => 'portal',
                'nav_' => 'portal',
                'label_' => 'homepage',
                'authorized_' => 'icard',
                'letter_' => 'letter',
                'signatory_' => 'signatory',
            ];

            $user = auth()->user();
            $targetUserId = $user->isSuperAdmin() ? null : $user->id;

            foreach ($validated['settings'] as $setting) {
                $key = $setting['key'];
                $value = $setting['value'];

                // Master Branding Protection
                if (!$user->isSuperAdmin() && in_array($key, self::MASTER_BRANDING_KEYS)) {
                    continue; // Refuse update for Master keys by non-Super Admin
                }

                // Determine group: keep existing group if the row exists, else derive from key prefix
                $existing = Setting::query()
                    ->where('key', $key)
                    ->where('user_id', $targetUserId)
                    ->first();

                if ($existing) {
                    $existing->update(['value' => $value]);
                } else {
                    $group = 'general';
                    foreach ($defaultGroups as $prefix => $g) {
                        if (str_starts_with($key, $prefix)) {
                            $group = $g;
                            break;
                        }
                    }
                    Setting::create([
                        'key' => $key,
                        'value' => $value,
                        'group' => $group,
                        'user_id' => $targetUserId
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Settings update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: '.$e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Handle file upload for settings like logo, signature, hero video.
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'key' => 'required|string|in:company_favicon,company_logo,company_logo_2,company_signature,company_seal,hero_video',
            'file' => 'required|file|max:102400', // 100MB max
        ]);

        $key = $request->input('key');
        $file = $request->file('file');

        $user = auth()->user();
        $targetUserId = $user->isSuperAdmin() ? null : $user->id;

        // Master Branding Protection for file uploads
        if (!$user->isSuperAdmin() && in_array($key, self::MASTER_BRANDING_KEYS)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Only Super Admin can modify Master Branding assets.'], 403);
        }

        // Delete old file if exists
        /** @var Setting|null $existing */
        $existing = Setting::query()
            ->where('key', $key)
            ->where('user_id', $targetUserId)
            ->first();

        if ($existing && $existing->value) {
            Storage::disk('public')->delete((string)$existing->value);
        }

        // Store new file
        $folder = match ($key) {
            'company_favicon', 'company_logo', 'company_logo_2', 'company_signature', 'company_seal' => 'branding',
            'hero_video' => 'homepage',
            default => 'uploads',
        };

        $path = $file->store($folder, 'public');

        Setting::updateOrCreate(
            ['key' => $key, 'user_id' => $targetUserId],
            [
                'value' => $path,
                'group' => match ($key) {
                    'company_favicon', 'company_logo', 'company_logo_2', 'company_signature', 'company_seal' => 'branding',
                    'hero_video' => 'homepage',
                    default => 'general',
                },
            ]
        );

        return response()->json([
            'success' => true,
            'data' => ['url' => asset('storage/'.$path)],
            'message' => 'File uploaded successfully',
        ]);
    }

    /**
     * Update the admin's personal profile details.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'mobile' => ['sometimes', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'state' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'area' => ['nullable', 'string'],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'Profile updated successfully.',
        ]);
    }
}
