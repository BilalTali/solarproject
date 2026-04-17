<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pro Forma Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 13px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        .header img.logo {
            position: absolute;
            left: 0;
            top: 0;
            max-width: 80px;
            max-height: 80px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 14px;
            margin-top: 5px;
        }
        .company-details {
            font-size: 12px;
            margin-top: 5px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 0;
        }
        .info-table td.strong {
            font-weight: bold;
            width: 120px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .main-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .footer-section {
            margin-top: 50px;
            width: 100%;
        }
        .bank-details {
            float: left;
            width: 50%;
            font-size: 12px;
            line-height: 1.5;
        }
        .bank-details strong {
            display: block;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .signature-box {
            float: right;
            width: 40%;
            text-align: center;
        }
        .signature-img {
            max-height: 60px;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-text {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>

    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
        @endif
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-address">{{ $companyAddress }}</div>
        <div class="company-details">
            Email: {{ $companyEmail }} &nbsp;|&nbsp; Mob: {{ $companyPhone }}<br>
            {{ $companyRegNo }}<br>
            {{ $companyAffiliated }}
        </div>
    </div>

    <div class="title">PRO FORMA QUOTATION</div>

    <table class="info-table">
        <tr>
            <td class="strong">Serial No:</td>
            <td>{{ $quotationSerial }}</td>
            <td class="strong" style="text-align: right">Date:</td>
            <td style="text-align: right">{{ $quotationDate }}</td>
        </tr>
        <tr>
            <td class="strong">To,</td>
            <td colspan="3">{{ $lead->beneficiary_name }}</td>
        </tr>
        <tr>
            <td class="strong">Address:</td>
            <td colspan="3">{{ $address }}</td>
        </tr>
        <tr>
            <td class="strong">Contact:</td>
            <td colspan="3">{{ $lead->beneficiary_mobile }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">S.No</th>
                <th width="45%">Description</th>
                <th width="10%">Qty</th>
                <th width="20%">Rate (RS)</th>
                <th width="20%">Amount (RS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <b>{{ $kw }} KW Solar Grid Tie System</b><br>
                    Make: {{ $make }}<br>
                    Item: {{ $item }}<br>
                    <i>Including Installation, Net Metering and all accessories</i>
                </td>
                <td class="text-center">1 Set</td>
                <td class="text-right">{{ number_format($baseAmount, 2) }}</td>
                <td class="text-right">{{ number_format($baseAmount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><b>Taxable Amount:</b></td>
                <td></td>
                <td class="text-right"><b>{{ number_format($baseAmount, 2) }}</b></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><b>Add CGST @ 2.5%:</b></td>
                <td></td>
                <td class="text-right">{{ number_format($gstAmount / 2, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><b>Add SGST @ 2.5%:</b></td>
                <td></td>
                <td class="text-right">{{ number_format($gstAmount / 2, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><b>Total Amount:</b></td>
                <td></td>
                <td class="text-right bg-gray"><b>{{ number_format($totalAmount, 2) }}</b></td>
            </tr>
        </tbody>
    </table>

    <p style="text-transform: capitalize"><b>Amount in Words:</b> {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only.</p>

    <div class="footer-section clearfix">
        <div class="bank-details">
            <strong>Bank Details</strong>
            Account Name: {{ $bankAccountName }}<br>
            A/c No: {{ $bankAccountNumber }}<br>
            IFSC Code: {{ $bankIfsc }}<br>
            Branch: {{ $bankBranch }}
        </div>
        
        <div class="signature-box">
            @if($sigBase64)
                <img src="{{ $sigBase64 }}" class="signature-img" alt="Signature">
            @else
                <div style="height: 60px;"></div>
            @endif
            <div class="signature-text">Authorized Signatory</div>
        </div>
    </div>

</body>
</html>
