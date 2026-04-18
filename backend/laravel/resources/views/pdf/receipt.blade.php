<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 14px;
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
        .title-container {
            text-align: center;
            margin: 30px 0;
        }
        .title {
            display: inline-block;
            background-color: #333;
            color: #fff;
            padding: 8px 30px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
            line-height: 2;
        }
        .info-table td {
            padding: 5px 0;
            vertical-align: bottom;
        }
        .underline-text {
            border-bottom: 1px dotted #666;
            width: 95%;
            display: inline-block;
            font-style: italic;
        }
        .amount-box {
            display: inline-block;
            border: 2px solid #333;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer-section {
            margin-top: 60px;
            width: 100%;
        }
        .amount-container {
            float: left;
            width: 50%;
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

    <div class="title-container">
        <div class="title">PAYMENT RECEIPT</div>
    </div>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%"><strong>Receipt No:</strong> {{ $receiptSerial }}</td>
            <td style="width: 50%; text-align: right;"><strong>Date:</strong> {{ $quotationDate }}</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 15%">Received from</td>
            <td style="width: 85%"><span class="underline-text">{{ $lead->beneficiary_name }}</span></td>
        </tr>
        <tr>
            <td>The sum of Rupees</td>
            <td><span class="underline-text">{{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Only</span></td>
        </tr>
        <tr>
            <td>For the payment of</td>
            <td>
                <span class="underline-text">
                    Advance for 
                    @foreach($billingItems as $it)
                        {{ $it['description'] }} ({{ $it['make'] }}){{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </span>
            </td>
        </tr>
        <tr>
            <td>By Cash / Cheque / DD</td>
            <td><span class="underline-text">Online / Bank Transfer</span></td>
        </tr>
    </table>

    <div class="footer-section clearfix">
        <div class="amount-container">
            <div class="amount-box">
                RS {{ number_format($receiptAmount, 2) }}/-
            </div>
            <div style="margin-top: 15px; font-size: 12px; color: #666;">
                <i>* Subject to realization of cheque</i>
            </div>
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
