<?php

namespace App\Http\Controllers\Solar;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PublicController extends Controller
{
    /**
     * Return public settings used by the homepage.
     */
    public function settings()
    {
        $keys = [
            'company_name', 'company_email', 'company_mobile', 'company_whatsapp',
            'company_address', 'company_slogan', 'company_logo', 'company_favicon',
            'company_logo_2', 'company_signature', 'company_seal', 'company_website',
            'company_registration_no', 'company_affiliated_with',
            'authorized_signatory', 'authorized_signatory_title',
            'hero_headline', 'hero_subheadline', 'hero_video',
            'hero_stats_json', 'how_it_works_json', 'why_choose_us_json',
            'calculator_headline', 'calculator_subheadline', 'calculator_values_json',
            'eligibility_headline', 'eligibility_subheadline', 'eligibility_questions_json',
            'eligibility_success_title', 'eligibility_success_desc', 'eligibility_error_title', 'eligibility_error_desc',
            'footer_about_text', 'footer_copyright', 'footer_disclaimer',
            'footer_section_quick_links', 'footer_section_legal',
            'footer_link_about', 'footer_link_scheme', 'footer_link_contact',
            'footer_link_faq', 'footer_link_privacy', 'footer_link_terms', 'footer_link_refund'
        ];

        // Determine the primary user ID for public settings — typically the first Super Admin
        $superAdmin = User::roleSuperAdmin()->first();
        $userId = $superAdmin ? $superAdmin->id : null;

        $settings = Setting::query()
            ->whereIn('key', $keys)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('key');

        $result = [];
        foreach ($keys as $key) {
            /** @var Setting|null $item */
            $item = $settings[$key] ?? null;
            $value = $item?->value;

            // File paths — return as full URLs
            $assetKeys = ['company_logo', 'company_logo_2', 'company_signature', 'company_seal', 'company_favicon', 'hero_video'];
            if (in_array($key, $assetKeys) && $value) {
                $value = asset('storage/'.$value);
            }

            $result[$key] = $value;
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Return published achievements for the homepage.
     */
    public function achievements()
    {
        $achievements = Achievement::query()->where(fn ($q) => $q->where('is_published', true))
            ->orderBy('sort_order')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'description' => $a->description,
                'image_url' => $a->image_path ? asset('storage/'.$a->image_path) : null,
                'date' => $a->date?->format('d M Y'),
            ]);

        return response()->json(['success' => true, 'data' => $achievements]);
    }

    /**
     * Return published feedbacks for the homepage.
     */
    public function feedbacks()
    {
        $feedbacks = \App\Models\Feedback::query()->where(fn ($q) => $q->where('is_published', true))
            ->latest()
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'message' => $f->message,
                'rating' => $f->rating,
                'admin_reply' => $f->admin_reply,
                'date' => $f->created_at->format('d M Y'),
            ]);

        return response()->json(['success' => true, 'data' => $feedbacks]);
    }

    /**
     * Public verification API for agent I-Cards.
     */
    public function verifyAgent(\Illuminate\Http\Request $request, string $token)
    {
        /** @var \App\Models\User|null $user */
        $user = User::query()->where(fn ($q) => $q->where('qr_token', $token))
            ->with(['superAgent'])
            ->first();

        // Log the scan
        try {
            \App\Models\QrScanLog::create([
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'referer' => substr($request->header('referer') ?? '', 0, 500),
            ]);
        } catch (\Exception $e) {
            Log::warning('Public QR scan log failed: '.$e->getMessage());
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired QR token.',
            ], 404);
        }

        // Update scan statistics if active
        if ($user->status === 'active') {
            $user->increment('scan_count');
            $user->update(['last_verified_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'agent_id' => $user->agent_id ?? $user->super_agent_code ?? 'ADM-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'role' => $user->role,
                'designation' => $this->getDesignation($user),
                'photo_url' => $user->profile_photo ? asset('storage/'.$user->profile_photo) : null,
                'status' => $user->status,
                'is_active' => $user->status === 'active',
                'district' => $user->district,
                'state' => $user->state,
                'joining_date' => ($user->approved_at ?? $user->joining_date)?->format('d M Y'),
                'clearance' => Setting::getValue('icard_clearance', 'Level-V (Elite)'),
                'mobile' => $user->mobile ? 'XXXXXX'.substr($user->mobile, -4) : null,
                'blood_group' => $user->blood_group,
                'super_agent' => $user->superAgent ? [
                    'name' => $user->superAgent->name,
                    'code' => $user->superAgent->super_agent_code,
                ] : null,
            ],
        ]);
    }

    private function getDesignation(User $user): string
    {
        return match ($user->role) {
            'admin' => 'ADMINISTRATOR',
            'super_agent' => 'BUSINESS DEVELOPMENT MANAGER',
            'agent' => 'BUSINESS DEVELOPMENT EXECUTIVE',
            default => 'MEMBER',
        };
    }

    public function help()
    {
        $faqs = \App\Models\FAQ::published()->orderBy('sort_order')->get();
        
        $contacts = User::where('is_public_contact', true)
            ->where('status', 'active')
            ->select('id', 'name', 'district', 'state', 'whatsapp_number', 'role')
            ->orderBy('public_contact_order')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'district' => $user->district,
                    'state' => $user->state,
                    'whatsapp' => $user->whatsapp_number,
                    'role' => strtoupper(str_replace('_', ' ', $user->role)),
                ];
            });

        return response()->json([
            'faqs' => $faqs,
            'contacts' => $contacts
        ]);
    }
}
