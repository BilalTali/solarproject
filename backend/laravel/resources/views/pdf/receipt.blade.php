<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $receiptSerial }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #2D3436;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .serif { font-family: 'Times New Roman', Times, serif; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        .rupee-icon {
            width: 12pt;
            height: 12pt;
            vertical-align: middle;
            margin-right: 2px;
        }
        
        /* Branding Header */
        .brand-container {
            border-bottom: 3px solid #f4f4f4;
            padding-bottom: 12px;
            margin-bottom: 30px;
        }
        .company-brand {
            font-family: 'Times New Roman', serif;
            font-size: 20pt;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: bold;
            color: #1a1a1a;
            margin: 0;
        }
        .company-tagline {
            font-size: 8pt;
            color: #A68636;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }

        .header-table td { vertical-align: middle; }
        .logo { max-width: 140px; }
        .company-contact { text-align: right; font-size: 7.5pt; color: #636E72; line-height: 1.4; }

        .doc-title-bar {
            text-align: center;
            margin: 30px 0;
        }
        .doc-title {
            font-family: 'Times New Roman', serif;
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 8px;
            color: #444;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .receipt-card {
            margin: 20px 0;
            padding: 30px;
            border: 1px solid #F0F0F0;
            background-color: #FCFBFA;
        }

        .hero-amount {
            font-family: 'Times New Roman', serif;
            font-size: 32pt;
            font-weight: bold;
            color: #A68636;
            text-align: center;
            margin: 25px 0;
            border-top: 1px solid #EBE7E1;
            border-bottom: 1px solid #EBE7E1;
            padding: 15px 0;
        }

        .detail-group { margin-bottom: 20px; border-bottom: 1px solid #F5F5F5; padding-bottom: 8px; }
        .label {
            display: block;
            font-size: 8pt;
            font-weight: bold;
            color: #A68636;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 5px;
        }
        .value { font-size: 11.5pt; color: #2D3436; font-weight: bold; }
        
        .signature-section {
            margin-top: 80px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            width: 220px;
        }
        .sig-line {
            border-top: 1px solid #2D3436;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        @php
            $rupeeSvg = "data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzIgMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTI0IDhoLTQuMDNhMSAxIDAgMCAwLTEtMWg1LjAzYTEgMSAwIDAgMCAwLTJIOWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDVsLTcgOGgzYTEgMSAwIDAgMCAwIDJsOS0xMGExIDEtMCAwIDAgMC0yaC01di0zaDVhMSAxIDAgMCAwIDAtM2gtNXYtM2g0YTEgMSAwIDAgMCAwLTJ6IiBmaWxsPSIjQTY4NjM2Ii8+PC9zdmc+";
        @endphp
    </style>
</head>
<body>

    <!-- Professional Designer Header -->
    <div class="brand-container">
        <table class="header-table">
            <tr>
                <td style="width: 140px;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="logo">
                    @endif
                </td>
                <td>
                    <div class="company-brand">{{ $companyName }}</div>
                    <div class="company-tagline">{{ $companyAddress }}</div>
                </td>
                <td class="company-contact">
                    {{ $companyEmail }}<br>
                    {{ $companyPhone }}<br>
                    GSTIN: {{ $companyRegNo }}
                </td>
            </tr>
        </table>
    </div>

    <!-- World Class Title -->
    <div class="doc-title-bar">
        <div class="doc-title">Acknowledgement Receipt</div>
    </div>

    <table style="font-size: 9pt; color: #636E72; margin-bottom: 10px;">
        <tr>
            <td>Receipt No: <strong>{{ $receiptSerial }}</strong></td>
            <td style="text-align: right;">Date: <strong>{{ $quotationDate }}</strong></td>
        </tr>
    </table>

    <!-- Receipt Information -->
    <div class="receipt-card">
        <div class="detail-group">
            <span class="label">Received With Thanks From</span>
            <span class="value serif" style="font-size: 15pt;">{{ $lead->beneficiary_name }}</span>
        </div>

        <div class="hero-amount">
            <img src="{{ $rupeeSvg }}" class="rupee-icon" style="width:20pt; height:20pt;"> {{ number_format($receiptAmount, 2) }}
        </div>

        <div class="detail-group">
            <span class="label">Sum In Words</span>
            <div style="font-style: italic; color: #636E72; font-size: 10pt;">
                {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Rupees Only
            </div>
        </div>

        <div class="detail-group" style="border-bottom: none;">
            <span class="label">Partial Consideration For</span>
            <span class="value" style="font-weight: normal; font-size: 10pt;">
                Advance payment towards {{ $kw }} kW Solar Power Installation as per Quotation REF: {{ $quotationSerial }}.
            </span>
        </div>
    </div>

    <div style="margin-top: 30px; border-left: 3px solid #A68636; padding-left: 15px; font-size: 8.5pt; color: #636E72;">
        <strong>Portal Verified Voucher</strong><br>
        This receipt is digitally issued upon successful confirmation of bank remittance.<br>
        Final funds subject to realization at clearing house.
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            @if($sigBase64)
                <img src="{{ $sigBase64 }}" style="max-height: 50px; margin-bottom: 5px;">
            @else
                <div style="height: 50px;"></div>
            @endif
            <div class="sig-line">Authorized Signature</div>
            <div style="font-size: 7.5pt; color: #aaa; margin-top: 2px;">Electronically Authenticated</div>
        </div>
    </div>

    <div style="text-align: center; font-size: 7.5pt; color: #aaa; margin-top: 60px; border-top: 1px solid #eee; padding-top: 15px;">
        &copy; {{ date('Y') }} {{ $companyName }} &bull; Leading the way in Renewable Solar Solutions.
    </div>

</body>
</html>