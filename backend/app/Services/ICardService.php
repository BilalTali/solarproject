<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'admin' => $user->agent_id ?? ('ADM-'.str_pad($user->id, 4, '0', STR_PAD_LEFT)),
            'super_agent' => $user->super_agent_code ?? ('SSM-'.str_pad($user->id, 4, '0', STR_PAD_LEFT)),
            'agent' => $user->agent_id ?? ('SM-'.str_pad($user->id, 4, '0', STR_PAD_LEFT)),
            'enumerator' => $user->enumerator_id ?? ('ENM-'.str_pad($user->id, 4, '0', STR_PAD_LEFT)),
            default => 'SMS-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
        };

        // ── Designation ───────────────────────────────────────────
        $designation = match ($user->role) {
            'admin' => 'ADMINISTRATOR',
            'super_agent' => 'BUSINESS DEVELOPMENT MANAGER',
            'agent' => 'BUSINESS DEVELOPMENT EXECUTIVE',
            'enumerator' => 'FIELD ENUMERATOR',
            default => 'MEMBER',
        };

        // ── Initials (for avatar fallback) ────────────────────────
        $nameParts = explode(' ', trim($user->name));
        $initials = strtoupper(
            (isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : '').
            (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
        );

        $adminId = $user->getRootAdminId();

        // ── Branding Assets (Dynamic) ─────────────────────────────
        // Logo 1: Admin's own company logo (overridable)
        $logoPath = Setting::getValue('company_logo', null, $adminId, false);
        
        // Logo 2: Affiliate Partner Logo
        // Prioritize Admin's secondary logo (company_logo_2), fallback to Global company_logo
        $logoPath2 = Setting::getValue('company_logo_2', null, $adminId, false)
                     ?? Setting::getValue('company_logo', null, null, false);
        
        $signaturePath = Setting::getValue('company_signature', null, $adminId, false);
        $sealPath = Setting::getValue('company_seal', null, $adminId, false);

        $logoBase64 = $this->getBase64Image($logoPath);
        $logoBase64_2 = $this->getBase64Image($logoPath2);
        $sigBase64 = $this->getBase64Image($signaturePath);
        $sealBase64 = $this->getBase64Image($sealPath);

        // ── Company Settings ──────────────────────────────────────
        $companyName = Setting::getValue('company_name', 'SURYAMITRA SOLAR NETWORK', null);
        $companyAddress = Setting::getValue('company_address', 'Srinagar, Jammu & Kashmir', $adminId);
        $companyEmail = Setting::getValue('company_email', 'info@suryamitra.in', $adminId);
        $companyRegNo = Setting::getValue('company_registration_no', '', null);
        $websiteUrl = Setting::getValue('company_website', 'suryamitra.in', $adminId);
        $companyWebsite = preg_replace('#^https?://#', '', $websiteUrl);
        $companyPhone = Setting::getValue('company_phone', '', $adminId);

        $companyAffiliatedWith = Setting::getValue('company_affiliated_with', '', $adminId);
        $authorizedSignatory = Setting::getValue('authorized_signatory', 'Authorized Signatory', $adminId);

        // ── Card Content Settings (Dynamic) ────────────────────────
        $icardClearance = Setting::getValue('icard_clearance', 'Level-V (Elite)', $adminId);
        $companyEmergency = Setting::getValue('company_emergency', '102', $adminId);
        $icardVerifiedBy = Setting::getValue('icard_verified_by', 'CHIEF OPERATIONS OFFICER', $adminId);

        $icardWarningText = Setting::getValue('icard_warning_text', 'This ID card remains the property of the issuing authority.', $adminId);

        // ── QR Code & Verification (New Phase 30) ──────────────────
        $frontendUrl = config('app.frontend_url') ?? env('APP_FRONTEND_URL', 'https://andleebsurya.in');
        $verifyUrl = $user->qr_token ? rtrim($frontendUrl, '/').'/verify/'.$user->qr_token : null;


        // ── Dynamic Dynamic Content ──
        $barcodeBase64 = $this->generateBarcode($cardNumber);
        $qrCodeBase64 = $this->generateQrCodeLegacy($user);
        $qrBase64 = $this->generateQrCode($user);


        // ── Joining Date ───────────────────────────────────────────
        $joiningDate = ($user->approved_at ?? $user->joining_date)
            ? ($user->approved_at ?? $user->joining_date)->format('d-m-Y')
            : date('d-m-Y');

        $dob = $user->dob ? $user->dob->format('d-m-Y') : 'N/A';
        $mobile = $user->mobile ?? '—';
        $address = Str::limit($user->address ?? 'Pampore, Pulwama', 35);

        $profilePhotoBase64 = $this->getProcessedProfilePhoto($user->profile_photo);
        
        $validUntil = ($user->approved_at ?? $user->joining_date)
            ? ($user->approved_at ?? $user->joining_date)->addYear()->format('d M Y')
            : now()->addYear()->format('d M Y');
        $issuedDate = ($user->approved_at ?? $user->joining_date)
            ? ($user->approved_at ?? $user->joining_date)->format('d M Y')
            : now()->format('d M Y');

        $affiliatedPartner = Setting::getValue('company_affiliated_with', null, $adminId)
                             ?: Setting::getValue('company_name', 'AndleebSurya Platform', null);

        return compact(
            'user',
            'cardNumber',
            'designation',
            'initials',
            'joiningDate',
            'dob',
            'mobile',
            'address',
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
            'verifyUrl',
            'profilePhotoBase64',
            'validUntil',
            'issuedDate',
            'affiliatedPartner'
        );





    }

    /**
     * Generate barcode image and return its temp file path.
     */
    private function generateBarcode(string $cardNumber): ?string
    {
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG;
            $barcodeData = $generator->getBarcode(
                $cardNumber,
                \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128,
                2,   // width factor
                50   // height in pixels
            );

            return 'data:image/png;base64,'.base64_encode($barcodeData);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Generate a QR code for the user's verification token.
     */
    private function generateQrCode(User $user): ?string
    {
        if (! $user->qr_token) {
            $user->qr_token = bin2hex(random_bytes(16));
            $user->save();
        }

        $frontendUrl = config('app.frontend_url') ?? env('APP_FRONTEND_URL', 'https://andleebsurya.in');
        $url = rtrim($frontendUrl, '/').'/verify/'.$user->qr_token;

        return $this->generatePremiumQr($url);
    }


    /**
     * Generate a premium styled QR code with fallbacks.
     */
    private function generatePremiumQr(string $data): ?string
    {
        try {
            // Use SVG format as it doesn't require imagick
            $qrData = QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->errorCorrection('H')
                ->color(10, 25, 49)
                ->generate($data);

            return 'data:image/svg+xml;base64,'.base64_encode($qrData);
        } catch (\Throwable $e) {

            try {
                // Fallback 1: Simpler style (often works with standard GD)
                $qrData = QrCode::format('png')
                    ->size(300)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->color(10, 25, 49)
                    ->generate($data);

                return 'data:image/png;base64,'.base64_encode($qrData);
            } catch (\Throwable $e2) {
                // Fallback 2: Basic PNG
                try {
                    $qrData = QrCode::format('svg')
                        ->size(300)
                        ->generate($data);

                    return 'data:image/svg+xml;base64,'.base64_encode($qrData);

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
        if (! $user->qr_token) {
            $user->qr_token = bin2hex(random_bytes(16));
            $user->save();
        }

        $frontendUrl = env('FRONTEND_URL', config('app.url'));
        $url = rtrim($frontendUrl, '/').'/verify/'.$user->qr_token;

        return $this->generatePremiumQr($url);
    }

    /**
     * Generate PDF and return as download response.
     */
    public function generateAndDownload(User $user): Response
    {
        $data = $this->buildViewData($user);
        $html = view('icard.index', $data)->render();
        $filename = 'SuryaMitra-ID-Card-'.Str::slug($user->name).'-'.$data['cardNumber'].'.pdf';

        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', [base_path(), public_path(), storage_path()]);

        $dompdf = new Dompdf($options);
        // 210mm x 148mm (A5 Landscape or 2 x A6 Side-by-Side)
        // 105mm ~ 297.64pt, 148mm ~ 419.53pt
        // Set paper size to match card dimensions (approx 227pt x 368pt at 72dpi for 302px x 490px)
        $dompdf->setPaper([0, 0, 227, 368]); 

        $dompdf->loadHtml($html);
        $dompdf->render();

        return response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    /**
     * Get base64 encoded image string for DOMPDF.
     */
    private function getBase64Image(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // Handle full URLs if they were passed through (strip prefix to get relative path)
        if (str_starts_with($path, 'http')) {
            $storageUrl = asset('storage/');
            if (str_starts_with($path, $storageUrl)) {
                $path = str_replace($storageUrl, '', $path);
            } else {
                // External URL: try to download or fallback to placeholder
                try {
                    $content = file_get_contents($path);
                    if ($content) {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->buffer($content);
                        return 'data:' . $mimeType . ';base64,' . base64_encode($content);
                    }
                } catch (\Throwable $e) {
                    // Fallback to placeholder if download fails
                }
            }
        }

        // 1. Try public path first (for assets/images)
        $fullPath = public_path($path);
        if (file_exists($fullPath) && ! is_dir($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);

            return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($fullPath));
        }

        // 2. Try storage disk (for user uploads)
        if (Storage::disk('public')->exists($path)) {
            $content = Storage::disk('public')->get($path);
            $type = pathinfo($path, PATHINFO_EXTENSION);

            return 'data:image/'.$type.';base64,'.base64_encode($content);
        }

        // Fallback placeholder logo (Simple SVG Circle with initials if file missing)
        return 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="48" fill="#0A1931"/><text x="50" y="65" font-family="Arial" font-size="40" font-weight="bold" fill="#F7B100" text-anchor="middle">AS</text></svg>');
    }

    /**
     * Process profile photo: Crop to square, resize, and apply hexagon mask if possible.
     */
    private function getProcessedProfilePhoto(?string $path): ?string
    {
        if (!$path) return null;

        $absPath = storage_path('app/public/' . $path);
        if (!file_exists($absPath)) return null;

        try {
            if (!function_exists('imagecreatefromstring')) {
                return 'data:image/png;base64,' . base64_encode(file_get_contents($absPath));
            }

            $img = @imagecreatefromstring(file_get_contents($absPath));
            if (!$img) return 'data:image/png;base64,' . base64_encode(file_get_contents($absPath));

            $w = imagesx($img);
            $h = imagesy($img);
            $size = min($w, $h);
            
            // 1. Crop to square
            $cropped = imagecrop($img, ['x' => ($w - $size) / 2, 'y' => ($h - $size) / 2, 'width' => $size, 'height' => $size]);
            if (!$cropped) $cropped = $img;

            // 2. Resize
            $finalSize = 250;
            $resized = imagecreatetruecolor($finalSize, $finalSize);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $cropped, 0, 0, 0, 0, $finalSize, $finalSize, imagesx($cropped), imagesy($cropped));

            // 3. Apply Hexagon Mask
            $mask = imagecreatetruecolor($finalSize, $finalSize);
            $magenta = imagecolorallocate($mask, 255, 0, 255);
            $white = imagecolorallocate($mask, 255, 255, 255);
            imagefill($mask, 0, 0, $magenta);
            
            $points = [125, 5, 242, 65, 242, 185, 125, 245, 8, 185, 8, 65];
            imagefilledpolygon($mask, $points, 6, $white);

            for ($x = 0; $x < $finalSize; $x++) {
                for ($y = 0; $y < $finalSize; $y++) {
                    if (imagecolorat($mask, $x, $y) == $magenta) {
                        imagesetpixel($resized, $x, $y, imagecolorallocatealpha($resized, 0, 0, 0, 127));
                    }
                }
            }

            // 4. Draw Border
            $navy = imagecolorallocate($resized, 10, 25, 49);
            imagesetthickness($resized, 4);
            imagepolygon($resized, $points, 6, $navy);

            ob_start();
            imagepng($resized);
            $base64 = 'data:image/png;base64,' . base64_encode(ob_get_clean());

            imagedestroy($img);
            if ($cropped !== $img) imagedestroy($cropped);
            imagedestroy($resized);
            imagedestroy($mask);

            return $base64;
        } catch (\Throwable $e) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($absPath));
        }
    }
}