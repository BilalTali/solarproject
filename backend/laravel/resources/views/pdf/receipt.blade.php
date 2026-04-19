<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold:       #C9A84C;
            --gold-light: #F0E0B0;
            --gold-pale:  #FDF8EE;
            --dark:       #0D0D0D;
            --mid:        #4A4540;
            --soft:       #7A746E;
            --rule:       #E2D9CC;
            --bg:         #FAFAF8;
            --white:      #FFFFFF;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #ECECEC;
            padding: 40px 20px;
            min-height: 100vh;
        }

        .page {
            max-width: 860px;
            margin: 0 auto;
            background: var(--white);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 48px rgba(0,0,0,0.14), 0 2px 8px rgba(0,0,0,0.08);
        }

        /* ── LEFT GOLD STRIPE ── */
        .side-stripe {
            position: absolute;
            top: 0; left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--gold) 0%, #8B6914 100%);
            z-index: 10;
        }

        /* ── TOP BAR ── */
        .top-bar {
            background: var(--dark);
            padding: 13px 48px 13px 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .top-bar-label {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.30em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .top-bar-ref {
            font-size: 10px;
            font-weight: 300;
            letter-spacing: 0.12em;
            color: rgba(255,255,255,0.38);
        }

        /* ── HEADER ── */
        .header {
            padding: 34px 48px 30px 60px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            border-bottom: 1px solid var(--rule);
        }

        .header-left {
            display: flex;
            align-items: flex-start;
            gap: 18px;
        }

        .logo-wrap {
            width: 64px;
            height: 64px;
            border: 1px solid var(--rule);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gold-pale);
            flex-shrink: 0;
        }

        .logo-wrap img {
            max-width: 56px;
            max-height: 56px;
            object-fit: contain;
        }

        .company-name {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--dark);
            line-height: 1.1;
            margin-bottom: 7px;
        }

        .company-sub {
            font-size: 11px;
            color: var(--soft);
            font-weight: 300;
            line-height: 1.75;
        }

        .company-sub b { color: var(--mid); font-weight: 600; }

        .header-right {
            text-align: right;
            flex-shrink: 0;
        }

        .doc-title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            letter-spacing: 0.04em;
        }

        .doc-meta {
            font-size: 11px;
            color: var(--soft);
            margin-top: 5px;
            font-weight: 300;
            line-height: 1.7;
        }

        .doc-meta b { color: var(--mid); font-weight: 600; }

        /* ── GOLD RULE ── */
        .gold-rule {
            height: 1px;
            margin: 0 48px 0 60px;
            background: linear-gradient(90deg, var(--gold) 0%, rgba(201,168,76,0.06) 100%);
        }

        /* ── AMOUNT HERO BAND ── */
        .amount-hero {
            padding: 28px 48px 28px 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--dark);
            gap: 24px;
        }

        .amount-hero-label {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-bottom: 6px;
        }

        .amount-hero-value {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 38px;
            font-weight: 700;
            color: var(--gold-light);
            letter-spacing: 0.04em;
            line-height: 1;
        }

        .amount-hero-words {
            font-size: 11px;
            color: rgba(255,255,255,0.38);
            font-weight: 300;
            margin-top: 6px;
            text-transform: capitalize;
            max-width: 420px;
            line-height: 1.5;
        }

        .receipt-badge {
            flex-shrink: 0;
            border: 1px solid rgba(201,168,76,0.35);
            padding: 10px 20px;
            text-align: center;
        }

        .receipt-badge-top {
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 4px;
        }

        .receipt-badge-num {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--gold-light);
            letter-spacing: 0.05em;
        }

        /* ── DETAILS SECTION ── */
        .details-section {
            padding: 30px 48px 30px 60px;
            border-bottom: 1px solid var(--rule);
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 13px 0;
            border-bottom: 1px solid var(--rule);
            gap: 16px;
        }

        .detail-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-row:first-child { padding-top: 0; }

        .detail-key {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
            min-width: 160px;
            padding-top: 2px;
            flex-shrink: 0;
        }

        .detail-val {
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            line-height: 1.55;
        }

        .detail-val.hero {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 17px;
            font-weight: 600;
        }

        .detail-val.muted {
            color: var(--mid);
            font-weight: 400;
        }

        /* ── FOOTER ── */
        .footer {
            padding: 26px 48px 32px 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
        }

        .footnote {
            font-size: 10.5px;
            color: var(--soft);
            font-style: italic;
            font-weight: 300;
        }

        .footnote::before {
            content: '* ';
            color: var(--gold);
            font-style: normal;
        }

        .sig-box {
            text-align: center;
            min-width: 155px;
            flex-shrink: 0;
        }

        .sig-img {
            max-height: 54px;
            display: block;
            margin: 0 auto 14px;
            object-fit: contain;
        }

        .sig-space { height: 54px; margin-bottom: 14px; }

        .sig-line {
            border-top: 1.5px solid var(--dark);
            padding-top: 8px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: var(--dark);
        }

        /* ── BOTTOM BAR ── */
        .bottom-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--gold) 0%, #6B4E0A 55%, var(--dark) 100%);
        }
    </style>
