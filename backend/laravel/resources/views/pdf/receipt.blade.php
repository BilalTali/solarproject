<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $receiptSerial }}</title>
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
            background-color: #F4F4F4;
            font-family: 'Outfit', sans-serif;
            color: #1A1A1A;
            -webkit-font-smoothing: antialiased;
        }

        :root {
            --gold: #C9A84C;
            --gold-dark: #A68636;
            --gold-light: #E5D5A7;
            --dark: #0D0D0D;
            --mid: #4A4540;
            --soft: #8E8881;
            --border: #E8E2D9;
            --bg-page: #FFFFFF;
            --bg-accent: #FAF9F6;
        }

        /* ── THEME WRAPPER ── */
        .a4-container {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: var(--bg-page);
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        /* ── DECORATIVE ELEMENTS ── */
        .side-accent {
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 6px;
            background: linear-gradient(to bottom, var(--gold), var(--gold-dark));
            z-index: 100;
        }

        .header-bg-shape {
            position: absolute;
            top: -50mm; right: -50mm;
            width: 150mm; height: 150mm;
            background: radial-gradient(circle, rgba(201, 168, 76, 0.05) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            z-index: 1;
        }

        /* ── MAIN LAYOUT ── */
        .content {
            position: relative;
            z-index: 10;
            padding: 12mm 15mm 12mm 20mm; /* Extra left for accent */
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP NAV / META ── */
        .top-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1.5px solid var(--dark);
            padding-bottom: 12px;
        }

        .doc-type {
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--gold-dark);
        }

        .doc-id {
            font-size: 9pt;
            font-weight: 500;
            color: var(--soft);
        }

        /* ── HEADER ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 65%;
        }

        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 35%;
            text-align: right;
        }

        .logo-box {
            margin-bottom: 15px;
        }

        .logo-box img {
            max-height: 20mm;
            max-width: 50mm;
        }

        .company-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26pt;
            font-weight: 700;
            color: var(--dark);
            line-height: 0.9;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: -0.02em;
        }

        .company-contact {
            font-size: 9pt;
            color: var(--soft);
            line-height: 1.5;
        }

        .receipt-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24pt;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .meta-grid {
            border-top: 1px solid var(--border);
            padding-top: 10px;
        }

        .meta-item {
            font-size: 9pt;
            margin-bottom: 4px;
            color: var(--soft);
        }

        .meta-item b {
            color: var(--dark);
            font-weight: 600;
            margin-left: 5px;
        }

        /* ── HERO AMOUNT BAND ── */
        .hero-band {
            background: var(--dark);
            color: #FFF;
            padding: 30px;
            border-radius: 4px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hero-band::after {
            content: "";
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 30%;
            background: linear-gradient(to left, rgba(201,168,76,0.1), transparent);
        }

        .hero-label {
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .hero-val {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36pt;
            font-weight: 700;
            color: var(--gold-light);
            line-height: 1;
        }

        .hero-badge {
            text-align: right;
        }

        .badge-top { font-size: 8pt; color: var(--soft); font-weight: 600; margin-bottom: 5px; }
        .badge-num { font-size: 14pt; color: #FFF; font-weight: 500; font-family: 'Cormorant Garamond', serif; }

        /* ── DETAILS ── */
        .details-list {
            flex-grow: 1;
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 20px 0;
            border-bottom: 1px solid var(--border);
        }

        .detail-key {
            width: 180px;
            font-size: 8.5pt;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--soft);
            padding-top: 4px;
        }

        .detail-val {
            flex-grow: 1;
            font-size: 12pt;
            font-weight: 500;
            color: var(--dark);
            line-height: 1.4;
        }

        .detail-val.hero {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18pt;
            font-weight: 700;
        }

        .detail-val.words {
            font-style: italic;
            color: var(--mid);
            text-transform: capitalize;
        }

        /* ── FOOTER ── */
        .bottom-sections {
            display: table;
            width: 100%;
            margin-top: 40px;
            padding-top: 30px;
        }

        .bank-details {
            display: table-cell;
            width: 60%;
            vertical-align: bottom;
        }

        .signature-box {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: bottom;
        }

        .sig-img {
            max-height: 50px;
            margin-bottom: 5px;
            display: block;
            margin-left: auto;
        }

        .sig-line {
            width: 60mm;
            height: 1.5px;
            background: var(--dark);
            margin-left: auto;
            margin-top: 10px;
        }

        .sig-text {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-top: 8px;
            color: var(--dark);
        }

        .footer-note {
            margin-top: 40px;
            text-align: center;
            font-size: 8pt;
            color: var(--soft);
            letter-spacing: 0.05em;
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }

    </style>
</head>
<body>

<div class="a4-container">
    <div class="side-accent"></div>
    <div class="header-bg-shape"></div>

    <div class="content">
        <!-- TOP META -->
        <div class="top-meta">
            <div class="doc-type">Official Payment Receipt</div>
            <div class="doc-id">TRANS-ID: {{ $receiptSerial }} / {{ $quotationDate }}</div>
        </div>

        <!-- HEADER -->
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Company Logo">
                    @endif
                </div>
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-contact">
                    {{ $companyAddress }} <br>
                    <b>Email:</b> {{ $companyEmail }} &nbsp; <b>Phone:</b> {{ $companyPhone }} <br>
                    <b>GSTIN:</b> {{ $companyRegNo }}
                </div>
            </div>
            <div class="header-right">
                <div class="receipt-title">Receipt</div>
                <div class="meta-grid">
                    <div class="meta-item">Date: <b>{{ $quotationDate }}</b></div>
                    <div class="meta-item">Ref: <b>{{ $receiptSerial }}</b></div>
                </div>
            </div>
        </div>

        <!-- HERO BAND -->
        <div class="hero-band">
            <div>
                <div class="hero-label">Amount Received</div>
                <div class="hero-val">₹ {{ number_format($receiptAmount, 2) }}</div>
            </div>
            <div class="hero-badge">
                <div class="badge-top">Receipt Voucher</div>
                <div class="badge-num">#{{ $receiptSerial }}</div>
            </div>
        </div>

        <!-- DETAILS -->
        <div class="details-list">
            <div class="detail-row">
                <div class="detail-key">Received From</div>
                <div class="detail-val hero">{{ $lead->beneficiary_name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Sum of Rupees</div>
                <div class="detail-val words">
                    {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Rupees Only
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-key">On Account Of</div>
                <div class="detail-val">
                    Advance payment for 
                    @foreach($billingItems as $it)
                        {{ $it['description'] }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Payment Mode</div>
                <div class="detail-val">Bank Transfer / Online Payment</div>
            </div>
        </div>

        <!-- BOTTOM -->
        <div class="bottom-sections">
            <div class="bank-details">
                <div style="font-size: 8.5pt; color: var(--soft); line-height: 1.6;">
                    * This is a computer generated receipt. <br>
                    * All payments are subject to bank realization.
                </div>
            </div>
            <div class="signature-box">
                @if($sigBase64)
                    <img src="{{ $sigBase64 }}" class="sig-img" alt="Authorized Signature">
                @else
                    <div style="height:50px"></div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-text">Authorized Signatory</div>
            </div>
        </div>

        <div class="footer-note">
            Thank you for your business. For any queries, please contact {{ $companyEmail }}.
        </div>
    </div>
</div>

</body>
</html>