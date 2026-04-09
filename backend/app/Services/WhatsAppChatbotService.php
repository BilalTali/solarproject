<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\Setting;
use App\Models\User;
use App\Models\WaChatbotCategory;
use App\Models\WaChatbotSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppChatbotService
{
    private WhatsAppCloudApiService $waApi;
    private LeadService $leadService;

    public function __construct(WhatsAppCloudApiService $waApi, LeadService $leadService)
    {
        $this->waApi = $waApi;
        $this->leadService = $leadService;
    }

    public function handle(string $from, array $message): void
    {
        Log::error('Chatbot Handle - From: ' . $from, ['message' => $message]);
        
        // $from is usually E.164, without '+'
        $phone = ltrim($from, '+');

        $session = WaChatbotSession::firstOrCreate(
            ['wa_phone' => $phone],
            ['state' => 'menu', 'context' => []]
        );


        // Timeout check: greater than 30 mins -> reset to menu
        if ($session->last_message_at && $session->last_message_at->diffInMinutes(now()) > 30) {
            $session->state = 'menu';
            $session->context = [];
        }

        $session->last_message_at = now();
        $session->save();

        $input = $this->parseInput($message);

        // Global overrides: Keywords from website buttons
        $cleanInput = is_string($input) ? strtoupper(trim($input)) : '';
        
        if (in_array($cleanInput, ['0', 'MENU', 'HI', 'HELLO'])) {
            $session->state = 'menu';
            $session->context = [];
            $session->save();
        } elseif ($cleanInput === 'APPLY') {
            $session->state = 'register';
            $session->context = ['reg_step' => 1, 'reg_data' => []];
            $session->save();
            $this->startRegistration($from);
            return;
        } elseif ($cleanInput === 'FAQ') {
            $session->state = 'menu';
            $session->context = [];
            $session->save();
            $this->sendFaqCategories($from);
            return;
        } elseif ($cleanInput === 'SUPPORT' || $cleanInput === '00') {
            $this->sendContactList($from);
            return;
        }

        switch ($session->state) {
            case 'menu':
                $this->handleMenuState($session, $input);
                break;
            case 'category':
                $this->handleCategoryState($session, $input);
                break;
            case 'register':
                $this->handleRegisterState($session, $input);
                break;
            case 'done':
                $this->waApi->sendText($from, "Your registration is already complete. Reply 0 to access the main menu.");
                break;
            default:
                $this->handleMenuState($session, $input);
                break;
        }
    }

    private function parseInput(array $message): string|array
    {
        $type = $message['type'] ?? 'text';
        if ($type === 'text') {
            return $message['text']['body'] ?? '';
        } elseif ($type === 'interactive') {
            $interactive = $message['interactive'];
            if (($interactive['type'] ?? '') === 'list_reply') {
                return $interactive['list_reply']['id'] ?? '';
            }
            if (($interactive['type'] ?? '') === 'button_reply') {
                return $interactive['button_reply']['id'] ?? '';
            }
        } elseif ($type === 'image') {
            return [
                'type' => 'image',
                'media_id' => $message['image']['id'] ?? '',
                'mime_type' => $message['image']['mime_type'] ?? ''
            ];
        }

        return ''; // unhandled type
    }

    private function handleMenuState(WaChatbotSession $session, mixed $input): void
    {
        $choice = is_string($input) ? trim($input) : '';

        if ($choice === 'apply' || $choice === '7') {
            // Apply Now
            $session->state = 'register';
            $session->context = ['reg_step' => 1, 'reg_data' => []];
            $session->save();
            $this->startRegistration($session->wa_phone);
            return;
        } 
        
        if ($choice === 'agent' || $choice === '8') {
            // Talk to Agent
            $this->sendContactList($session->wa_phone);
            return;
        }

        // Check if a category was selected
        if ($choice !== '') {
            $category = null;
            if (is_numeric($choice)) {
                $category = WaChatbotCategory::where('sort_order', (int)$choice)->active()->first();
            } else {
                // Interactive list sends the DB ID normally, but here we used 'cat_{id}'
                if (str_starts_with($choice, 'cat_')) {
                    $id = (int)str_replace('cat_', '', $choice);
                    $category = WaChatbotCategory::active()->find($id);
                }
            }

            if ($category) {
                $session->state = 'category';
                $session->context = ['selected_category' => $category->name];
                $session->save();
                $this->sendQuestionList($session->wa_phone, $category);
                return;
            }
        }

        $this->sendCategoryMenu($session->wa_phone);
    }

    private function startRegistration(string $to): void
    {
        $regFields = $this->getRegistrationFields();
        $firstField = collect($regFields)->firstWhere('order', 1);
        
        $this->waApi->sendText($to, "🚀 *Let's get you registered for PM Surya Ghar!*\n\nWe'll need a few details to process your application. You can type *0* anytime to return to the main menu.\n\n👉 Step 1: *{$firstField['label']}*");
    }

    private function handleCategoryState(WaChatbotSession $session, mixed $input): void
    {
        $catName = $session->context['selected_category'] ?? null;
        if (!$catName) {
            $session->state = 'menu';
            $session->save();
            $this->sendCategoryMenu($session->wa_phone);
            return;
        }

        $category = WaChatbotCategory::where('name', $catName)->first();
        if (!$category) {
            $session->state = 'menu';
            $session->save();
            $this->sendCategoryMenu($session->wa_phone);
            return;
        }

        if (is_string($input) && is_numeric($input)) {
            $choice = (int)$input;
            $faqs = $category->faqs();
            
            if ($choice > 0 && $choice <= $faqs->count()) {
                $faq = $faqs[$choice - 1]; // 0-indexed internally
                $this->waApi->sendText($session->wa_phone, "Q: {$faq->question}\n\nA: {$faq->answer}\n\nReply 0 = Main Menu | 00 = Talk to Agent");
                return;
            }
        }

        // Invalid response, resend list
        $this->waApi->sendText($session->wa_phone, "Invalid choice. Please reply with a valid question number.\nReply 0 = Main Menu | 00 = Talk to Agent");
    }

    private function handleRegisterState(WaChatbotSession $session, mixed $input): void
    {
        $context = $session->context ?? [];
        $step = $context['reg_step'] ?? 1;
        $data = $context['reg_data'] ?? [];
        $regFields = $this->getRegistrationFields();
        $totalFields = count($regFields);
        $totalSteps = $totalFields + 3; // + image1, image2, referral

        if ($step <= $totalFields) {
            $currentField = collect($regFields)->firstWhere('order', $step);
            if (!$currentField) {
                // Should not happen, fallback
                $step++;
            } else {
                $value = is_string($input) ? trim($input) : '';
                if ($value === '' && $currentField['required']) {
                    $this->waApi->sendText($session->wa_phone, "This field is required.\n{$currentField['label']}");
                    return;
                }
                
                if (strtolower($value) === 'skip' && !$currentField['required']) {
                    $value = null;
                }

                $data[$currentField['key']] = $value;
                $step++;
            }
        } elseif ($step === $totalFields + 1) {
            // Electricity Bill
            if (is_array($input) && $input['type'] === 'image') {
                $data['elec_bill_media_id'] = $input['media_id'];
                $step++;
            } else {
                $this->waApi->sendText($session->wa_phone, "Step {$step}: Please send a clear *photo* of your Electricity Bill 📄");
                return;
            }
        } elseif ($step === $totalFields + 2) {
            // Aadhaar Card
            if (is_array($input) && $input['type'] === 'image') {
                $data['aadhaar_media_id'] = $input['media_id'];
                $step++;
            } else {
                $this->waApi->sendText($session->wa_phone, "Step {$step}: Please send a clear *photo* of your Aadhaar Card 🪪");
                return;
            }
        } elseif ($step === $totalFields + 3) {
            // Referral code step
            $val = is_string($input) ? trim($input) : 'NO';
            if (strtoupper($val) === 'NO' || strtoupper($val) === 'SKIP') {
                $data['referral_code'] = null;
            } else {
                $data['referral_code'] = $val;
            }
            $step++;
        }

        // Update Context
        $context['reg_step'] = $step;
        $context['reg_data'] = $data;
        $session->context = $context;
        $session->save();

        // Ask next question or finish
        if ($step <= $totalFields) {
            $nextField = collect($regFields)->firstWhere('order', $step);
            $this->waApi->sendText($session->wa_phone, "Step {$step}: {$nextField['label']}");
        } elseif ($step === $totalFields + 1) {
            $this->waApi->sendText($session->wa_phone, "Step {$step}: Please send a photo of your Electricity Bill 📄");
        } elseif ($step === $totalFields + 2) {
            $this->waApi->sendText($session->wa_phone, "Step {$step}: Please send a photo of your Aadhaar Card 🪪");
        } elseif ($step === $totalFields + 3) {
            $this->waApi->sendText($session->wa_phone, "Step {$step}: Do you have a referral code from a solar agent?\nEnter the code (e.g. SM-2026-1042) or type NO to skip.");
        } else {
            // Finished! Create Lead
            try {
                $lead = $this->createLead($session);
                $session->state = 'done';
                $session->save();
                
                $this->waApi->sendText($session->wa_phone, "✅ *Registration successful!*\n\nYour Application ID is: *{$lead->ulid}*\n\nOur team will review your application and contact you shortly. Thank you for choosing green energy!\n\nReply *0* for the Main Menu.");
            } catch (\Exception $e) {
                Log::error('WA Chatbot Lead Creation Error: ' . $e->getMessage());
                $this->waApi->sendText($session->wa_phone, "❌ Sorry, there was an error processing your registration. Please try again later.\nReply 0 for Main Menu.");
            }
        }
    }

    private function createLead(WaChatbotSession $session): Lead
    {
        $reg = $session->context['reg_data'];
        $ulid = Str::ulid()->toBase32();

        $referralCode = $reg['referral_code'] ?? null;
        $handlerId = null;

        if (!$referralCode) {
            $handler = $this->pickWaHandler();
            $handlerId = $handler?->id;
        }

        // Create data for LeadService
        $data = [
            'ulid'                 => $ulid,
            'beneficiary_name'     => $reg['name'] ?? 'Unknown',
            'beneficiary_mobile'   => $reg['mobile'] ?? '',
            'beneficiary_whatsapp' => $session->wa_phone,
            'beneficiary_state'    => $reg['state'] ?? '',
            'beneficiary_district' => $reg['district'] ?? '',
            'beneficiary_address'  => $reg['area'] ?? null,
            'referral_agent_id'    => $referralCode,
            'query_message'        => "Source: WhatsApp Chatbot | WA: {$session->wa_phone}",
        ];

        // Let LeadService wrap existing logic
        $lead = $this->leadService->createFromPublicForm($data);

        // Force fill the overrides
        if (!$referralCode) {
            $lead->forceFill([
                'source'              => 'whatsapp_chatbot',
                'wa_handler_admin_id' => $handlerId,
            ])->save();
        } else {
            $lead->forceFill(['source' => 'whatsapp_chatbot'])->save();
        }

        // Handle File downloads
        if (!empty($reg['elec_bill_media_id'])) {
            $path = $this->waApi->downloadMedia($reg['elec_bill_media_id'], "leads/{$ulid}/electricity_bill_wa.jpg");
            if ($path) {
                LeadDocument::create([
                    'lead_id'           => $lead->id,
                    'document_type'     => 'electricity_bill',
                    'file_path'         => $path,
                    'original_filename' => 'electricity_bill_wa.jpg',
                    'uploaded_by'       => null
                ]);
            }
        }

        if (!empty($reg['aadhaar_media_id'])) {
            $path = $this->waApi->downloadMedia($reg['aadhaar_media_id'], "leads/{$ulid}/aadhaar_wa.jpg");
            if ($path) {
                LeadDocument::create([
                    'lead_id'           => $lead->id,
                    'document_type'     => 'aadhaar',
                    'file_path'         => $path,
                    'original_filename' => 'aadhaar_wa.jpg',
                    'uploaded_by'       => null
                ]);
            }
        }

        return $lead;
    }

    private function pickWaHandler(): ?User
    {
        $handler = User::where('is_wa_lead_handler', true)
            ->whereIn('role', ['admin', 'super_admin'])
            ->orderBy('wa_lead_round_robin_counter', 'asc')
            ->first();

        if ($handler) {
            $handler->increment('wa_lead_round_robin_counter');
            return $handler;
        }

        return null;
    }

    private function sendCategoryMenu(string $to): void
    {
        $categories = WaChatbotCategory::active()->ordered()->get();
        
        $sections = [
            [
                'title' => 'FAQ Categories',
                'rows' => $categories->map(fn($cat) => [
                    'id'    => 'cat_' . $cat->id,
                    'title' => $cat->name,
                    'description' => $cat->description ?: 'View questions'
                ])->toArray()
            ],
            [
                'title' => 'Quick Actions',
                'rows' => [
                    [
                        'id'    => 'apply',
                        'title' => '📋 Apply for Solar',
                        'description' => 'Register for rooftop solar'
                    ],
                    [
                        'id'    => 'agent',
                        'title' => '🧑‍💼 Talk to Agent',
                        'description' => 'Connect with customer care'
                    ]
                ]
            ]
        ];
 
        $this->waApi->sendList(
            $to,
            'PM Surya Ghar Support',
            "🌞 *Welcome to AndleebSurya!* \n\nJoin the revolution of free solar energy. Get up to 300 units of free electricity every month!\n\nHow can we assist you today?",
            $sections
        );
    }
 
    private function sendFaqCategories(string $to): void
    {
        $categories = WaChatbotCategory::active()->ordered()->get();
        
        $rows = $categories->map(fn($cat) => [
            'id'    => 'cat_' . $cat->id,
            'title' => $cat->name,
            'description' => 'Browse questions'
        ])->toArray();
 
        $this->waApi->sendList(
            $to,
            'Frequently Asked Questions',
            "📚 *Browse FAQ Categories*\n\nPlease select a category to see relevant questions.",
            [['title' => 'Categories', 'rows' => $rows]]
        );
    }

    private function sendQuestionList(string $to, WaChatbotCategory $cat): void
    {
        $faqs = $cat->faqs();
        
        if ($faqs->isEmpty()) {
            $this->waApi->sendText($to, "No questions found in [{$cat->name}].\nReply 0 = Main Menu");
            return;
        }

        $body = "Here are questions in [{$cat->name}]:\n\n";
        foreach ($faqs as $i => $faq) {
            $num = $i + 1;
            $body .= "{$num}. {$faq->question}\n";
        }

        $body .= "\n0. Back to Main Menu\n00. Talk to Agent";
        
        $this->waApi->sendText($to, $body);
    }

    private function sendContactList(string $to): void
    {
        $contacts = User::where('status', 'active')
            ->where('is_public_contact', true)
            ->get();
            
        if ($contacts->isEmpty()) {
            $this->waApi->sendText($to, "No agents available right now.\nReply 0 = Main Menu");
            return;
        }
            
        $body = "📞 Customer Care Contacts:\n\n";
        /** @var \App\Models\User $c */
        foreach ($contacts as $c) {
            $roleLabel = $c->isSuperAgent() ? 'BDM' : 'Agent';
            $body .= "✅ {$c->name} ({$roleLabel})\n";
            $body .= "📍 {$c->district}, {$c->state}\n";
            $body .= "WhatsApp: wa.me/+91{$c->whatsapp_number}\n\n";
        }

        $body .= "Reply 0 = Main Menu";
        $this->waApi->sendText($to, trim($body));
    }

    private function getRegistrationFields(): array
    {
        $setting = Setting::where('key', 'wa_registration_fields')->first();
        if ($setting && $setting->value) {
            return json_decode($setting->value, true) ?? $this->defaultFields();
        }
        return $this->defaultFields();
    }

    private function defaultFields(): array
    {
        return [
            ['key' => 'name',     'label' => 'Your full name',         'required' => true,  'order' => 1, 'type' => 'text'],
            ['key' => 'mobile',   'label' => 'Your mobile number',     'required' => true,  'order' => 2, 'type' => 'mobile'],
            ['key' => 'state',    'label' => 'Your state',             'required' => true,  'order' => 3, 'type' => 'text'],
            ['key' => 'district', 'label' => 'Your district / city',   'required' => true,  'order' => 4, 'type' => 'text'],
            ['key' => 'area',     'label' => 'Your village / mohalla', 'required' => false, 'order' => 5, 'type' => 'text'],
        ];
    }
}
