<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\User;

$unassignedPublicLeads = Lead::where('source', 'public_form')
    ->whereNull('assigned_super_agent_id')
    ->get(['id', 'ulid', 'beneficiary_name', 'status']);

echo "Unassigned Public Leads:\n";
foreach ($unassignedPublicLeads as $lead) {
    echo "ID: {$lead->id} | ULID: {$lead->ulid} | Name: {$lead->beneficiary_name} | Status: {$lead->status}\n";
}

$superAgent = User::where('name', 'like', '%BILAL TALI%')->orWhere('super_agent_code', 'SSM-2026-1001')->first();
if ($superAgent) {
    echo "\nSuper Agent found:\n";
    echo "ID: {$superAgent->id} | Name: {$superAgent->name} | Code: {$superAgent->super_agent_code} | Status: {$superAgent->status}\n";
} else {
    echo "\nSuper Agent 'BILAL TALI' not found.\n";
}
