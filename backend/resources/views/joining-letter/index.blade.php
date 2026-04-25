@php
    /** @var \App\Models\User $user */
    /** @var string $companyName */
    /** @var string $companyTagline */
    /** @var string $companyAddress */
    /** @var string|null $companyAffiliatedWith */
    /** @var string $companyPhone */
    /** @var string $companyEmail */
    /** @var string $companyWebsite */
    /** @var string $letterNumber */
    /** @var string|null $barcodeBase64 */
    /** @var string|null $qrBase64 */
    /** @var string $designation */
    /** @var string $body */
    /** @var array $terms */
    /** @var string|null $logoBase64 */
    /** @var string|null $logoBase64_2 */
    /** @var string|null $sigBase64 */
    /** @var string $authorizedSignatory */
    /** @var string $signatoryTitle */
    /** @var array $settings */
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Appointment Letter - {{ isset($user) ? $user->name : 'User' }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.8cm 1.5cm 1.5cm 1.5cm;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #04111F;
            background: #fff;
            position: relative;
        }

        /* ── Header ───────────────────────────────── */
        .company-name {
            font-family: 'Times New Roman', serif;
            font-size: 19pt;
            font-weight: bold;
            color: #0A1931;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 1;
            letter-spacing: 1.5px;
        }
        .company-tagline {
            font-size: 7.5pt;
            font-weight: bold;
            color: #F7B100;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .affiliation-partner {
            font-size: 7pt;
            color: #64748B;
            font-weight: bold;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Metadata row ─────────────────────────── */
        .metadata-table {
            width: 100%;
            margin-bottom: 6px;
            font-size: 8.5pt;
        }
        .meta-label {
            font-weight: bold;
            color: #4A5568;
            font-size: 7.5pt;
            text-transform: uppercase;
        }

        /* ── Body sections ────────────────────────── */
        .salutation {
            margin-bottom: 6px;
        }
        .subject {
            text-align: center;
            margin: 6px 0;
            font-weight: bold;
            font-size: 9.5pt;
            text-decoration: underline;
            text-transform: uppercase;
            line-height: 1.2;
            color: #0A1931;
        }
        .content {
            text-align: justify;
            margin-bottom: 6px;
            clear: both;
        }

        /* ── Terms box ────────────────────────────── */
        .terms-box {
            background: #F8FAFC;
            padding: 5px 10px;
            border-left: 3px solid #0A1931;
            margin: 6px 0;
        }
        .terms-title {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #0A1931;
        }
        .terms-list {
            padding-left: 14px;
            margin: 0;
            font-size: 7.5pt;
        }
        .terms-list li {
            margin-bottom: 2px;
            text-align: justify;
        }

        /* ── Signature ────────────────────────────── */
        .signature-section {
            margin-top: 8px;
            width: 100%;
        }
        .sig-container {
            float: right;
            width: 220px;
            text-align: center;
            position: relative;
        }
        .sig-image {
            max-width: 130px;
            max-height: 50px;
        }
        .seal-image {
            position: absolute;
            left: 50%;
            top: 50%;
            height: 75px;
            width: auto;
            opacity: 0.25;
            z-index: 5;
        }
        .sig-name {
            font-weight: bold;
            font-size: 9pt;
            margin: 0;
            color: #0A1931;
        }
        .sig-title {
            font-size: 7.5pt;
            color: #64748B;
            text-transform: uppercase;
            font-weight: 700;
        }

        /* ── Footer ───────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6.5pt;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
            padding-top: 4px;
        }
        .footer-site {
            font-weight: 700;
            color: #0A1931;
            text-transform: lowercase;
        }

        /* ── Watermark ────────────────────────────── */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            font-size: 80pt;
            font-weight: 900;
            color: rgba(226, 232, 240, 0.12);
            z-index: -1;
            white-space: nowrap;
            text-transform: uppercase;
        }

        /* ── Barcode & QR ─────────────────────────── */
        .barcode-container { margin-top: 3px; }
        .barcode-img { height: 22px; width: auto; }
        .qr-section { text-align: right; }
        .qr-img {
            width: 68px;
            height: 68px;
            border: 1px solid #E2E8F0;
            padding: 3px;
            background: white;
        }
        .verification-text {
            font-size: 6pt;
            color: #64748B;
            margin-top: 2px;
            text-transform: uppercase;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="watermark">{{ $companyName }}</div>

    {{-- ── HEADER ── --}}
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 8px; border-bottom: 2px solid #0A1931; padding-bottom: 8px;">
        <tr>
            <td width="18%" valign="middle" align="left">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-width: 65px; max-height: 65px;" alt="Primary Logo">
                @endif
            </td>
            <td width="64%" valign="middle" align="center">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-tagline">{{ $companyTagline }}</div>
                <div class="affiliation-partner">An Affiliation Partner of {{ $globalName }}</div>
            </td>
            <td width="18%" valign="middle" align="right">
                @if($logoBase64_2)
                    <img src="{{ $logoBase64_2 }}" style="max-width: 65px; max-height: 65px;" alt="Secondary Logo">
                @endif
            </td>
        </tr>
    </table>

    {{-- ── LETTER NUMBER + DATE + QR ── --}}
    <table class="metadata-table" cellspacing="0" cellpadding="0">
        <tr>
            <td width="65%" valign="top">
                <span class="meta-label">Date:</span> <strong>{{ ($user->approved_at ?? null) ? $user->approved_at->format('d F, Y') : date('d F, Y') }}</strong><br>
                <span class="meta-label">Letter No:</span> <strong>{{ $letterNumber }}</strong>
                @if($barcodeBase64)
                    <div class="barcode-container">
                        <img src="{{ $barcodeBase64 }}" class="barcode-img">
                    </div>
                @endif
            </td>
            <td width="35%" align="right" valign="top">
                @if($qrBase64)
                    <div class="qr-section">
                        <img src="{{ $qrBase64 }}" class="qr-img">
                        <div class="verification-text">Scan to Verify</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── ADDRESS ── --}}
    <div class="salutation">
        To,<br>
        <strong>{{ $user->name ?? 'N/A' }}</strong><br>
        {{ ($user->current_address ?? $user->area ?? 'N/A') }}<br>
        Contact: {{ $user->mobile ?? 'N/A' }}
    </div>

    {{-- ── SUBJECT ── --}}
    <div class="subject">Subject: Letter of Appointment as {{ $designation }}</div>

    {{-- ── BODY ── --}}
    <div class="content">
        Dear {{ explode(' ', $user->name ?? 'User')[0] }},<br><br>
        Following your successful application and subsequent evaluation, we are pleased to appoint you as <strong>{{ $designation }}</strong> at <strong>{{ $companyName }}</strong>.
    </div>

    <div class="content">
        {!! $body !!}
    </div>

    {{-- ── TERMS ── --}}
    <div class="terms-box">
        <div class="terms-title">Standard Terms &amp; Conditions</div>
        <ul class="terms-list">
            @foreach($terms as $term)
                <li>{{ $term }}</li>
            @endforeach
        </ul>
    </div>

    {{-- ── CLOSING ── --}}
    <div class="content">
        We welcome you to the <strong>{{ $companyName }}</strong> family and are confident that your contribution will be instrumental in our mission. We look forward to a mutually beneficial association.
    </div>

    {{-- ── SIGNATURE ── --}}
    <div class="signature-section">
        <div class="sig-container">
            <div style="position: relative; padding: 6px 0;">
                @if($sealBase64)
                    <img src="{{ $sealBase64 }}" class="seal-image" style="width: 80px; height: 80px; position: absolute; left: 50%; top: 50%; opacity: 0.2; z-index: 5;">
                @endif
                @if($sigBase64)
                    <img src="{{ $sigBase64 }}" class="sig-image" style="width: 120px; height: auto; position: relative; z-index: 10;">
                @endif
            </div>
            <div class="sig-name" style="margin-top: 3px; border-top: 1px solid #E2E8F0; padding-top: 3px;">{{ $authorizedSignatory }}</div>
            <div class="sig-title">{{ $signatoryTitle }}</div>
        </div>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <div>{{ $settings['letter_footer_note'] ?? 'This is a computer-generated document and carries a digital signature for authenticity.' }}</div>
        <div class="footer-site">Website: {{ $companyWebsite ?? 'suryamitra.in' }}</div>
    </div>
</body>
</html>
