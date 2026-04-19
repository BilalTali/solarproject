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
            font-size: 140pt;
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

        .company-contact {
            font-size: 9.5pt;
            color: var(--mid);
            line-height: 1.6;
        }

        .header-title {
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

        /* ── HERO BAND ── */
        .hero-band {
            background: var(--dark);
            color: #FFF;
            padding: 35px;
            border-radius: 6px;
            margin-bottom: 45px;
            position: relative;
            overflow: hidden;
            display: table;
            width: 100%;
        }

        .hero-left {
            display: table-cell;
            vertical-align: middle;
        }

        .hero-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .hero-label {
            font-size: 9pt;
            font-weight: 800;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 10px;
        }

        .hero-val {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42pt;
            font-weight: 700;
            color: var(--gold-light);
            line-height: 1;
        }

        .hero-badge-top { font-size: 8.5pt; color: var(--soft); font-weight: 700; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .hero-badge-num { font-size: 16pt; color: #FFF; font-weight: 600; font-family: 'Cormorant Garamond', serif; }

        /* ── DETAILS ── */
        .details-list {
            flex-grow: 1;
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 24px 0;
            border-bottom: 1px solid var(--border);
        }

        .detail-key {
            width: 200px;
            font-size: 9pt;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--soft);
            padding-top: 5px;
        }

        .detail-val {
            flex-grow: 1;
            font-size: 12.5pt;
            font-weight: 600;
            color: var(--dark);
            line-height: 1.5;
        }

        .detail-val.hero {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20pt;
            font-weight: 700;
        }

        .detail-val.words {
            font-style: italic;
            color: var(--mid);
            text-transform: capitalize;
            font-size: 11pt;
            background: var(--bg-accent);
            padding: 10px;
            border-radius: 4px;
            border: 1px solid var(--border);
            display: inline-block;
        }

        /* ── BOTTOM ── */
        .bottom-sections {
            display: table;
            width: 100%;
            margin-top: 50px;
            padding-bottom: 30px;
        }

        .verif-box {
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

        .qr-placeholder {
            width: 28mm;
            height: 28mm;
            border: 1px solid var(--border);
            background: #FFF;
            padding: 5px;
            margin-bottom: 10px;
        }
        
        .qr-inner {
            width: 100%;
            height: 100%;
            background: #FAFAFA;
            border: 0.5px dashed var(--soft);
        }

        .sig-container {
            display: inline-block;
            text-align: center;
        }

        .sig-img {
            max-height: 55px;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .sig-line {
            width: 70mm;
            height: 2px;
            background: var(--dark);
            margin-bottom: 12px;
        }

        .sig-text {
            font-size: 9pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--dark);
        }

        .footer-note {
            margin-top: 40px;
            text-align: center;
            font-size: 8.5pt;
            color: var(--soft);
            letter-spacing: 0.1em;
            border-top: 2px solid var(--dark);
            padding-top: 25px;
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
            <div class="doc-type">Payment Receipt</div>
            <div class="doc-id">TXN: {{ $receiptSerial }} / {{ $quotationDate }}</div>
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
                <div class="company-contact">
                    {{ $companyAddress }} <br>
                    {{ $companyEmail }} &bull; {{ $companyPhone }} <br>
                    <b>GSTIN:</b> {{ $companyRegNo }}
                </div>
            </div>
            <div class="header-right">
                <div class="header-title">Voucher</div>
                <div class="meta-grid">
                    <div class="meta-item">Release Date <b>{{ $quotationDate }}</b></div>
                    <div class="meta-item">Serial No. <b>{{ $receiptSerial }}</b></div>
                </div>
            </div>
        </div>

        <!-- HERO BAND -->
        <div class="hero-band">
            <div class="hero-left">
                <div class="hero-label">Amount Acknowledged</div>
                <div class="hero-val">₹ {{ number_format($receiptAmount, 2) }}</div>
            </div>
            <div class="hero-right">
                <div class="hero-badge-top">Receipt Token</div>
                <div class="hero-badge-num">#{{ substr($receiptSerial, -6) }}</div>
            </div>
        </div>

        <!-- DETAILS -->
        <div class="details-list">
            <div class="detail-row">
                <div class="detail-key">Remitter Name</div>
                <div class="detail-val hero">{{ $lead->beneficiary_name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Legal Tender Sum</div>
                <div class="detail-val words">
                    {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Rupees Only
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Transaction For</div>
                <div class="detail-val">
                    Advance consideration towards Solar Rooftop Installation of {{ $kw }} kW capacity as per Pro Forma REF: {{ $quotationSerial }}.
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Mode of Payment</div>
                <div class="detail-val">Interbank Transfer / Digital Gateway</div>
            </div>
        </div>

        <!-- BOTTOM -->
        <div class="bottom-sections">
            <div class="verif-box">
                <div class="qr-placeholder">
                    <div class="qr-inner"></div>
                </div>
                <div style="font-size: 8pt; color: var(--soft); line-height: 1.6; max-width: 80%;">
                    Verified via Digital Audit Node. <br>
                    Payments are subject to fund realization.
                </div>
            </div>
            <div class="signature-box">
                <div class="sig-container">
                    @if($sigBase64)
                        <img src="{{ $sigBase64 }}" class="sig-img" alt="Authorized Sign">
                    @else
                        <div style="height:55px"></div>
                    @endif
                    <div class="sig-line"></div>
                    <div class="sig-text">Authorized Officer</div>
                </div>
            </div>
        </div>

        <div class="footer-note">
            &copy; {{ date('Y') }} {{ $companyName }}. This is an official acknowledgment of payment receipt. <br>
            For billing inquiries, please reach out to our accounts desk at {{ $companyEmail }}.
        </div>
    </div>
</div>

</body>
</html>