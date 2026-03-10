<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommissionSlab;

class CommissionSlabSeeder extends Seeder
{
    public function run(): void
    {
        $slabs = [
            [
                'capacity'            => '1kw',
                'label'               => '1 kW Solar System',
                'agent_commission'    => 3000.00,
                'super_agent_override'=> 300.00,
                'description'         => 'Ideal for small households with low electricity usage',
                'is_active'           => true,
            ],
            [
                'capacity'            => '2kw',
                'label'               => '2 kW Solar System',
                'agent_commission'    => 5000.00,
                'super_agent_override'=> 500.00,
                'description'         => 'Suitable for medium households',
                'is_active'           => true,
            ],
            [
                'capacity'            => '3kw',
                'label'               => '3 kW Solar System',
                'agent_commission'    => 8000.00,
                'super_agent_override'=> 800.00,
                'description'         => 'Best for large families with high consumption',
                'is_active'           => true,
            ],
            [
                'capacity'            => 'above_3kw',
                'label'               => 'Above 3 kW Solar System',
                'agent_commission'    => 12000.00,
                'super_agent_override'=> 1200.00,
                'description'         => 'Commercial or large residential installations',
                'is_active'           => true,
            ],
        ];

        foreach ($slabs as $slab) {
            CommissionSlab::updateOrCreate(
                ['capacity' => $slab['capacity']],
                $slab
            );
        }
    }
}
