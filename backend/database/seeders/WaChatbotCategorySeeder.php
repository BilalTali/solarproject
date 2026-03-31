<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaChatbotCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Seed categories — names MUST match faqs.category strings exactly.
        // The chatbot links them by matching wa_chatbot_categories.name = faqs.category.
        $categories = [
            [
                'name'        => 'Subsidy',
                'description' => 'Subsidy amounts & scheme details',
                'icon_emoji'  => '💰',
                'sort_order'  => 1,
                'is_active'   => true,
            ],
            [
                'name'        => 'Eligibility',
                'description' => 'Who can apply for the scheme',
                'icon_emoji'  => '✅',
                'sort_order'  => 2,
                'is_active'   => true,
            ],
            [
                'name'        => 'Registration',
                'description' => 'How to register & documents needed',
                'icon_emoji'  => '📝',
                'sort_order'  => 3,
                'is_active'   => true,
            ],
            [
                'name'        => 'Installation',
                'description' => 'Installation timeline & process',
                'icon_emoji'  => '🔧',
                'sort_order'  => 4,
                'is_active'   => true,
            ],
            [
                'name'        => 'Maintenance',
                'description' => 'Panel care & warranty',
                'icon_emoji'  => '🛠️',
                'sort_order'  => 5,
                'is_active'   => true,
            ],
            [
                'name'        => 'Support',
                'description' => 'Track status & get help',
                'icon_emoji'  => '📞',
                'sort_order'  => 6,
                'is_active'   => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('wa_chatbot_categories')->updateOrInsert(
                ['name' => $category['name']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Seed WhatsApp registration form fields into the settings table.
        // Super Admin can modify these from the Chatbot Config panel → Registration Form tab.
        // Uses the same JSON setting pattern as eligibility_questions_json.
        Setting::updateOrCreate(
            ['key' => 'wa_registration_fields'],
            [
                'value' => json_encode([
                    [
                        'key'      => 'name',
                        'label'    => 'Your full name',
                        'required' => true,
                        'order'    => 1,
                        'type'     => 'text',
                    ],
                    [
                        'key'      => 'mobile',
                        'label'    => 'Your mobile number (10 digits)',
                        'required' => true,
                        'order'    => 2,
                        'type'     => 'mobile',
                    ],
                    [
                        'key'      => 'state',
                        'label'    => 'Your state (e.g. Uttar Pradesh)',
                        'required' => true,
                        'order'    => 3,
                        'type'     => 'text',
                    ],
                    [
                        'key'      => 'district',
                        'label'    => 'Your district / city',
                        'required' => true,
                        'order'    => 4,
                        'type'     => 'text',
                    ],
                    [
                        'key'      => 'area',
                        'label'    => 'Your village / mohalla / town (or type Skip)',
                        'required' => false,
                        'order'    => 5,
                        'type'     => 'text',
                    ],
                ]),
                'group' => 'whatsapp',
            ]
        );

        $this->command->info('WaChatbotCategorySeeder: 6 categories + wa_registration_fields seeded.');
    }
}
