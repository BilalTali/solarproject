<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $receiptSerial }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 1.2cm;
        }
        body {
            font-family: 'Outfit', sans-serif;
            font-size: 10pt;
            color: #2D3436;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .currency { font-family: 'DejaVu Sans', sans-serif; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* World-Class Heading */
        .doc-title-container {
            text-align: center;
            margin: 20px 0 30px 0;
            border-top: 1px solid #DFE6E9;
            border-bottom: 2.5px double #2D3436;
            padding: 12px 0;
        }
        .doc-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20pt;
            text-transform: uppercase;
            letter-spacing: 6px;
            color: #2D3436;
            font-weight: 600;
        }

        .header-table td { vertical-align: top; }
        .logo { max-width: 160px; max-height: 70px; }
        .company-info { text-align: right; font-size: 8pt; color: #636E72; }
        .company-name-bold { font-size: 15pt; font-weight: 600; color: #000; }

        .receipt-card {
            margin: 20px 0;
            padding: 30px;
            border: 1px solid #F0F0F0;
            background-color: #FCFBFA;
        }

        .hero-amount {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32pt;
            font-weight: 600;
            color: #A68636;
            text-align: center;
            margin: 25px 0;
            border-top: 1px solid #EBE7E1;
            border-bottom: 1px solid #EBE7E1;
            padding: 15px 0;
        }

        .detail-group { margin-bottom: 20px; border-bottom: 1px solid #F0F0F0; padding-bottom: 10px; }
        .label {
            display: block;
            font-size: 7.5pt;
            font-weight: 600;
            color: #A68636;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 5px;
        }
        .value { font-size: 12pt; color: #2D3436; font-weight: 500; }
        .value.serif-bold { font-family: 'Cormorant Garamond', serif; font-size: 16pt; font-weight: 600; }

        .signature-area { text-align: center; width: 220px; margin-left: auto; }
        .sig-line { border-top: 1px solid #2D3436; margin-top: 15px; padding-top: 5px; font-size: 8pt; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }

        .footer-note {
            text-align: center;
            font-size: 7.5pt;
            color: #B2BEC3;
            margin-top: 50px;
            border-top: 1px solid #F0F0F0;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo">
                @else
                    <div class="company-name-bold serif">{{ $companyName }}</div>
                @endif
            </td>
            <td class="company-info">
                <div class="company-name-bold serif">{{ $companyName }}</div>
                <div>{{ $companyAddress }}</div>
                <div>{{ $companyEmail }} | {{ $companyPhone }}</div>
                <div>GSTIN: {{ $companyRegNo }}</div>
            </td>
        </tr>
    </table>

    <!-- World Class Heading -->
    <div class="doc-title-container">
        <div class="doc-title">Payment Acknowledgment</div>
    </div>

    <!-- Meta -->
    <table style="font-size: 9pt; color: #636E72; margin-bottom: 10px;">
        <tr>
            <td>Receipt Serial: <strong>{{ $receiptSerial }}</strong></td>
            <td style="text-align: right;">Date Issued: <strong>{{ $quotationDate }}</strong></td>
        </tr>
    </table>

    <!-- Receipt Center -->
    <div class="receipt-card">
        <div class="detail-group">
            <span class="label">Received With Thanks From</span>
            <span class="value serif-bold">{{ $lead->beneficiary_name }}</span>
        </div>

        <div class="hero-amount">
            <span class="currency">&#8377;</span> {{ number_format($receiptAmount, 2) }}
        </div>

        <div class="detail-group">
            <span class="label">Sum in Words</span>
            <div style="font-style: italic; color: #636E72; font-size: 9.5pt;">
                {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Rupees Only
            </div>
        </div>

        <div class="detail-group">
            <span class="label">On Account Of</span>
            <span class="value" style="font-size: 10pt;">
                Advance partial payment towards {{ $kw }} kW Solar Power Installation as per Project Quotation Ref: {{ $quotationSerial }}.
            </span>
        </div>

        <div class="detail-group" style="border-bottom: none;">
            <span class="label">Payment Status</span>
            <span class="value" style="color: #27AE60; font-weight: 600;">Digitally Confirmed</span>
        </div>
    </div>

    <!-- Bottom Footer Section -->
    <table style="margin-top: 40px;">
        <tr>
            <td style="vertical-align: bottom;">
                <div style="border-left: 2px solid #A68636; padding-left: 12px; font-size: 8pt; color: #636E72;">
                    <strong>Electronic Voucher</strong><br>
                    Authenticated via billing portal. <br>
                    Funds realization subject to bank clearance.
                </div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div class="signature-area">
                    @if($sigBase64)
                        <img src="{{ $sigBase64 }}" style="max-height: 48px; margin-bottom: 5px;">
                    @else
                        <div style="height: 48px;"></div>
                    @endif
                    <div class="sig-line">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        &copy; {{ date('Y') }} {{ $companyName }} &bull; All Rights Reserved. <br>
        Thank you for choosing renewable energy to power your home.
    </div>

</body>
</html>