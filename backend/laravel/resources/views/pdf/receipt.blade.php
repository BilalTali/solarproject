<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $receiptSerial }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo {
            max-width: 160px;
            max-height: 70px;
        }
        .company-info {
            text-align: right;
            font-size: 8pt;
            color: #666;
        }
        .company-name {
            font-size: 15pt;
            font-weight: bold;
            color: #000;
        }
        .doc-type-box {
            background-color: #f8f8f8;
            border: 1px solid #eee;
            margin: 20px 0;
            padding: 10px;
            text-align: center;
        }
        .doc-type-text {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #444;
        }
        .meta-table {
            margin-bottom: 30px;
            font-size: 9pt;
        }
        .receipt-body {
            margin: 15px 0;
            padding: 20px;
            border: 1px solid #f0f0f0;
            background-color: #fafafa;
        }
        .amount-highlight {
            font-size: 22pt;
            font-weight: bold;
            color: #A68636;
            margin: 15px 0;
            text-align: center;
            border-top: 1px dashed #ddd;
            border-bottom: 1px dashed #ddd;
            padding: 15px 0;
        }
        .detail-row {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .label {
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 5px;
        }
        .value {
            font-size: 11pt;
            color: #222;
        }
        .footer-table {
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
            width: 220px;
            margin-left: auto;
        }
        .sig-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
            font-size: 8.5pt;
        }
        .amount-words {
            font-style: italic;
            color: #777;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
                @else
                    <div class="company-name">{{ $companyName }}</div>
                @endif
            </td>
            <td class="company-info">
                <div class="company-name">{{ $companyName }}</div>
                <div>{{ $companyAddress }}</div>
                <div>{{ $companyEmail }} | {{ $companyPhone }}</div>
                <div>GSTIN: {{ $companyRegNo }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-type-box">
        <span class="doc-type-text">Payment Acknowledgment Receipt</span>
    </div>

    <!-- Meta Info -->
    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Receipt No:</strong> {{ $receiptSerial }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Date:</strong> {{ $quotationDate }}
            </td>
        </tr>
    </table>

    <!-- Receipt Centerpiece -->
    <div class="receipt-body">
        <div class="detail-row">
            <span class="label">Received From</span>
            <span class="value" style="font-size: 14pt; font-weight: bold;">{{ $lead->beneficiary_name }}</span>
        </div>

        <div class="amount-highlight">
            ₹ {{ number_format($receiptAmount, 2) }}
        </div>

        <div class="detail-row" style="border-bottom: none;">
            <span class="label">Amount in Words</span>
            <span class="amount-words">
                {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Rupees Only
            </td>
        </div>

        <div class="detail-row">
            <span class="label">Being Payment Towards</span>
            <span class="value">
                Advance consideration for {{ $kw }} kW Solar Power Installation as per Quotation REF: {{ $quotationSerial }}.
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Payment Mode</span>
            <span class="value">Digital/Bank Transfer</span>
        </div>
    </div>

    <!-- Verification & Footer -->
    <table class="footer-table">
        <tr>
            <td style="vertical-align: bottom; font-size: 8pt; color: #888;">
                <div style="border: 1px solid #eee; padding: 10px; width: 250px; background-color: #fff;">
                    <strong>System Verified Receipt</strong><br>
                    Transaction ID: {{ substr(md5($receiptSerial . $quotationDate), 0, 12) }}<br>
                    Status: <span style="color: green; font-weight: bold;">Confirmed</span>
                </div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div class="signature-box">
                    @if($sigBase64)
                        <img src="{{ $sigBase64 }}" style="max-height: 40px; margin-bottom: 5px;">
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <div class="sig-line">Authorized Signatory</div>
                    <div style="font-size: 7pt; color: #999; margin-top: 3px;">Digitally Authenticated</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; font-size: 7.5pt; color: #aaa; margin-top: 60px; border-top: 1px solid #eee; padding-top: 10px;">
        &copy; {{ date('Y') }} {{ $companyName }}. Thank you for choosing a sustainable future.<br>
        This is an official computer-generated receipt.
    </div>

</body>
</html>