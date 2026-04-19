<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pro Forma Quotation - {{ $quotationSerial }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo {
            max-width: 180px;
            max-height: 80px;
        }
        .company-info {
            text-align: right;
            font-size: 8pt;
            color: #666;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
        }
        .title-bar {
            background-color: #f4f4f4;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            margin: 20px 0;
            padding: 8px 10px;
            text-align: center;
        }
        .title-text {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .meta-table {
            margin-bottom: 20px;
        }
        .meta-label {
            font-weight: bold;
            color: #555;
            width: 100px;
        }
        .section-header {
            background-color: #fafafa;
            border-left: 3px solid #A68636;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .items-table th {
            background-color: #333;
            color: #fff;
            text-align: left;
            padding: 8px;
            font-size: 8pt;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .summary-table td {
            padding: 5px 8px;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 11pt;
        }
        .footer-table {
            margin-top: 40px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin-top: 50px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }
        .bank-box {
            border: 1px solid #eee;
            padding: 10px;
            font-size: 8pt;
            background-color: #fff;
            width: 300px;
        }
        .amount-words {
            font-style: italic;
            color: #777;
            font-size: 8pt;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
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
                <div>{{ $companyAffiliated }}</div>
                <div>Reg No: {{ $companyRegNo }}</div>
                <div>Address: {{ $companyAddress }}</div>
                <div>Email: {{ $companyEmail }} | Phone: {{ $companyPhone }}</div>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <div class="title-bar">
        <span class="title-text">Pro Forma Quotation</span>
    </div>

    <!-- Metadata & Recipient -->
    <table class="meta-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="section-header">Customer Details</div>
                <div style="font-size: 11pt; font-weight: bold;">{{ $lead->beneficiary_name }}</div>
                <div style="margin-top: 5px;">
                    {{ $address }}<br>
                    Contact: {{ $lead->beneficiary_mobile }}<br>
                    Solar Capacity: <strong>{{ $kw }} kW</strong> Solar PV System
                </div>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <table style="width: auto; margin-left: auto;">
                    <tr>
                        <td class="meta-label">Quote Ref:</td>
                        <td>{{ $quotationSerial }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Date:</td>
                        <td>{{ $quotationDate }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Validity:</td>
                        <td>30 Days</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Section -->
    <div class="section-header">Scope of Work & Technical Specifications</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 20%; text-align: right;">Rate</th>
                <th style="width: 20%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($billingItems as $it)
            <tr>
                <td>
                    <strong>{{ $it['description'] }}</strong><br>
                    <span style="font-size: 7.5pt; color: #888;">Make: {{ $it['make'] ?? 'Standard Tier-1' }}</span>
                </td>
                <td style="text-align: center;">1 Set</td>
                <td style="text-align: right;">₹ {{ number_format($it['rate'], 2) }}</td>
                <td style="text-align: right;">₹ {{ number_format($it['rate'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Section -->
    <table style="margin-top: 15px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="amount-words">
                    <strong>Total amount in words:</strong><br>
                    {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="summary-table">
                    <tr>
                        <td>Taxable Base Value</td>
                        <td style="text-align: right;">₹ {{ number_format($baseAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>GST ({{ $gstPercentage }}%) <small>(CGST + SGST)</small></td>
                        <td style="text-align: right;">₹ {{ number_format($gstAmount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Amount Payable</td>
                        <td style="text-align: right; color: #A68636;">₹ {{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Bank & Signature Section -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%; vertical-align: bottom;">
                <div class="bank-box">
                    <strong style="color: #A68636; text-transform: uppercase; font-size: 7.5pt; display: block; margin-bottom: 5px;">Bank Details for Remittance</strong>
                    A/C Name: {{ $bankAccountName }}<br>
                    Bank: {{ $bankBranch }}<br>
                    A/C No: <strong>{{ $bankAccountNumber }}</strong><br>
                    IFSC: <strong>{{ $bankIfsc }}</strong>
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: bottom;">
                <div style="display: inline-block; text-align: center;">
                    @if($sigBase64)
                        <img src="{{ $sigBase64 }}" style="max-height: 40px; margin-bottom: 5px;">
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <div class="signature-line">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; font-size: 7.5pt; color: #aaa; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
        &copy; {{ date('Y') }} {{ $companyName }}. This is a computer-generated Pro Forma document requiring no manual seal if signed electronically.
    </div>

</body>
</html>