<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ICardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ICardRefinementTest extends TestCase
{
    use RefreshDatabase;

    public function test_icard_renders_with_refinements()
    {
        $user = User::factory()->create([
            'role' => 'agent',
            'agent_id' => 'SM-2026-1008',
            'name' => 'AFFAN AHMAD TALI1',
            'father_name' => 'dfg',
            'blood_group' => 'A+',
            'mobile' => '8899117816',
            'qr_token' => 'test-token',
        ]);

        $service = new ICardService();
        $data = $service->buildViewData($user);
        
        // Inject dummy signature for testing relocation
        $data['sigBase64'] = 'data:image/png;base64,dummy';

        $html = view('icard.index', $data)->render();


        // Check for card-footer
        $this->assertStringContainsString('class="card-footer"', $html);
        
        // Check for contact-block on back card
        $this->assertStringContainsString('class="contact-block"', $html);
        
        // Assert website is included
        $this->assertStringContainsString('suryamitra.in', $html);
        
        // Back card checks
        $this->assertStringContainsString('Contact Details:', $html);
    }
}
