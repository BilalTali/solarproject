<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pro Forma Quotation - {{ $quotationSerial }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── RESET & A4 CORE ── */
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
        @page {
            size: A4;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #F8F9FA;
            font-family: 'Outfit', sans-serif;
            color: #1A1A1A;
            -webkit-font-smoothing: antialiased;
        }

        :root {
            --gold: #C9A84C;
            --gold-dark: #A68636;
            --gold-light: #E5D5A7;
            --dark: #111111;
            --mid: #4A4540;
            --soft: #7A7A7A;
            --border: #EBEBEB;
            --bg-page: #FFFFFF;
            --bg-accent: #FCFBFA;
        }

        /* ── THEME WRAPPER ── */
        .a4-container {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: var(--bg-page);
            position: relative;
            overflow: hidden;
        }

        /* ── DECORATIVE ELEMENTS ── */
        .side-accent {
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 10px;
            background: linear-gradient(to bottom, var(--dark), var(--gold), var(--dark));
            z-index: 100;
        }

        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150pt;
            color: rgba(0,0,0,0.02);
            font-weight: 800;
            z-index: 1;
            white-space: nowrap;
            user-select: none;
            text-transform: uppercase;
        }

        .header-bg-shape {
            position: absolute;
            top: -20mm; right: -20mm;
            width: 120mm; height: 120mm;
            background: radial-gradient(circle, rgba(201, 168, 76, 0.08) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            z-index: 1;
        }

        /* ── MAIN LAYOUT ── */
        .content {
            position: relative;
            z-index: 10;
            padding: 15mm 15mm 15mm 25mm; 
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP NAV / META ── */
        .top-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--dark);
            padding-bottom: 12px;
        }

        .doc-type {
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: 0.4em;
            text-transform: uppercase;
            color: var(--dark);
        }

        .doc-id {
            font-size: 8.5pt;
            font-weight: 600;
            color: var(--soft);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── HEADER ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 35px;
        }

        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 40%;
            text-align: right;
        }

        .logo-box {
            margin-bottom: 18px;
        }

        .logo-box img {
            max-height: 22mm;
            max-width: 55mm;
            filter: grayscale(20%);
        }

        .company-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28pt;
            font-weight: 700;
            color: var(--dark);
            line-height: 0.85;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: -0.01em;
        }

        .company-tag {
            font-size: 9pt;
            color: var(--gold-dark);
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .company-contact {
            font-size: 9.5pt;
            color: var(--mid);
            line-height: 1.6;
            max-width: 95%;
        }

        .company-contact b { color: var(--dark); }

        /* ── QUOTATION INFO ── */
        .quotation-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26pt;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .meta-grid {
            border-top: 1px solid var(--border);
            padding-top: 15px;
        }

        .meta-item {
            font-size: 9.5pt;
            margin-bottom: 6px;
            color: var(--soft);
        }

        .meta-item b {
            color: var(--dark);
            font-weight: 700;
            margin-left: 8px;
        }

        /* ── CLIENT INFO ── */
        .billing-section {
            background: var(--bg-accent);
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 40px;
            border: 1px solid var(--border);
            display: table;
            width: 100%;
        }

        .bill-to {
            display: table-cell;
            width: 70%;
        }

        .qr-placeholder {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
        }

        .qr-box {
            width: 25mm;
            height: 25mm;
            border: 1px solid var(--border);
            display: inline-block;
            background: #FFF;
            padding: 4px;
        }
        
        .qr-inner {
            width: 100%;
            height: 100%;
            background: #FAFAFA;
            border: 0.5px dashed var(--soft);
        }

        .bill-label {
            font-size: 8pt;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--soft);
            margin-bottom: 10px;
        }

        .client-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18pt;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
        }

        .client-details {
            font-size: 10pt;
            color: var(--mid);
            line-height: 1.5;
        }

        /* ── TABLE ── */
        .table-wrap {
            flex-grow: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th {
            background: var(--dark);
            color: #FFF;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 15px 12px;
            text-align: left;
        }

        td {
            padding: 18px 12px;
            border-bottom: 1px solid var(--border);
            font-size: 10.5pt;
            vertical-align: top;
            color: var(--dark);
        }

        .col-desc { width: 45%; }
        .col-qty { width: 15%; text-align: center; font-weight: 600; }
        .col-rate { width: 20%; text-align: right; }
        .col-total { width: 20%; text-align: right; font-weight: 700; color: var(--dark); }

        .item-main { font-weight: 600; display: block; margin-bottom: 4px; }
        .item-make {
            display: block;
            font-size: 9pt;
            color: var(--soft);
            font-style: italic;
        }

        /* ── SUMMARY ── */
        .summary-block {
            display: table;
            width: 100%;
            margin-top: 25px;
            border-top: 2px solid var(--dark);
            padding-top: 20px;
        }

        .summary-left {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            padding-right: 50px;
        }

        .summary-right {
            display: table-cell;
            width: 45%;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .summary-row.total {
            border-bottom: none;
            background: var(--dark);
            color: #FFF;
            padding: 15px 12px;
            margin-top: 8px;
            border-radius: 4px;
        }

        .summary-lbl { font-size: 10pt; color: var(--soft); font-weight: 600; }
        .summary-val { font-size: 10.5pt; color: var(--dark); font-weight: 700; }
        
        .total-lbl { font-size: 12pt; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.1em; }
        .total-val { font-size: 16pt; font-weight: 700; color: #FFF; }

        .amount-words {
            font-size: 9pt;
            color: var(--mid);
            text-transform: capitalize;
            font-style: italic;
            background: #FFF;
            padding: 12px;
            border: 1px solid var(--border);
            border-left: 4px solid var(--gold);
            margin-top: 10px;
        }

        /* ── BOTTOM INFO ── */
        .bottom-sections {
            display: table;
            width: 100%;
            margin-top: 50px;
            padding-bottom: 20px;
        }

        .bank-details {
            display: table-cell;
            width: 60%;
        }

        .signature-box {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: bottom;
        }

        .bank-box {
            background: #FAF9F8;
            padding: 18px;
            border-radius: 6px;
            font-size: 9pt;
            line-height: 1.7;
            border: 1px solid var(--border);
            max-width: 90%;
        }

        .bank-box b { color: var(--gold-dark); text-transform: uppercase; display: block; margin-bottom: 8px; font-size: 8.5pt; letter-spacing: 0.12em; font-weight: 800; }

        .sig-container {
            display: inline-block;
            text-align: center;
        }

        .sig-img {
            max-height: 48px;
            margin-bottom: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .sig-line {
            width: 65mm;
            height: 2px;
            background: var(--dark);
            margin-bottom: 10px;
        }

        .sig-text {
            font-size: 9pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: var(--dark);
        }

        /* ── FOOTER ── */
        .footer-note {
            margin-top: auto;
            text-align: center;
            font-size: 8.5pt;
            color: var(--soft);
            letter-spacing: 0.08em;
            padding-top: 30px;
        }

    </style>
</head>
<body>

<div class="a4-container">
    <div class="side-accent"></div>
    <div class="watermark">{{ $companyName }}</div>
    <div class="header-bg-shape"></div>

    <div class="content">
        <!-- TOP META -->
        <div class="top-meta">
            <div class="doc-type">Quotation</div>
            <div class="doc-id">DOC #{{ $quotationSerial }} / {{ $quotationDate }}</div>
        </div>

        <!-- HEADER -->
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Brand Logo">
                    @endif
                </div>
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-tag">{{ $companyAffiliated }}</div>
                <div class="company-contact">
                    <b>REG:</b> {{ $companyRegNo }} <br>
                    <b>LOC:</b> {{ $companyAddress }} <br>
                    {{ $companyEmail }} &bull; {{ $companyPhone }}
                </div>
            </div>
            <div class="header-right">
                <div class="quotation-title">Pro Forma</div>
                <div class="meta-grid">
                    <div class="meta-item">Issue Date <b>{{ $quotationDate }}</b></div>
                    <div class="meta-item">Reference <b>{{ $quotationSerial }}</b></div>
                    <div class="meta-item">Valid Until <b>30 Days</b></div>
                </div>
            </div>
        </div>

        <!-- CLIENT INFO -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="bill-label">Recipient Details</div>
                <div class="client-name">{{ $lead->beneficiary_name }}</div>
                <div class="client-details">
                    {{ $address }} <br>
                    M: {{ $lead->beneficiary_mobile }} <br>
                    <b>Capacity:</b> {{ $kw }} kW Solar Rooftop Power Plant
                </div>
            </div>
            <div class="qr-placeholder">
                <div class="qr-box">
                    <div class="qr-inner"></div>
                </div>
                <div style="font-size: 7pt; color: var(--soft); margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;">Digital Verification</div>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-desc">Scope of Work & Specifications</th>
                        <th class="col-qty">Quantity</th>
                        <th class="col-rate">Unit Rate</th>
                        <th class="col-total">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($billingItems as $it)
                    <tr>
                        <td class="col-desc">
                            <span class="item-main">{{ $it['description'] }}</span>
                            <span class="item-make">Brand Preference: {{ $it['make'] ?? 'Tier-1 Standard' }}</span>
                        </td>
                        <td class="col-qty">01 System</td>
                        <td class="col-rate">₹ {{ number_format($it['rate'], 2) }}</td>
                        <td class="col-total">₹ {{ number_format($it['rate'], 2) }}</td>
                    </tr>
                    @endforeach
                    @for($i = count($billingItems); $i < 3; $i++)
                    <tr style="height: 48px;"><td colspan="4"></td></tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- SUMMARY -->
        <div class="summary-block">
            <div class="summary-left">
                <div class="bill-label">Capitalized Value (In Words)</div>
                <div class="amount-words">
                    {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only
                </div>
            </div>
            <div class="summary-right">
                <div class="summary-row">
                    <span class="summary-lbl">Taxable Base Amount</span>
                    <span class="summary-val">₹ {{ number_format($baseAmount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-lbl">GST ({{ $gstPercentage }}%) <small>(CGST + SGST)</small></span>
                    <span class="summary-val">₹ {{ number_format($gstAmount, 2) }}</span>
                </div>
                <div class="summary-row total">
                    <span class="total-lbl">Consolidated Total</span>
                    <span class="total-val">₹ {{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- BANK & SIGNATURE -->
        <div class="bottom-sections">
            <div class="bank-details">
                <div class="bank-box">
                    <b>Banking Credentials</b>
                    A/C Name: {{ $bankAccountName }} <br>
                    Bank/Branch: {{ $bankBranch }} <br>
                    A/C Number: <b>{{ $bankAccountNumber }}</b> <br>
                    IFSC: <b>{{ $bankIfsc }}</b>
                </div>
            </div>
            <div class="signature-box">
                <div class="sig-container">
                    @if($sigBase64)
                        <img src="{{ $sigBase64 }}" class="sig-img" alt="Sign">
                    @else
                        <div style="height:48px"></div>
                    @endif
                    <div class="sig-line"></div>
                    <div class="sig-text">Authorized Officer</div>
                </div>
            </div>
        </div>

        <div class="footer-note">
            &copy; {{ date('Y') }} {{ $companyName }}. This is an electronically generated document. <br>
            All installations are subject to MNRE technical guidelines and site feasibility.
        </div>
    </div>
</div>

</body>
</html>