<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pro Forma Quotation - {{ $quotationSerial }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 1.2cm;
        }
        body {
            font-family: 'Outfit', sans-serif;
            font-size: 9pt;
            color: #2D3436;
            margin: 0;
            padding: 0;
            line-height: 1.4;
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
            margin: 15px 0 25px 0;
            border-top: 1px solid #DFE6E9;
            border-bottom: 2.5px double #2D3436;
            padding: 10px 0;
        }
        .doc-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22pt;
            text-transform: uppercase;
            letter-spacing: 8px;
            color: #2D3436;
            font-weight: 600;
        }

        .header-table td { vertical-align: top; }
        .logo { max-width: 170px; max-height: 75px; }
        .company-info { text-align: right; font-size: 8pt; color: #636E72; }
        .company-name-bold { font-size: 16pt; font-weight: 600; color: #000; margin-bottom: 2px; }

        .section-header {
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #A68636;
            border-bottom: 1px solid #F0F0F0;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .meta-table td { font-size: 8.5pt; padding: 2px 0; }
        .meta-label { color: #636E72; width: 90px; }
        .meta-value { font-weight: 600; }

        .items-table th {
            border-bottom: 1px solid #2D3436;
            text-align: left;
            padding: 10px 8px;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: #FAFAFA;
        }
        .items-table td { padding: 10px 8px; border-bottom: 1px solid #DFE6E9; vertical-align: top; }
        
        .summary-box { background-color: #FCFBFA; padding: 15px; border: 1px solid #F2EEE9; }
        .total-label { font-size: 11pt; font-weight: 600; text-transform: uppercase; }
        .total-value { font-size: 14pt; font-weight: 600; color: #A68636; font-family: 'Cormorant Garamond', serif; }

        .bank-box {
            font-size: 7.5pt;
            line-height: 1.6;
            color: #636E72;
            border-left: 2px solid #A68636;
            padding-left: 12px;
        }
        .bank-title { color: #2D3436; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; display: block; }

        .signature-area { text-align: center; width: 200px; }
        .sig-line { border-top: 1px solid #2D3436; margin-top: 10px; padding-top: 5px; font-size: 7.5pt; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }

        .footer-note {
            text-align: center;
            font-size: 7pt;
            color: #B2BEC3;
            margin-top: 35px;
            border-top: 1px solid #F0F0F0;
            padding-top: 10px;
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
                <div style="font-style: italic; margin-bottom: 5px;">{{ $companyAffiliated }}</div>
                <div>Reg: {{ $companyRegNo }}</div>
                <div>{{ $companyAddress }}</div>
                <div>{{ $companyEmail }} | {{ $companyPhone }}</div>
            </td>
        </tr>
    </table>

    <!-- World Class Heading -->
    <div class="doc-title-container">
        <div class="doc-title">Pro Forma Quotation</div>
    </div>

    <!-- Info Grid -->
    <table>
        <tr>
            <td style="width: 60%; vertical-align: top; padding-right: 40px;">
                <div class="section-header">Client Recipient</div>
                <div style="font-size: 13pt; font-weight: 600;" class="serif">{{ $lead->beneficiary_name }}</div>
                <div style="margin-top: 4px; color: #636E72;">
                    {{ $address }}<br>
                    Contact: {{ $lead->beneficiary_mobile }}<br>
                    Project: <strong>{{ $kw }} kW Solar PV Power Plant</strong>
                </div>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <div class="section-header">Reference</div>
                <table class="meta-table">
                    <tr><td class="meta-label">Quote Serial</td><td class="meta-value">{{ $quotationSerial }}</td></tr>
                    <tr><td class="meta-label">Date Issued</td><td class="meta-value">{{ $quotationDate }}</td></tr>
                    <tr><td class="meta-label">Validity</td><td class="meta-value">30 Calendar Days</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Items -->
    <div class="section-header" style="margin-top: 30px;">Investment Specifications</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description / Scope of Supply</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 20%; text-align: right;">Rate (<span class="currency">&#8377;</span>)</th>
                <th style="width: 20%; text-align: right;">Amount (<span class="currency">&#8377;</span>)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($billingItems as $it)
            <tr>
                <td>
                    <span style="font-weight: 600;">{{ $it['description'] }}</span><br>
                    <span style="font-size: 7.5pt; color: #636E72; font-style: italic;">Preferred Make: {{ $it['make'] ?? 'Standard IEC Certified' }}</span>
                </td>
                <td style="text-align: center;">01 Unit</td>
                <td style="text-align: right;"><span class="currency">&#8377;</span> {{ number_format($it['rate'], 2) }}</td>
                <td style="text-align: right;"><span class="currency">&#8377;</span> {{ number_format($it['rate'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Summary -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 30px;">
                <div style="font-size: 8pt; color: #636E72; font-style: italic;">
                    <strong>Amount in words:</strong><br>
                    {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only
                </div>
            </td>
            <td style="width: 50%;">
                <table class="summary-box">
                    <tr>
                        <td style="font-size: 8.5pt; color: #636E72;">Taxable Base Value</td>
                        <td style="text-align: right; font-weight: 600;"><span class="currency">&#8377;</span> {{ number_format($baseAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 8.5pt; color: #636E72;">GST ({{ $gstPercentage }}%) <small>(CGST + SGST)</small></td>
                        <td style="text-align: right; font-weight: 600;"><span class="currency">&#8377;</span> {{ number_format($gstAmount, 2) }}</td>
                    </tr>
                    <tr style="height: 10px;"><td></td><td></td></tr>
                    <tr>
                        <td class="total-label">Total Investment</td>
                        <td style="text-align: right;" class="total-value"><span class="currency">&#8377;</span> {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Signature & Footer -->
    <table style="margin-top: 40px; border-top: 1px solid #DFE6E9; padding-top: 20px;">
        <tr>
            <td style="width: 60%;">
                <div class="bank-box">
                    <span class="bank-title">Remittance Information</span>
                    Beneficiary: {{ $bankAccountName }}<br>
                    Bank: {{ $bankBranch }}<br>
                    Account: <strong>{{ $bankAccountNumber }}</strong><br>
                    IFSC: <strong>{{ $bankIfsc }}</strong>
                </div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div style="display: inline-block;">
                    <div class="signature-area">
                        @if($sigBase64)
                            <img src="{{ $sigBase64 }}" style="max-height: 45px; margin-bottom: 5px;">
                        @else
                            <div style="height: 45px;"></div>
                        @endif
                        <div class="sig-line">Authorized Signatory</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        This document is an electronically generated Pro Forma Quotation and does not require a physical stamp.<br>
        &copy; {{ date('Y') }} {{ $companyName }} &bull; All Rights Reserved.
    </div>

</body>
</html>