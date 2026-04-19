<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pro Forma Quotation</title>
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

        .company-sub b {
            color: var(--mid);
            font-weight: 600;
        }

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

        /* ── THIN GOLD RULE ── */
        .gold-rule {
            height: 1px;
            margin: 0 48px 0 60px;
            background: linear-gradient(90deg, var(--gold) 0%, rgba(201,168,76,0.06) 100%);
        }

        /* ── BILL-TO BAND ── */
        .bill-to-band {
            padding: 22px 48px 22px 60px;
            display: flex;
            gap: 40px;
            align-items: flex-start;
            border-bottom: 1px solid var(--rule);
        }

        .field-block { flex: 1; }
        .field-block.shrink { flex: 0; min-width: 130px; }

        .field-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 4px;
        }

        .field-value {
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            line-height: 1.5;
        }

        .field-value.hero {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 17px;
            font-weight: 600;
        }

        /* ── TABLE ── */
        .table-wrap {
            padding: 28px 48px 0 60px;
        }

        table { width: 100%; border-collapse: collapse; }

        thead tr {
            border-top: 1.5px solid var(--dark);
            border-bottom: 1.5px solid var(--dark);
        }

        thead th {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--dark);
            padding: 10px 10px;
            text-align: left;
        }

        thead th.r { text-align: right; }
        thead th.c { text-align: center; }

        tbody tr { border-bottom: 1px solid var(--rule); }
        tbody tr:last-child { border-bottom: none; }

        tbody td {
            padding: 14px 10px;
            vertical-align: top;
        }

        .td-sno {
            text-align: center;
            color: var(--soft);
            font-size: 11px;
        }

        .td-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 13px;
            display: block;
        }

        .td-make {
            font-size: 11px;
            color: var(--soft);
            font-weight: 300;
        }

        .td-center { text-align: center; color: var(--mid); }

        .td-right {
            text-align: right;
            font-variant-numeric: tabular-nums;
            color: var(--dark);
            font-weight: 500;
        }

        /* ── SUMMARY ── */
        .summary-wrap {
            padding: 24px 48px 0 60px;
            display: flex;
            justify-content: flex-end;
        }

        .summary-box {
            width: 310px;
            border: 1px solid var(--rule);
            overflow: hidden;
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 16px;
            border-bottom: 1px solid var(--rule);
            font-size: 12px;
        }

        .sum-row:last-child { border-bottom: none; }
        .sum-row.sub { background: var(--bg); }

        .sum-lbl { font-weight: 500; color: var(--mid); }
        .sum-val { font-variant-numeric: tabular-nums; font-weight: 600; color: var(--dark); }

        .sum-row.total-row {
            background: var(--dark);
            padding: 13px 16px;
        }

        .sum-row.total-row .sum-lbl {
            color: var(--gold-light);
            font-size: 13px;
            font-weight: 600;
        }

        .sum-row.total-row .sum-val {
            color: var(--gold-light);
            font-size: 14px;
        }

        /* ── AMOUNT IN WORDS ── */
        .words-wrap {
            margin: 20px 48px 30px 60px;
            padding: 11px 16px;
            background: var(--gold-pale);
            border-left: 3px solid var(--gold);
        }

        .w-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 3px;
        }

        .w-text {
            font-size: 12.5px;
            font-weight: 500;
            color: var(--dark);
            text-transform: capitalize;
        }

        /* ── FOOTER ── */
        .footer {
            border-top: 1px solid var(--rule);
            padding: 26px 48px 32px 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
        }

        .bank-title {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 12px;
        }

        .bank-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 5px 18px;
        }

        .bk {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--mid);
            white-space: nowrap;
        }

        .bv {
            font-size: 11.5px;
            color: var(--dark);
            font-weight: 400;
        }

        .sig-box {
            text-align: center;
            min-width: 155px;
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

        /* ── BOTTOM GRADIENT BAR ── */
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
        <span class="top-bar-label">Pro Forma Quotation</span>
        <span class="top-bar-ref">Ref: {{ $quotationSerial }} &nbsp;·&nbsp; {{ $quotationDate }}</span>
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
            <div class="doc-title">Quotation</div>
            <div class="doc-meta">
                Date &nbsp;<b>{{ $quotationDate }}</b><br>
                Ref &nbsp;<b>{{ $quotationSerial }}</b>
            </div>
        </div>
    </div>

    <div class="gold-rule"></div>

    <!-- BILL TO -->
    <div class="bill-to-band">
        <div class="field-block">
            <div class="field-label">Billed To</div>
            <div class="field-value hero">{{ $lead->beneficiary_name }}</div>
        </div>
        <div class="field-block">
            <div class="field-label">Address</div>
            <div class="field-value">{{ $address }}</div>
        </div>
        <div class="field-block shrink">
            <div class="field-label">Contact</div>
            <div class="field-value">{{ $lead->beneficiary_mobile }}</div>
        </div>
    </div>

    <!-- ITEMS TABLE -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="c" width="5%">#</th>
                    <th width="47%">Description</th>
                    <th class="c" width="10%">Qty</th>
                    <th class="r" width="19%">Rate (₹)</th>
                    <th class="r" width="19%">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($billingItems as $index => $item)
                <tr>
                    <td class="td-sno">{{ $index + 1 }}</td>
                    <td>
                        <span class="td-name">{{ $item['description'] }}</span>
                        <span class="td-make">Make: {{ $item['make'] }}</span>
                    </td>
                    <td class="td-center">1 Set</td>
                    <td class="td-right">{{ number_format($item['rate'], 2) }}</td>
                    <td class="td-right">{{ number_format($item['rate'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- SUMMARY -->
    <div class="summary-wrap">
        <div class="summary-box">
            <div class="sum-row sub">
                <span class="sum-lbl">Taxable Amount</span>
                <span class="sum-val">{{ number_format($baseAmount, 2) }}</span>
            </div>
            <div class="sum-row">
                <span class="sum-lbl">GST @ {{ $gstPercentage }}% &nbsp;<small style="color:var(--soft);font-weight:400;">(CGST + SGST)</small></span>
                <span class="sum-val">{{ number_format($gstAmount, 2) }}</span>
            </div>
            <div class="sum-row total-row">
                <span class="sum-lbl">Total Amount</span>
                <span class="sum-val">₹ {{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- AMOUNT IN WORDS -->
    <div class="words-wrap">
        <div class="w-label">Amount in Words</div>
        <div class="w-text">{{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only.</div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div>
            <div class="bank-title">Bank Details</div>
            <div class="bank-grid">
                <span class="bk">Account Name</span><span class="bv">{{ $bankAccountName }}</span>
                <span class="bk">Account No.</span><span class="bv">{{ $bankAccountNumber }}</span>
                <span class="bk">IFSC Code</span><span class="bv">{{ $bankIfsc }}</span>
                <span class="bk">Branch</span><span class="bv">{{ $bankBranch }}</span>
            </div>
        </div>
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