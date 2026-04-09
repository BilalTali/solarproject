<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\WaChatbotCategory;
use App\Models\WaChatbotSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    // ── Categories CRUD ───────────────────────────────────────────────

    public function index(): JsonResponse
    {
        $categories = WaChatbotCategory::ordered()->get();
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:wa_chatbot_categories,name',
            'description' => 'nullable|string|max:255',
            'icon_emoji'  => 'nullable|string|max:10',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        $category = WaChatbotCategory::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = WaChatbotCategory::findOrFail($id);

        $validated = $request->validate([
            'name'        => "required|string|max:255|unique:wa_chatbot_categories,name,{$id}",
            'description' => 'nullable|string|max:255',
            'icon_emoji'  => 'nullable|string|max:10',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = WaChatbotCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $category = WaChatbotCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        return response()->json($category);
    }

    // ── Registration Fields ───────────────────────────────────────────

    public function getRegistrationFields(): JsonResponse
    {
        $setting = Setting::where('key', 'wa_registration_fields')->first();
        $fields = $setting && $setting->value ? json_decode($setting->value, true) : [];
        return response()->json($fields);
    }

    public function setRegistrationFields(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fields'             => 'required|array',
            'fields.*.key'       => 'required|string',
            'fields.*.label'     => 'required|string',
            'fields.*.required'  => 'required|boolean',
            'fields.*.order'     => 'required|integer',
            'fields.*.type'      => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'wa_registration_fields'],
            [
                'value' => json_encode($validated['fields']),
                'group' => 'whatsapp',
            ]
        );

        return response()->json(['message' => 'Saved successfully']);
    }

    // ── Sessions & Contacts ───────────────────────────────────────────

    public function sessions(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $sessions = WaChatbotSession::orderBy('last_message_at', 'desc')->paginate($perPage);
        return response()->json($sessions);
    }

    public function contacts(): JsonResponse
    {
        $contacts = User::where('is_public_contact', true)
            ->where('status', 'active')
            ->select(['id', 'name', 'role', 'district', 'state', 'whatsapp_number'])
            ->get();
        return response()->json($contacts);
    }

    /** Returns ALL active users of all roles with their public-contact status for the management UI. */
    public function allContacts(): JsonResponse
    {
        $users = User::where('status', 'active')
            ->whereNotIn('role', ['beneficiary'])
            ->whereNotNull('whatsapp_number')
            ->select(['id', 'name', 'role', 'district', 'state', 'whatsapp_number', 'is_public_contact'])
            ->orderByDesc('is_public_contact')
            ->orderBy('name')
            ->get();
        return response()->json($users);
    }

    /** Toggle is_public_contact for any user (all roles). */
    public function toggleContact(int $id): JsonResponse
    {
        $user = User::where('status', 'active')->findOrFail($id);
        $user->is_public_contact = !$user->is_public_contact;
        $user->save();
        return response()->json(['id' => $user->id, 'is_public_contact' => $user->is_public_contact]);
    }

    /** Public (no-auth) endpoint used by the frontend WhatsApp float button. */
    public function publicContacts(): JsonResponse
    {
        $contacts = User::where('is_public_contact', true)
            ->where('status', 'active')
            ->whereNotNull('whatsapp_number')
            ->select(['id', 'name', 'whatsapp_number'])
            ->get();

        return response()->json([
            'contacts'      => $contacts,
            'chatbot_ready' => !empty(config('services.whatsapp.phone_number_id'))
                               && !empty(config('services.whatsapp.access_token')),
            'company_whatsapp' => Setting::where('key', 'company_whatsapp')->value('value') ?? '+91-8899055335',
        ]);
    }

    // ── WhatsApp Lead Handlers (Super Admin Only) ─────────────────────

    public function waHandlers(Request $request): JsonResponse
    {
        // Must be super admin
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->where('status', 'active')
            ->select(['id', 'name', 'email', 'is_wa_lead_handler', 'wa_lead_round_robin_counter'])
            ->get();

        return response()->json($admins);
    }

    public function toggleWaHandler(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admin = User::whereIn('role', ['admin', 'super_admin'])->findOrFail($id);
        $admin->is_wa_lead_handler = !$admin->is_wa_lead_handler;
        $admin->save();

        return response()->json($admin);
    }

    public function resetCounters(Request $request): JsonResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        User::where('is_wa_lead_handler', true)->update(['wa_lead_round_robin_counter' => 0]);
        return response()->json(['message' => 'Counters reset successfully']);
    }
}
