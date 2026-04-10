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
        @font-face {
            font-family: 'Cinzel';
            src: local('Cinzel'), url('{{ public_path("fonts/Cinzel-Bold.ttf") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'DM Sans';
            src: local('DM Sans'), url('{{ public_path("fonts/DMSans-Regular.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'DM Sans';
            src: local('DM Sans'), url('{{ public_path("fonts/DMSans-Bold.ttf") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'DM Sans', 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.5;
            color: #04111F;
            margin: 0;
            padding: 1.5cm 2.0cm;
            background: #fff;
        }

        .header-section {
            width: 100%;
            border-bottom: 2.5px solid #0A1931;
            padding-bottom: 12px;
            margin-bottom: 25px;
            position: relative;
        }

        .header-logo {
            float: left;
            width: 80px;
            height: 80px;
        }
        
        .header-logo img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }

        .header-branding {
            margin-left: 95px;
            text-align: right;
        }

        .company-name {
            font-family: 'Cinzel', serif;
            font-size: 22pt;
            font-weight: bold;
            color: #0A1931;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 1.0;
        }
        
        .company-tagline {
            font-family: 'DM Sans', sans-serif;
            font-size: 9pt;
            color: #F7B100;
            margin: 4px 0;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .affiliation-partner {
            font-family: 'DM Sans', sans-serif;
            font-size: 8pt;
            color: #64748B;
            font-weight: 700;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-contact {
            margin-top: 8px;
            font-size: 8pt;
            color: #475569;
            line-height: 1.3;
        }

        .metadata-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }

        .meta-label {
            font-weight: bold;
            color: #4A5568;
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        .salutation {
            margin-bottom: 20px;
        }
        .subject {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            font-size: 11pt;
            text-decoration: underline;
            text-transform: uppercase;
            line-height: 1.4;
            color: #0A1931;
        }
        .content {
            text-align: justify;
            margin-bottom: 12px;
            clear: both;
        }

        .terms-box {
            background: #F8FAFC;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #0A1931;
            margin: 15px 0;
        }

        .terms-title {
            font-family: 'Helvetica', Arial, sans-serif;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 9pt;
            text-transform: uppercase;
            color: #0A1931;
        }
        .terms-list {
            padding-left: 18px;
            margin: 0;
            font-size: 8.5pt;
        }
        .terms-list li {
            margin-bottom: 4px;
            text-align: justify;
        }
        
        .signature-section {
            margin-top: 30px;
            width: 100%;
            height: 120px;
        }

        .sig-container {
            float: right;
            width: 250px;
            text-align: center;
            position: relative;
        }

        .sig-image {
            max-width: 160px;
            max-height: 70px;
        }
        
        .seal-image {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            height: 90px;
            width: auto;
            opacity: 0.8;
            z-index: 5;
        }

        .sig-name {
            font-weight: bold;
            font-size: 11pt;
            margin: 0;
            color: #0A1931;
        }

        .sig-title {
            font-size: 9pt;
            color: #64748B;
            text-transform: uppercase;
            font-family: 'Helvetica', Arial, sans-serif;
            font-weight: 700;
        }
        
        .footer {
            position: absolute;
            bottom: 1.5cm;
            left: 2.0cm;
            right: 2.0cm;
            text-align: center;
            font-size: 8pt;
            color: #94A3B8;
            border-top: 1.2px solid #E2E8F0;
            padding-top: 12px;
            font-family: 'Helvetica', Arial, sans-serif;
        }
        
        .footer-note {
            margin-bottom: 4px;
        }
        
        .footer-site {
            font-weight: 700;
            color: #0A1931;
            text-transform: lowercase;
            letter-spacing: 0.5px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 100pt;
            font-weight: 900;
            color: rgba(226, 232, 240, 0.15);
            z-index: -1;
            white-space: nowrap;
            font-family: 'Helvetica', Arial, sans-serif;
            text-transform: uppercase;
            pointer-events: none;
        }

        .barcode-container {
            margin-top: 8px;
        }
        .barcode-img {
            height: 28px;
            width: auto;
        }
        .qr-section {
            text-align: right;
        }
        .qr-img {
            width: 85px;
            height: 85px;
            border: 1px solid #E2E8F0;
            padding: 4px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .verification-text {
            font-size: 7pt;
            color: #64748B;
            margin-top: 4px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

    </style>
</head>
<body>
    <div class="watermark">{{ $companyName }}</div>

    <div class="header-section">
        <div class="header-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo">
            @endif
        </div>
        <div class="header-branding">
            <h1 class="company-name">{{ $companyName }}</h1>
            <div class="company-tagline">{{ $companyTagline }}</div>
            <div class="affiliation-partner">AN AFFILIATION PARTNER OF {{ $globalName }}</div>
            <div class="company-contact">
                {{ $companyAddress }}<br>
                Phone: {{ $companyPhone }} &nbsp;|&nbsp; Email: {{ $companyEmail }}<br>
                <span class="footer-site">Website: {{ $companyWebsite }}</span>
            </div>
        </div>
    </div>

    <table class="metadata-table" cellspacing="0" cellpadding="0">
        <tr>
            <td width="60%">
                <span class="meta-label">Letter No:</span> <strong>{{ $letterNumber }}</strong>
                @if($barcodeBase64)
                    <div class="barcode-container">
                        <img src="{{ $barcodeBase64 }}" class="barcode-img">
                    </div>
                @endif
            </td>
            <td width="40%" align="right" valign="top">
                <span class="meta-label">Date:</span> <strong>{{ ($user->approved_at ?? null) ? $user->approved_at->format('d F, Y') : date('d F, Y') }}</strong>
                @if($qrBase64)
                    <div class="qr-section" style="margin-top: 10px;">
                        <img src="{{ $qrBase64 }}" class="qr-img">
                        <div class="verification-text">Scan to Verify</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>


    <div class="salutation">
        To,<br>
        <strong>{{ $user->name ?? 'N/A' }}</strong><br>
        {{ ($user->current_address ?? $user->area ?? 'N/A') }}<br>
        Contact: {{ $user->mobile ?? 'N/A' }}
    </div>

    <div class="subject">
        Subject: Letter of Appointment as {{ $designation }}
    </div>

    <div class="content">
        Dear {{ explode(' ', $user->name ?? 'User')[0] }},<br><br>
        Following your successful application and subsequent evaluation, we are pleased to appoint you as <strong>{{ $designation }}</strong> at <strong>{{ $companyName }}</strong>.
    </div>

    <div class="content">
        {!! $body !!}
    </div>

    <div class="terms-box">
        <div class="terms-title">Standard Terms & Conditions</div>
        <ul class="terms-list">
            @foreach($terms as $term)
                <li>{{ $term }}</li>
            @endforeach
        </ul>
    </div>

    <div class="content">
        We welcome you to the <strong>{{ $companyName }}</strong> family and are confident that your contribution will be instrumental in our mission. We look forward to a mutually beneficial association.
    </div>

    <p class="body-para" style="font-size: 10.5px; color: #555; margin-top: 12px; margin-left: 45px; margin-right: 45px;">
        For verification of this letter, visit
        <strong style="color: #0A3D7A;">{{ $companyWebsite ?? 'suryamitra.in' }}</strong>
        or contact our office.
    </p>

    <div class="signature-section">
        <div class="sig-container">
            <div style="position: relative; height: 80px; margin-bottom: 5px;">
                @if($sealBase64)
                    <img src="{{ $sealBase64 }}" class="seal-image">
                @endif
                @if($sigBase64)
                    <img src="{{ $sigBase64 }}" class="sig-image" style="position: relative; z-index: 10;">
                @endif
            </div>
            <div class="sig-name">{{ $authorizedSignatory }}</div>
            <div class="sig-title">{{ $signatoryTitle }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-note">{{ $settings['letter_footer_note'] ?? 'This is a computer-generated document and carries a digital signature for authenticity.' }}</div>
        <div class="footer-site">Website: {{ $companyWebsite ?? 'suryamitra.in' }}</div>
    </div>
</body>
</html>
