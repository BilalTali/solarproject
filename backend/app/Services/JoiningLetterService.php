<?php

namespace App\Services;

use App\Models\User;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class JoiningLetterService
{
    /**
     * Generate unique letter number
     */
    public function generateLetterNumber(User $user): string
    {
        $prefix = $user->role === 'super_agent' ? 'SMS/BDM/' : 'SMS/BDE/';
        $year = date('Y');
        $lastLetter = User::where('letter_number', 'like', "{$prefix}{$year}/%")
            ->orderBy('letter_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastLetter) {
            $parts = explode('/', $lastLetter->letter_number);
            $sequence = (int) end($parts) + 1;
        }

        return $prefix . $year . '/' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Build view data for the joining letter
     */
    public function buildLetterData(User $user): array
    {
        // ── Card/Letter Number ───────────────────────────────────
        $letterNumber = match ($user->role) {
            'admin'       => 'ADM-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
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

        // ── Company Settings (Shared with ICard) ──────────────────
        $companyName    = Setting::getValue('company_name', 'SURYAMITRA SOLAR NETWORK');
        $companyAddress = Setting::getValue('company_address', 'Srinagar, Jammu & Kashmir');
        $companyEmail   = Setting::getValue('company_email', 'info@suryamitra.in');
        $companyWebsite = Setting::getValue('company_website', 'https://suryamitra.in');
        $companyPhone   = Setting::getValue('company_phone', '');
        $companyRegNo   = Setting::getValue('company_registration_no', '');
        $companyAffiliatedWith = Setting::getValue('company_affiliated_with', '');
        $companyTagline = Setting::getValue('company_tagline', 'Empowering Sustainable Futures');

        // ── Branding Assets (Dynamic) ─────────────────────────────
        $logoPath      = Setting::getValue('company_logo');
        $signaturePath = Setting::getValue('company_signature');
        
        $logoBase64    = $this->getBase64Image($logoPath);
        $sigBase64     = $this->getBase64Image($signaturePath);

        // ── Legacy Setting Fetch for Other Text ────────────────────
        $settings = Setting::whereIn('group', ['company', 'signatory', 'letter'])->get()->pluck('value', 'key')->toArray();

        // ── Process Body Placeholders ─────────────────────────────
        $bodyTemplate = $user->role === 'super_agent' 
            ? ($settings['letter_super_agent_body'] ?? '') 
            : ($settings['letter_agent_body'] ?? '');

        // Basic mask for bank and PAN
        $maskedBank = $user->bank_account_number ? str_repeat('*', max(0, strlen($user->bank_account_number) - 4)) . substr($user->bank_account_number, -4) : 'N/A';
        $maskedPan = $user->pan_number ? substr($user->pan_number, 0, 2) . str_repeat('*', max(0, strlen($user->pan_number) - 5)) . substr($user->pan_number, -3) : 'N/A';

        $placeholders = [
            '{name}' => $user->name,
            '{agent_id}' => $user->agent_id,
            '{super_agent_code}' => $user->super_agent_code,
            '{joining_date}' => $user->joining_date ? date('d-m-Y', strtotime($user->joining_date)) : date('d-m-Y'),
            '{territory}' => $user->territory ?? 'Assigned Regions',
            '{pan}' => $maskedPan,
            '{bank_account}' => $maskedBank,
            '{bank_ifsc}' => $user->bank_ifsc ?? 'N/A',
            '{website}' => $companyWebsite,
            '{company_name}' => $companyName,
        ];

        $body = str_replace(array_keys($placeholders), array_values($placeholders), $bodyTemplate);

        // ── Process Terms ─────────────────────────────────────────
        $termsString = $settings['letter_terms'] ?? '';
        $terms = array_filter(explode("\n", $termsString));

        return [
            'user' => $user,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
            'companyEmail' => $companyEmail,
            'companyWebsite' => $companyWebsite,
            'companyPhone' => $companyPhone,
            'companyRegNo' => $companyRegNo,
            'companyAffiliatedWith' => $companyAffiliatedWith,
            'companyTagline' => $companyTagline,
            'logoBase64' => $logoBase64,
            'sigBase64' => $sigBase64,
            'designation' => $designation,
            'letterNumber' => $letterNumber,
            'body' => $body,
            'terms' => $terms,
            'settings' => $settings,
            'authorizedSignatory' => $settings['signatory_name'] ?? 'Authorized Signatory',
            'signatoryTitle' => $settings['signatory_title'] ?? 'Manager',
            'barcodeBase64' => $this->generateBarcode($letterNumber),
            'qrBase64' => $this->generateQrCode($user),
            'verifyUrl' => $user->qr_token ? config('app.url') . '/verify/' . $user->qr_token : null,
        ];


    }

    /**
     * Generate PDF and return either content or download response
     */
    public function generatePdf(User $user, bool $download = true)
    {
        $data = $this->buildLetterData($user);
        
        $pdf = Pdf::loadView('joining-letter.index', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'helvetica'
            ]);

        $fileName = 'Joining_Letter_' . Str::slug($user->name) . '_' . date('Ymd') . '.pdf';

        if ($download) {
            return $pdf->download($fileName);
        }

        return $pdf->output();
    }

    /**
     * Generate barcode image for the letter.
     */
    private function generateBarcode(string $cardNumber): ?string
    {
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode(
                $cardNumber,
                \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_128,
                2,
                50
            );

            return 'data:image/png;base64,' . base64_encode($barcodeData);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Generate a QR code for the user.
     * Uses verification URL if qr_token exists, otherwise falls back to legacy text.
     */
    private function generateQrCode(User $user): ?string
    {
        $icardClearance = Setting::getValue('icard_clearance', 'Level-V (Elite)');
        $companyName    = Setting::getValue('company_name', 'SURYAMITRA SOLAR NETWORK');
        $cardNumber     = $user->agent_id ?? $user->super_agent_code ?? 'ADM-PENDING';

        if ($user->qr_token) {
            $qrString = config('app.url') . '/verify/' . $user->qr_token;
        } else {
            $qrString = "NAME: {$user->name}\n"
                      . "ID: {$cardNumber}\n"
                      . "FATHER: " . ($user->father_name ?? 'N/A') . "\n"
                      . "DOB: " . ($user->dob ? $user->dob->format('d-m-Y') : 'N/A') . "\n"
                      . "BLOOD: " . ($user->blood_group ?? 'N/A') . "\n"
                      . "CONTACT: " . ($user->mobile ?? 'N/A') . "\n"
                      . "CLEARANCE: {$icardClearance}\n"
                      . "COMPANY: {$companyName}";
        }

        return $this->generatePremiumQr($qrString);
    }


    /**
     * Generate a premium styled QR code with fallbacks.
     */
    private function generatePremiumQr(string $data): ?string
    {
        try {
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
                $qrData = QrCode::format('png')
                    ->size(300)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->color(10, 25, 49)
                    ->generate($data);
                return 'data:image/png;base64,' . base64_encode($qrData);
            } catch (\Throwable $e2) {
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
     * Helper to get base64 image
     */
    private function getBase64Image(?string $path)

    {
        if (!$path) return null;

        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            // Check storage if not in public
            if (Storage::disk('public')->exists($path)) {
                $content = Storage::disk('public')->get($path);
                $mime = Storage::disk('public')->mimeType($path);
                return 'data:' . $mime . ';base64,' . base64_encode($content);
            }
            return null;
        }

        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}