</head>
<body>
<div class="page">

    <div class="side-stripe"></div>

    <!-- TOP BAR -->
    <div class="top-bar">
        <span class="top-bar-label">Payment Receipt</span>
        <span class="top-bar-ref">{{ $receiptSerial }} &nbsp;·&nbsp; {{ $quotationDate }}</span>
    </div>

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            @if($logoBase64)
            <div class="logo-wrap">
                <img src="{{ $logoBase64 }}" alt="Logo">
            </div>
            @endif
            <div>
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-sub">
                    {{ $companyAddress }}<br>
                    <b>{{ $companyEmail }}</b> &nbsp;·&nbsp; <b>{{ $companyPhone }}</b><br>
                    {{ $companyRegNo }}<br>
                    {{ $companyAffiliated }}
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">Receipt</div>
            <div class="doc-meta">
                Date &nbsp;<b>{{ $quotationDate }}</b><br>
                Ref &nbsp;<b>{{ $receiptSerial }}</b>
            </div>
        </div>
    </div>

    <div class="gold-rule"></div>

    <!-- AMOUNT HERO BAND -->
    <div class="amount-hero">
        <div>
            <div class="amount-hero-label">Amount Received</div>
            <div class="amount-hero-value">₹ {{ number_format($receiptAmount, 2) }}</div>
            <div class="amount-hero-words">
                {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Rupees Only
            </div>
        </div>
        <div class="receipt-badge">
            <div class="receipt-badge-top">Receipt No.</div>
            <div class="receipt-badge-num">{{ $receiptSerial }}</div>
        </div>
    </div>

    <!-- DETAILS -->
    <div class="details-section">
        <div class="detail-row">
            <span class="detail-key">Received From</span>
            <span class="detail-val hero">{{ $lead->beneficiary_name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-key">Sum of Rupees</span>
            <span class="detail-val">{{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($receiptAmount) }} Only</span>
        </div>
        <div class="detail-row">
            <span class="detail-key">Payment For</span>
            <span class="detail-val">
                Advance for
                @foreach($billingItems as $it)
                    {{ $it['description'] }} ({{ $it['make'] }}){{ !$loop->last ? ', ' : '' }}
                @endforeach
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-key">Mode of Payment</span>
            <span class="detail-val muted">Online / Bank Transfer</span>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footnote">Subject to realization of cheque</div>
        <div class="sig-box">
            @if($sigBase64)
                <img src="{{ $sigBase64 }}" class="sig-img" alt="Signature">
            @else
                <div class="sig-space"></div>
            @endif
            <div class="sig-line">Authorized Signatory</div>
        </div>
    </div>

    <div class="bottom-bar"></div>

</div>
</body>
</html>