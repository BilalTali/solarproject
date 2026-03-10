<?php

namespace App\Services;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Dompdf\Options;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class ICardService
{
    /**
     * Build all view data for the icard Blade template.
     */
    public function buildViewData(User $user): array
    {
        // ── Card Number ───────────────────────────────────────────
        // ── Card Number ───────────────────────────────────────────
        $cardNumber = match ($user->role) {
            'admin'       => $user->agent_id ?? ('ADM-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)),
            'super_agent' => $user->super_agent_code ?? ('SSM-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)),
            'agent'       => $user->agent_id ?? ('SM-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)),
            default       => 'SMS-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
        };

        // ── Designation ───────────────────────────────────────────
        $designation = match ($user->role) {
            'admin'       => 'ADMINISTRATOR',
            'super_agent' => 'BUSINESS DEVELOPMENT MANAGER',
            'agent'       => 'BUSINESS DEVELOPMENT EXECUTIVE',
            default       => 'MEMBER',
        };


        // ── Initials (for avatar fallback) ────────────────────────
        $nameParts = explode(' ', trim($user->name));
        $initials = strtoupper(
            (isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : '') .
            (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
        );

        
        // ── Branding Assets (Dynamic) ─────────────────────────────
        $logoPath      = Setting::getValue('company_logo');
        $logoPath2     = Setting::getValue('company_logo_2');
        $signaturePath = Setting::getValue('company_signature');
        $sealPath      = Setting::getValue('company_seal');

        $logoBase64    = $this->getBase64Image($logoPath);
        $logoBase64_2  = $this->getBase64Image($logoPath2);
        $sigBase64     = $this->getBase64Image($signaturePath);
        $sealBase64    = $this->getBase64Image($sealPath);

        // ── Company Settings ──────────────────────────────────────
        $companyName         = Setting::getValue('company_name', 'SURYAMITRA SOLAR NETWORK');
        $companyAddress      = Setting::getValue('company_address', 'Srinagar, Jammu & Kashmir');
        $companyEmail        = Setting::getValue('company_email', 'info@suryamitra.in');
        $companyRegNo        = Setting::getValue('company_registration_no', '');
        $websiteUrl          = Setting::getValue('company_website', 'suryamitra.in');
        $companyWebsite      = preg_replace('#^https?://#', '', $websiteUrl);
        $companyPhone        = Setting::getValue('company_phone', '');
        
        $companyAffiliatedWith = Setting::getValue('company_affiliated_with', '');
        $authorizedSignatory = Setting::getValue('authorized_signatory', 'Authorized Signatory');

        // ── Card Content Settings (Dynamic) ────────────────────────
        $icardClearance   = Setting::getValue('icard_clearance', 'Level-V (Elite)');
        $companyEmergency = Setting::getValue('company_emergency', '102');
        $icardVerifiedBy  = Setting::getValue('icard_verified_by', 'CHIEF OPERATIONS OFFICER');

        $icardWarningText = Setting::getValue('icard_warning_text', 'This ID card remains the property of the issuing authority.');

        // ── QR Code & Verification (New Phase 30) ──────────────────
        $qrBase64  = $user->qr_token ? $this->generateQrCode($user) : null;
        $verifyUrl = $user->qr_token ? config('app.url') . '/verify/' . $user->qr_token : null;

        // ── Dynamic Dynamic Content ──
        $barcodeBase64 = $this->generateBarcode($cardNumber);
        $qrCodeBase64 = $this->generateQrCodeLegacy($user); // Existing QR (legacy)

        // ── Joining Date (Prioritize Approval) ───────────────────
        $joiningDate = ($user->approved_at ?? $user->joining_date) 
            ? ($user->approved_at ?? $user->joining_date)->format('d-m-Y')
            : date('d-m-Y');

        return compact(
            'user',
            'cardNumber',
            'designation',
            'initials',
            'joiningDate',
            'companyName',
            'companyAddress',
            'companyEmail',
            'companyWebsite',
            'companyPhone',
            'companyRegNo',
            'companyAffiliatedWith',
            'companyEmergency',
            'authorizedSignatory',
            'icardClearance',
            'icardVerifiedBy',
            'icardWarningText',
            'logoBase64',
            'logoBase64_2',
            'sigBase64',
            'sealBase64',
            'barcodeBase64',
            'qrCodeBase64',
            'qrBase64',
            'verifyUrl'
        );

    }

    /**
     * Generate barcode image and return its temp file path.
     */
    private function generateBarcode(string $cardNumber): ?string
    {
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode(
                $cardNumber,
                \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_128,
                2,   // width factor
                50   // height in pixels
            );

            return 'data:image/png;base64,' . base64_encode($barcodeData);
        } catch (\Throwable $e) {
            return null; 
        }
    }

    /**
     * Generate a QR code for the user's verification token.
     */
    private function generateQrCode(User $user): ?string
    {
        if (!$user->qr_token) {
            $user->qr_token = bin2hex(random_bytes(16));
            $user->save();
        }

        $frontendUrl = env('FRONTEND_URL', config('app.url'));
        $url = rtrim($frontendUrl, '/') . '/verify/' . $user->qr_token;
        return $this->generatePremiumQr($url);
    }

    /**
     * Generate a premium styled QR code with fallbacks.
     */
    private function generatePremiumQr(string $data): ?string
    {
        try {
            // Attempt premium style (requires imagick usually for these markers)
            $qrData = QrCode::format('png')
                ->size(300)
                ->margin(2)
                ->errorCorrection('H')
                ->color(10, 25, 49)
                ->eye('circle')
                ->style('round')
                ->generate($data);

            return 'data:image/png;base64,' . base64_encode($qrData);
        } catch (\Throwable $e) {
            try {
                // Fallback 1: Simpler style (often works with standard GD)
                $qrData = QrCode::format('png')
                    ->size(300)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->color(10, 25, 49)
                    ->generate($data);
                return 'data:image/png;base64,' . base64_encode($qrData);
            } catch (\Throwable $e2) {
                // Fallback 2: Basic PNG
                try {
                    $qrData = QrCode::format('png')
                        ->size(300)
                        ->generate($data);
                    return 'data:image/png;base64,' . base64_encode($qrData);
                } catch (\Throwable $e3) {
                    return null;
                }

            }
        }
    }


    /**
     * Generate a legacy QR code with detailed user information.
     */
    private function generateQrCodeLegacy(User $user): ?string
    {
        if (!$user->qr_token) {
            $user->qr_token = bin2hex(random_bytes(16));
            $user->save();
        }

        $frontendUrl = env('FRONTEND_URL', config('app.url'));
        $url = rtrim($frontendUrl, '/') . '/verify/' . $user->qr_token;
        
        return $this->generatePremiumQr($url);
    }


    /**
     * Generate PDF and return as download response.
     */
    public function generateAndDownload(User $user): Response
    {
        $data     = $this->buildViewData($user);
        $html     = view('icard.index', $data)->render();
        $filename = 'SuryaMitra-ID-Card-' . Str::slug($user->name) . '-' . $data['cardNumber'] . '.pdf';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', [base_path(), public_path(), storage_path()]);

        $dompdf = new Dompdf($options);
        // 210mm x 148mm (A5 Landscape or 2 x A6 Side-by-Side)
        // 105mm ~ 297.64pt, 148mm ~ 419.53pt
        $dompdf->setPaper([0, 0, 595.28, 419.53]); 
        $dompdf->loadHtml($html);
        $dompdf->render();


        return response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    /**
     * Get base64 encoded image string for DOMPDF.
     */
    private function getBase64Image(?string $path): ?string
    {
        if (!$path) return null;

        // Try public path first
        $fullPath = public_path($path);
        if (file_exists($fullPath) && !is_dir($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($fullPath));
        }

        // Try storage disk next
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            $content = \Illuminate\Support\Facades\Storage::disk('public')->get($path);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            return 'data:image/' . $type . ';base64,' . base64_encode($content);
        }

        return null;
    }
}
