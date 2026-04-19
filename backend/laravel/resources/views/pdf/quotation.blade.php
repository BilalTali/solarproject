<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pro Forma Quotation - {{ $quotationSerial }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.8cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #2D3436;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
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
        
        /* World-Class Compact Heading */
        .doc-header {
            text-align: center;
            margin: 10px 0 15px 0;
            border-top: 1px solid #DFE6E9;
            border-bottom: 2px solid #2D3436;
            padding: 8px 0;
        }
        .doc-title {
            font-size: 18pt;
            text-transform: uppercase;
            letter-spacing: 6px;
            color: #2D3436;
            font-weight: bold;
        }

        .header-table td { vertical-align: top; }
        .logo { max-width: 150px; max-height: 65px; }
        .company-info { text-align: right; font-size: 7.5pt; color: #636E72; }
        .company-name-bold { font-size: 14pt; font-weight: bold; color: #000; margin-bottom: 2px; }

        .section-header {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #A68636;
            border-bottom: 1px solid #F0F0F0;
            padding-bottom: 3px;
            margin-top: 15px;
            margin-bottom: 6px;
        }

        .meta-table td { font-size: 8pt; padding: 1px 0; }
        .meta-label { color: #636E72; width: 80px; }
        .meta-value { font-weight: bold; }

        .items-table th {
            border-bottom: 1px solid #2D3436;
            text-align: left;
            padding: 6px 8px;
            font-size: 7pt;
            text-transform: uppercase;
            background-color: #FAFAFA;
        }
        .items-table td { padding: 6px 8px; border-bottom: 1px solid #DFE6E9; vertical-align: top; }
        
        .summary-box { background-color: #FCFBFA; padding: 10px; border: 1px solid #F2EEE9; width: 250px; margin-left: auto; }
        .total-label { font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        .total-value { font-size: 12pt; font-weight: bold; color: #A68636; }

        .bank-box {
            font-size: 7.5pt;
            line-height: 1.5;
            color: #444;
            border-left: 2px solid #A68636;
            padding-left: 10px;
        }
        .bank-title { color: #2D3436; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; display: block; }

        .footer-fixed {
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid #F0F0F0;
            padding-top: 10px;
        }

        .signature-area { text-align: center; width: 180px; }
        .sig-line { border-top: 1px solid #2D3436; margin-top: 8px; padding-top: 4px; font-size: 7pt; font-weight: bold; text-transform: uppercase; }

        .amount-words { font-style: italic; color: #636E72; font-size: 7.5pt; margin-bottom: 15px;}

        @php
            $rupeeSvg = "data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzIgMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTI0IDhoLTQuMDNhMSAxIDAgMCAwLTEtMWg1LjAzYTEgMSAwIDAgMCAwLTJIOWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDV2M2gtNWExIDEtMCAwIDAgMCAyaDVsLTcgOGgzYTEgMSAwIDAgMCAwIDJsOS0xMGExIDEtMCAwIDAgMC0yaC01di0zaDVhMSAxIDAgMCAwIDAtM2gtNXYtM2g0YTEgMSAwIDAgMCAwLTJ6IiBmaWxsPSIjNDQ0Ii8+PC9zdmc+";
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
                <div>{{ $companyAffiliated }}</div>
                <div>Reg: {{ $companyRegNo }}</div>
                <div>{{ $companyAddress }}</div>
                <div>{{ $companyEmail }} | {{ $companyPhone }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-header">
        <div class="doc-title">Pro Forma Quotation</div>
    </div>

    <!-- Client / Meta Info Row -->
    <table>
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="section-header">Client Recipient</div>
                <div style="font-size: 11pt; font-weight: bold;">{{ $lead->beneficiary_name }}</div>
                <div style="margin-top: 3px; color: #444;">
                    {{ $address }}<br>
                    Contact: {{ $lead->beneficiary_mobile }}<br>
                    {{ $kw }} kW Solar Power Installation
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="section-header" style="text-align: right;">Document ID</div>
                <table class="meta-table" style="width: auto; margin-left: auto;">
                    <tr><td class="meta-label">Quote Serial</td><td class="meta-value">{{ $quotationSerial }}</td></tr>
                    <tr><td class="meta-label">Date Issued</td><td class="meta-value">{{ $quotationDate }}</td></tr>
                    <tr><td class="meta-label">Validity</td><td class="meta-value">30 Days</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <div class="section-header">Technical Specifications & Investment</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Scope of Supply</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 20%; text-align: right;">Rate</th>
                <th style="width: 20%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($billingItems as $it)
            <tr>
                <td>
                    <div style="font-weight: bold;">{{ $it['description'] }}</div>
                    <div style="font-size: 7pt; color: #666;">Make: {{ $it['make'] ?? 'Standard' }}</div>
                </td>
                <td style="text-align: center;">01 Set</td>
                <td style="text-align: right;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($it['rate'], 2) }}</td>
                <td style="text-align: right;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($it['rate'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Box Row -->
    <table style="margin-top: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="amount-words">
                    <strong>Amount in words:</strong><br>
                    {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only
                </div>
            </td>
            <td style="width: 50%;">
                <table class="summary-box">
                    <tr>
                        <td style="font-size: 8pt; color: #636E72;">Base Value</td>
                        <td style="text-align: right; font-weight: bold;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($baseAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 8pt; color: #636E72;">GST ({{ $gstPercentage }}%)</td>
                        <td style="text-align: right; font-weight: bold;"><img src="{{ $rupeeSvg }}" class="rupee-icon"> {{ number_format($gstAmount, 2) }}</td>
                    </tr>
                    <tr style="height: 5px;"><td></td><td></td></tr>
                    <tr>
                        <td class="total-label">Total Amount</td>
                        <td style="text-align: right;" class="total-value"><img src="{{ $rupeeSvg }}" class="rupee-icon" style="width:10pt; height:10pt;"> {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Fixed Footer Area -->
    <div class="footer-fixed">
        <table>
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="bank-box">
                        <span class="bank-title">Bank Details</span>
                        {{ $bankAccountName }} &bull; {{ $bankBranch }}<br>
                        Account: <strong>{{ $bankAccountNumber }}</strong> &bull; IFSC: <strong>{{ $bankIfsc }}</strong>
                    </div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: top;">
                    <div style="display: inline-block;">
                        <div class="signature-area">
                            @if($sigBase64)
                                <img src="{{ $sigBase64 }}" style="max-height: 40px; margin-bottom: 3px;">
                            @else
                                <div style="height: 40px;"></div>
                            @endif
                            <div class="sig-line">Authorized Signatory</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="text-align: center; font-size: 7pt; color: #aaa; margin-top: 10px;">
            This is an electronically generated Pro Forma document. &copy; {{ date('Y') }} {{ $companyName }}
        </div>
    </div>

</body>
</html>