<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Appointment Letter - {{ $user->name }}</title>
    <style>
        @page {
            margin: 1.0cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.4;
            color: #2D3748;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #2c5282;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .logo-cell {
            width: 60px;
            vertical-align: middle;
        }
        .company-cell {
            vertical-align: middle;
            text-align: right;
        }
        .logo {
            max-width: 60px;
            max-height: 60px;
        }

        .company-name {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 20pt;
            font-weight: bold;
            color: #2c5282;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 1.1;
        }
        .company-tagline {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9pt;
            color: #4A5568;
            margin: 2px 0;
            font-weight: 600;
        }
        .company-info {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 8pt;
            color: #718096;
            line-height: 1.3;
        }
        .metadata-table {
            width: 100%;
            margin-bottom: 10px;
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
            margin-bottom: 15px;
        }
        .subject {
            text-align: center;
            margin: 8px 0;
            font-weight: bold;
            font-size: 10.5pt;
            text-decoration: underline;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .content {
            text-align: justify;
            margin-bottom: 8px;
            clear: both;
        }

        .terms-box {
            background: #F8FAFC;
            padding: 8px 12px;
            border-radius: 5px;
            border-left: 3px solid #2c5282;
            margin: 8px 0;
        }

        .terms-title {
            font-family: 'Helvetica', Arial, sans-serif;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 8.5pt;
            text-transform: uppercase;
            color: #2c5282;
        }
        .terms-list {
            padding-left: 15px;
            margin: 0;
            font-size: 8pt;
        }
        .terms-list li {
            margin-bottom: 2px;
            text-align: justify;
        }
        .signature-section {
            margin-top: 15px;
            width: 100%;
        }

        .sig-container {
            float: right;
            width: 250px;
            text-align: center;
        }
        .sig-image {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 5px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 10.5pt;
            margin: 0;
        }

        .sig-title {
            font-size: 8.5pt;
            color: #718096;
            text-transform: uppercase;
            font-family: 'Helvetica', Arial, sans-serif;
        }
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #A0AEC0;
            border-top: 1px solid #E2E8F0;
            padding-top: 8px;
            font-family: 'Helvetica', Arial, sans-serif;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            font-weight: bold;
            color: rgba(226, 232, 240, 0.2);
            z-index: -1;
            white-space: nowrap;
            font-family: 'Helvetica', Arial, sans-serif;
        }
        .barcode-container {
            margin-top: 5px;
        }
        .barcode-img {
            height: 25px;
            width: auto;
        }
        .qr-section {
            text-align: right;
            margin-bottom: 5px;
        }
        .qr-img {
            width: 70px;
            height: 70px;
            border: 1px solid #E2E8F0;
            padding: 2px;
            border-radius: 4px;
        }

        .verification-text {
            font-size: 6pt;
            color: #718096;
            margin-top: 2px;
            text-transform: uppercase;
            font-family: 'Helvetica', Arial, sans-serif;
        }

    </style>
</head>
<body>
    <div class="watermark">{{ $companyName }}</div>

    <table class="header-table" cellspacing="0" cellpadding="0">
        <tr>
            <td class="logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo">
                @endif
            </td>
            <td class="company-cell">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-tagline">{{ $companyTagline }}</div>
                <div class="company-info" style="line-height: 1.5;">
                    <div>{{ $companyAddress }}</div>
                    @if($companyAffiliatedWith)
                      <div>Affiliated with: {{ $companyAffiliatedWith }}</div>
                    @endif
                    <div>Phone: {{ $companyPhone }}</div>
                    <div>Email: {{ $companyEmail }}</div>
                    <div style="color: #FF9500; font-weight: 700;">Website: {{ $companyWebsite }}</div>
                </div>
            </td>
        </tr>
    </table>

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
                <span class="meta-label">Date:</span> <strong>{{ $user->approved_at ? \Carbon\Carbon::parse($user->approved_at)->format('d F, Y') : date('d F, Y') }}</strong>
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
        <strong>{{ $user->name }}</strong><br>
        {{ $user->current_address ?? $user->area ?? 'N/A' }}<br>
        Contact: {{ $user->mobile }}
    </div>

    <div class="subject">
        Subject: Letter of Appointment as {{ $designation }}
    </div>

    <div class="content">
        Dear {{ explode(' ', $user->name)[0] }},<br><br>
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
            @if($sigBase64)
                <img src="{{ $sigBase64 }}" class="sig-image">
            @else
                <div style="height: 50px;"></div>
            @endif
            <div class="sig-name">{{ $authorizedSignatory }}</div>
            <div class="sig-title">{{ $signatoryTitle }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-note">{{ $settings['letter_footer_note'] ?? 'This is a computer-generated document and carries a digital signature for authenticity.' }}</div>
        <div style="
            font-size: 9px;
            font-weight: 700;
            color: #0A3D7A;
            letter-spacing: 1.5px;
            margin-top: 4px;
            text-transform: lowercase;
        ">Website: {{ $companyWebsite ?? 'suryamitra.in' }}</div>
    </div>
</body>
</html>
