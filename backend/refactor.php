<?php

$baseDir = __DIR__ . '/app/Http/Controllers';

$map = [
    'Api/PublicController.php' => 'Solar/PublicController.php',
    'Api/V1/ICardController.php' => 'Admin/ICardController.php',
    'Api/V1/Portal/LeadController.php' => 'Solar/PortalLeadController.php',
    'Api/V1/Portal/EligibilityController.php' => 'Solar/EligibilityController.php',
    'Api/V1/Auth/AuthController.php' => 'Auth/AuthController.php',
    'Api/V1/SuperAdmin/AdminManagementController.php' => 'Admin/AdminManagementController.php',
    'Api/V1/SuperAdmin/MonitoringController.php' => 'Admin/MonitoringController.php',
    'Api/V1/Admin/IncentiveOfferController.php' => 'Admin/AdminIncentiveOfferController.php',
    'Api/V1/Admin/OperatorController.php' => 'Admin/OperatorController.php',
    'Api/V1/Admin/DashboardController.php' => 'Admin/AdminDashboardController.php',
    'Api/V1/Admin/SuperAgentController.php' => 'Admin/AdminSuperAgentController.php',
    'Api/V1/Admin/ReportController.php' => 'Admin/ReportController.php',
    'Api/V1/Admin/SettingController.php' => 'Admin/SettingController.php',
    'Api/V1/Admin/AgentController.php' => 'Admin/AdminAgentController.php',
    'Api/V1/Admin/LeadController.php' => 'Admin/AdminLeadController.php',
    'Api/V1/Admin/OfferController.php' => 'Admin/AdminOfferController.php',
    'Api/V1/Admin/EnumeratorController.php' => 'Admin/AdminEnumeratorController.php',
    'Api/V1/Admin/CommissionController.php' => 'Admin/AdminCommissionController.php',
    'Api/V1/Agent/NotificationController.php' => 'Admin/AgentNotificationController.php',
    'Api/V1/Agent/DashboardController.php' => 'Admin/AgentDashboardController.php',
    'Api/V1/Agent/LeadController.php' => 'Admin/AgentLeadController.php',
    'Api/V1/Agent/OfferController.php' => 'Admin/AgentOfferController.php',
    'Api/V1/Agent/EnumeratorController.php' => 'Admin/AgentEnumeratorController.php',
    'Api/V1/Agent/CommissionController.php' => 'Admin/AgentCommissionController.php',
    'Api/V1/Shared/ProfileController.php' => 'Auth/SharedProfileController.php',
    'Api/V1/SuperAgent/NotificationController.php' => 'Admin/SuperAgentNotificationController.php',
    'Api/V1/SuperAgent/DashboardController.php' => 'Admin/SuperAgentDashboardController.php',
    'Api/V1/SuperAgent/AgentController.php' => 'Admin/SuperAgentAgentController.php',
    'Api/V1/SuperAgent/LeadController.php' => 'Admin/SuperAgentLeadController.php',
    'Api/V1/SuperAgent/OfferController.php' => 'Admin/SuperAgentOfferController.php',
    'Api/V1/SuperAgent/EnumeratorController.php' => 'Admin/SuperAgentEnumeratorController.php',
    'Api/V1/SuperAgent/CommissionController.php' => 'Admin/SuperAgentCommissionController.php',
    'Api/V1/JoiningLetterController.php' => 'Admin/JoiningLetterController.php',
    'Api/V1/Enumerator/NotificationController.php' => 'Admin/EnumeratorNotificationController.php',
    'Api/V1/Enumerator/DashboardController.php' => 'Admin/EnumeratorDashboardController.php',
    'Api/V1/Enumerator/ProfileController.php' => 'Auth/EnumeratorProfileController.php',
    'Api/V1/Enumerator/LeadController.php' => 'Admin/EnumeratorLeadController.php',
    'Api/V1/Enumerator/CommissionController.php' => 'Admin/EnumeratorCommissionController.php',
    'Api/V1/LeadDocumentController.php' => 'Solar/LeadDocumentController.php',
    'Api/FeedbackController.php' => 'Solar/FeedbackController.php',
    'Api/MediaController.php' => 'Admin/MediaController.php',
    'Api/DocumentController.php' => 'Solar/DocumentController.php',
    'Api/AchievementController.php' => 'Solar/AchievementController.php',
    'WithdrawalRequestController.php' => 'Admin/WithdrawalRequestController.php'
];

foreach (['Auth', 'Solar', 'Admin'] as $dir) {
    if (!is_dir("$baseDir/$dir")) mkdir("$baseDir/$dir", 0777, true);
}

// 1. Rename files
foreach ($map as $old => $new) {
    if (file_exists("$baseDir/$old")) {
        $content = file_get_contents("$baseDir/$old");
        
        $oldNS = 'App\Http\Controllers\\' . dirname(str_replace('/', '\\', $old));
        if ($oldNS === 'App\Http\Controllers\.') $oldNS = 'App\Http\Controllers';
        $newNS = 'App\Http\Controllers\\' . dirname(str_replace('/', '\\', $new));
        
        $oldClass = basename($old, '.php');
        $newClass = basename($new, '.php');
        
        // Fix namespace
        $content = preg_replace('/namespace\s+'.preg_quote($oldNS, '/').';/', "namespace $newNS;", $content);
        
        // Fix class name
        $content = preg_replace('/class\s+'.$oldClass.'\b/', "class $newClass", $content);
        
        file_put_contents("$baseDir/$new", $content);
        unlink("$baseDir/$old");
        echo "Moved $old to $new\n";
    }
}

// 2. Update api.php
$apiFile = __DIR__ . '/routes/api.php';
$apiContent = file_get_contents($apiFile);

foreach ($map as $old => $new) {
    $oldClassPath = 'App\Http\Controllers\\' . str_replace('/', '\\', substr($old, 0, -4));
    $newClassPath = 'App\Http\Controllers\\' . str_replace('/', '\\', substr($new, 0, -4));
    
    // Some were imported as "use OldClass as Alias;"
    // First, find all imports and replace them.
    // Replace raw usage:
    $apiContent = str_replace($oldClassPath, $newClassPath, $apiContent);
    $apiContent = str_replace("\\$oldClassPath", "\\$newClassPath", $apiContent);
}

// Write temp file and run regex to fix `use App\Http\Controllers\Solar\PortalLeadController as PublicLeadController;` 
// if we renamed them so they don't need aliasing anymore.
// To keep it safe, we just let the aliasing stay if it doesn't hurt.
file_put_contents($apiFile, $apiContent);

echo "Refactor complete.\n";
