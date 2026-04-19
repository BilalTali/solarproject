<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pro Forma Quotation - {{ $quotationSerial }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #2D3436;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .serif { font-family: 'Times New Roman', Times, serif; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .rupee-icon {
            width: 8pt;
            height: 8pt;
            vertical-align: middle;
            margin-right: -1px;
        }
        
        /* Branding Header */
        .brand-container {
            border-bottom: 3px solid #f4f4f4;
            padding-bottom: 12px;
            margin-bottom: 20px;
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
            margin: 25px 0;
            padding: 5px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        .doc-title {
            font-family: 'Times New Roman', serif;
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 8px;
            color: #444;
            font-weight: normal;
        }

        .section-header {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #A68636;
            margin-bottom: 8px;
        }

        .items-table th {
            border-bottom: 2px solid #2D3436;
            text-align: left;
            padding: 10px 8px;
            font-size: 7.5pt;
            text-transform: uppercase;
            background-color: #FAFAFA;
        }
        .items-table td { padding: 12px 8px; border-bottom: 1px solid #F0F0F0; vertical-align: top; }
        
        .summary-wrapper {
            margin-top: 30px;
        }
        .total-box {
            background-color: #FCFBFA;
            padding: 15px;
            border: 1px solid #F2EEE9;
            width: 250px;
            margin-left: auto;
        }
        .total-label { font-size: 11pt; font-weight: bold; }
        .total-value { font-size: 13pt; font-weight: bold; color: #A68636; }

        .bank-details {
            margin-top: 40px;
            font-size: 8pt;
            color: #444;
            border-left: 3px solid #A68636;
            padding-left: 15px;
        }

        .signature-section {
            margin-top: 60px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            width: 200px;
        }
        .sig-line {
            border-top: 1px solid #2D3436;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        @php
            $rupeeSvg = "data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzIgMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTI0IDhoLTQuMDNhMSAxIDAgMCAwLTEtMWg1LjAzYTEgMSAwIDAgMCAwLTJIOWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDVsLTcgOGgzYTEgMSAwIDAgMCAwIDJsOS0xMGExIDEtMCAwIDAgMC0yaC01di0zaDVhMSAxIDAgMCAwIDAtM2gtNXYtM2g0YTEgMSAwIDAgMCAwLTJ6IiBmaWxsPSIjNDQ0Ii8+PC9zdmc+";
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
                    <div class="company-tagline">{{ $companyAffiliated }}</div>
                </td>
                <td class="company-contact">
                    Reg: {{ $companyRegNo }}<br>
                    {{ $companyAddress }}<br>
                    {{ $companyEmail }}<br>
                    Phone: {{ $companyPhone }}
                </td>
            </tr>
        </table>
    </div>

    <!-- World Class Title -->
    <div class="doc-title-bar">
        <div class="doc-title">Pro Forma Quotation</div>
    </div>

    <!-- Recipient & Meta -->
    <table style="margin-bottom: 30px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="section-header">Prepared For</div>
                <div style="font-size: 13pt; font-weight: bold; color: #000;" class="serif">{{ $lead->beneficiary_name }}</div>
                <div style="margin-top: 5px; color: #636E72;">
                    {{ $address }}<br>
                    Contact: {{ $lead->beneficiary_mobile }}<br>
                    Capacity: <strong>{{ $kw }} kW</strong> Solar Photovoltaic Plant
                </div>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <div class="section-header">Quotation Info</div>
                <table style="font-size: 8.5pt;">
                    <tr><td style="color: #636E72; width: 80px;">Ref No:</td><td style="font-weight: bold;">{{ $quotationSerial }}</td></tr>
                    <tr><td style="color: #636E72;">Date:</td><td style="font-weight: bold;">{{ $quotationDate }}</td></tr>
                    <tr><td style="color: #636E72;">Validity:</td><td style="font-weight: bold;">30 Calendar Days</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <div class="section-header">Technical Scope & Supply</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description of Goods & Services</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 20%; text-align: right;">Rate</th>
                <th style="width: 20%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($billingItems as $it)
            <tr>
                <td>
                    <div style="font-weight: bold; font-size: 10pt;">{{ $it['description'] }}</div>
                    <div style="font-size: 7.5pt; color: #636E72; font-style: italic;">Make/Brand: {{ $it['make'] ?? 'MNRE Approved' }}</div>
                </td>
                <td style="text-align: center;">01 Unit</td>
                <td style="text-align: right;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($it['rate'], 2) }}</td>
                <td style="text-align: right;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($it['rate'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Summary -->
    <div class="summary-wrapper">
        <table>
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <div style="font-size: 8pt; color: #636E72; font-style: italic;">
                        <strong>Total Amount in Words:</strong><br>
                        {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only
                    </div>
                </td>
                <td style="width: 45%;">
                    <table class="total-box">
                        <tr>
                            <td style="font-size: 8pt; color: #636E72;">Subtotal (Excl. Tax)</td>
                            <td style="text-align: right; font-weight: bold;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($baseAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 8pt; color: #636E72;">GST Charge ({{ $gstPercentage }}%)</td>
                            <td style="text-align: right; font-weight: bold;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($gstAmount, 2) }}</td>
                        </tr>
                        <tr style="height: 10px;"><td></td><td></td></tr>
                        <tr>
                            <td class="total-label">Grand Total</td>
                            <td style="text-align: right;" class="total-value"><img src="{{ $rupeeSvg }}" class="rupee-icon" style="width:10pt; height:10pt;"> {{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Natural Balanced Footer Area -->
    <div style="margin-top: auto;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="bank-details">
                        <strong style="color: #2D3436; text-transform: uppercase; font-size: 7.5pt;">Standard Bank Remittance</strong><br>
                        Beneficiary: {{ $bankAccountName }}<br>
                        Bank: {{ $bankBranch }} | Account: <strong>{{ $bankAccountNumber }}</strong><br>
                        IFSC: <strong>{{ $bankIfsc }}</strong>
                    </div>
                </td>
                <td style="width: 40%; vertical-align: bottom;">
                    <div class="signature-section">
                        <div class="signature-box">
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
    </div>

    <div style="text-align: center; font-size: 7.5pt; color: #aaa; margin-top: 40px; border-top: 1px solid #eee; padding-top: 15px;">
        This is an official electronically generated Quotation Page. &copy; {{ date('Y') }} {{ $companyName }} &bull; Save paper, go solar.
    </div>

</body>
</html>