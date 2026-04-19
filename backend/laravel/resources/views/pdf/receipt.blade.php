<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $receiptSerial }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.8cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #2D3436;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .rupee-icon {
            width: 11pt;
            height: 11pt;
            vertical-align: middle;
            margin-right: 2px;
        }
        
        /* World-Class Compact Heading */
        .doc-header {
            text-align: center;
            margin: 10px 0 20px 0;
            border-top: 1px solid #DFE6E9;
            border-bottom: 2px solid #2D3436;
            padding: 8px 0;
        }
        .doc-title {
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 5px;
            color: #2D3436;
            font-weight: bold;
        }

        .header-table td { vertical-align: top; }
        .logo { max-width: 140px; max-height: 60px; }
        .company-info { text-align: right; font-size: 7.5pt; color: #636E72; }
        .company-name-bold { font-size: 13pt; font-weight: bold; color: #000; }

        .receipt-card {
            margin: 10px 0;
            padding: 15px 20px;
            border: 1px solid #F0F0F0;
            background-color: #FCFBFA;
        }

        .hero-amount {
            font-size: 26pt;
            font-weight: bold;
            color: #A68636;
            text-align: center;
            margin: 15px 0;
            border-top: 1px solid #EBE7E1;
            border-bottom: 1px solid #EBE7E1;
            padding: 10px 0;
        }

        .detail-group { margin-bottom: 15px; border-bottom: 1px solid #F5F5F5; padding-bottom: 8px; }
        .label {
            display: block;
            font-size: 7.5pt;
            font-weight: bold;
            color: #A68636;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .value { font-size: 11pt; color: #2D3436; font-weight: bold; }

        .footer-fixed {
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid #F0F0F0;
            padding-top: 10px;
        }

        .signature-area { text-align: center; width: 180px; }
        .sig-line { border-top: 1px solid #2D3436; margin-top: 10px; padding-top: 4px; font-size: 7pt; font-weight: bold; text-transform: uppercase; }

        @php
            $rupeeSvg = "data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzIgMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTI0IDhoLTQuMDNhMSAxIDAgMCAwLTEtMWg1LjAzYTEgMSAwIDAgMCAwLTJIOWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDVsLTcgOGgzYTEgMSAwIDAgMCAwIDJsOS0xMGExIDEtMCAwIDAgMC0yaC01di0zaDVhMSAxIDAgMCAwIDAtM2gtNXYtM2g0YTEgMSAwIDAgMCAwLTJ6IiBmaWxsPSIjQTY4NjM2Ii8+PC9zdmc+";
        @endphp
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
                    <div class="company-name-bold">{{ $companyName }}</div>
                @endif
            </td>
            <td class="company-info">
                <div class="company-name-bold">{{ $companyName }}</div>
                <div>{{ $companyAddress }}</div>
                <div>{{ $companyEmail }} | {{ $companyPhone }}</div>
                <div>GSTIN: {{ $companyRegNo }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-header">
        <div class="doc-title">Payment Acknowledgment</div>
    </div>

    <!-- Meta Summary Line -->
    <table style="font-size: 8.5pt; color: #444; margin-bottom: 5px;">
        <tr>
            <td>Receipt Serial: <strong>{{ $receiptSerial }}</strong></td>
            <td style="text-align: right;">Date: <strong>{{ $quotationDate }}</strong></td>
        </tr>
    </table>

    <!-- Receipt Information -->
    <div class="receipt-card">
        <div class="detail-group">
            <span class="label">Received From</span>
            <span class="value" style="font-size: 13pt;">{{ $lead->beneficiary_name }}</span>
        </div>

        <div class="hero-amount">
            <img src="{{ $rupeeSvg }}" class="rupee-icon" style="width:18pt; height:18pt;"> {{ number_format($receiptAmount, 2) }}
        </div>

        <div class="detail-group">
            <span class="label">Sum in Words</span>
            <div style="font-style: italic; color: #444; font-size: 9pt;">
                {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Rupees Only
            </div>
        </div>

        <div class="detail-group">
            <span class="label">Payment Description</span>
            <span class="value" style="font-size: 9.5pt; font-weight: normal;">
                Advance partial payment towards {{ $kw }} kW Solar Power Installation as per Quotation Ref: {{ $quotationSerial }}.
            </span>
        </div>

        <div style="font-size: 8pt; color: #27AE60; font-weight: bold;">
            &bull; Digital Transaction Confirmed
        </div>
    </div>

    <!-- Forced Footer -->
    <div class="footer-fixed">
        <table>
            <tr>
                <td style="vertical-align: bottom;">
                    <div style="border-left: 2px solid #A68636; padding-left: 10px; font-size: 7.5pt; color: #636E72;">
                        <strong>System Generated Voucher</strong><br>
                        Authenticated via {{ $companyName }} billing hub. <br>
                        Funds subject to bank realization.
                    </div>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <div class="signature-area">
                        @if($sigBase64)
                            <img src="{{ $sigBase64 }}" style="max-height: 42px; margin-bottom: 3px;">
                        @else
                            <div style="height: 42px;"></div>
                        @endif
                        <div class="sig-line">Authorized Signatory</div>
                        <div style="font-size: 6pt; color: #aaa;">Digitally Signed Document</div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="text-align: center; font-size: 7pt; color: #aaa; margin-top: 10px;">
            &copy; {{ date('Y') }} {{ $companyName }}. Thank you for choosing renewable energy.
        </div>
    </div>

</body>
</html>