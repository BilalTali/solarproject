<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function updateBulk(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'settings'         => 'required|array',
                'settings.*.key'   => 'required|string',
                'settings.*.value' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                \Log::warning('Settings update validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'input'  => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Key → default group map so new rows always get a meaningful group
            $defaultGroups = [
                'company_'      => 'company',
                'hero_'         => 'homepage',
                'footer_'       => 'company',
                'eligibility_'  => 'homepage',
                'calculator_'   => 'homepage',
                'welcome_'      => 'portal',
                'terms_'        => 'portal',
                'authorized_'   => 'icard',
                'letter_'       => 'letter',
                'signatory_'    => 'signatory',
            ];

            foreach ($request->settings as $setting) {
                $key   = $setting['key'];
                $value = $setting['value'];

                // Determine group: keep existing group if the row exists, else derive from key prefix
                $existing = Setting::where('key', $key)->first();
                if ($existing) {
                    $existing->update(['value' => $value]);
                } else {
                    $group = 'general';
                    foreach ($defaultGroups as $prefix => $g) {
                        if (str_starts_with($key, $prefix)) { $group = $g; break; }
                    }
                    Setting::create(['key' => $key, 'value' => $value, 'group' => $group]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Settings update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle file upload for settings like logo, signature, hero video.
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'key'  => 'required|string|in:company_favicon,company_logo,company_logo_2,company_signature,company_seal,hero_video',
            'file' => 'required|file|max:102400', // 100MB max
        ]);

        $key  = $request->input('key');
        $file = $request->file('file');

        // Delete old file if exists
        $existing = Setting::where('key', $key)->first();
        if ($existing && $existing->value) {
            Storage::disk('public')->delete($existing->value);
        }

        // Store new file
        $folder = match($key) {
            'company_favicon', 'company_logo', 'company_logo_2', 'company_signature', 'company_seal' => 'branding',
            'hero_video'        => 'homepage',
            default             => 'uploads',
        };

        $path = $file->store($folder, 'public');

        Setting::updateOrCreate(['key' => $key], [
            'value' => $path,
            'group' => match($key) {
                'company_favicon', 'company_logo', 'company_logo_2', 'company_signature', 'company_seal' => 'branding',
                'hero_video' => 'homepage',
                default => 'general',
            }
        ]);

        return response()->json([
            'success' => true,
            'data'    => ['url' => asset('storage/' . $path)],
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
            'name'           => ['sometimes', 'string', 'max:255'],
            'mobile'         => ['sometimes', 'string', 'max:20'],
            'whatsapp_number'=> ['nullable', 'string', 'max:20'],
            'father_name'    => ['nullable', 'string', 'max:255'],
            'dob'            => ['nullable', 'date'],
            'blood_group'    => ['nullable', 'string', 'max:10'],
            'state'          => ['nullable', 'string'],
            'district'       => ['nullable', 'string'],
            'area'           => ['nullable', 'string'],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $user->fresh(),
            'message' => 'Profile updated successfully.'
        ]);
    }
}
