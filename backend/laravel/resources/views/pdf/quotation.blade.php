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
            margin-bottom: 30px;
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

        .company-tag {
            font-size: 8.5pt;
            color: var(--gold-dark);
            font-weight: 600;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
        }

        .company-contact {
            font-size: 9pt;
            color: var(--soft);
            line-height: 1.5;
            max-width: 90%;
        }

        .company-contact b { color: var(--mid); }

        .quotation-title {
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

        /* ── CLIENT INFO ── */
        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 35px;
            background: var(--bg-accent);
            padding: 15px;
            border-radius: 4px;
        }

        .bill-to {
            display: table-cell;
            width: 50%;
        }

        .bill-label {
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--soft);
            margin-bottom: 8px;
        }

        .client-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16pt;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .client-details {
            font-size: 9.5pt;
            color: var(--mid);
            line-height: 1.4;
        }

        /* ── TABLE ── */
        .table-wrap {
            flex-grow: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: var(--dark);
            color: #FFF;
            font-size: 8.5pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 12px 10px;
            text-align: left;
        }

        td {
            padding: 14px 10px;
            border-bottom: 1px solid var(--border);
            font-size: 10pt;
            vertical-align: top;
        }

        .col-desc { width: 50%; }
        .col-qty { width: 10%; text-align: center; }
        .col-rate { width: 20%; text-align: right; }
        .col-total { width: 20%; text-align: right; font-weight: 600; }

        .item-make {
            display: block;
            font-size: 8.5pt;
            color: var(--soft);
            margin-top: 3px;
            font-style: italic;
        }

        /* ── SUMMARY ── */
        .summary-block {
            display: table;
            width: 100%;
            margin-top: 20px;
        }

        .summary-left {
            display: table-cell;
            width: 55%;
            vertical-align: bottom;
            padding-right: 40px;
        }

        .summary-right {
            display: table-cell;
            width: 45%;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }

        .summary-row.total {
            border-bottom: none;
            background: var(--bg-accent);
            padding: 12px 10px;
            margin-top: 5px;
            border-left: 3px solid var(--gold);
        }

        .summary-lbl { font-size: 9.5pt; color: var(--soft); font-weight: 500; }
        .summary-val { font-size: 10pt; color: var(--dark); font-weight: 600; }
        
        .total-lbl { font-size: 11pt; font-weight: 700; color: var(--dark); text-transform: uppercase; }
        .total-val { font-size: 15pt; font-weight: 700; color: var(--gold-dark); }

        .amount-words {
            font-size: 8.5pt;
            color: var(--soft);
            text-transform: capitalize;
            font-style: italic;
            margin-top: 10px;
            padding: 10px;
            border: 1px dashed var(--border);
        }

        /* ── BOTTOM INFO ── */
        .bottom-sections {
            display: table;
            width: 100%;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1.5px solid var(--dark);
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
            background: #F9F9F9;
            padding: 15px;
            border-radius: 4px;
            font-size: 8.5pt;
            line-height: 1.6;
        }

        .bank-box b { color: var(--gold-dark); text-transform: uppercase; display: block; margin-bottom: 5px; font-size: 8pt; letter-spacing: 0.1em; }

        .sig-img {
            max-height: 45px;
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

        /* ── FOOTER ── */
        .footer-note {
            margin-top: auto;
            padding-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: var(--soft);
            letter-spacing: 0.05em;
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
            <div class="doc-type">Pro Forma Quotation</div>
            <div class="doc-id">REF: {{ $quotationSerial }} / {{ $quotationDate }}</div>
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
                <div class="company-tag">{{ $companyAffiliated }}</div>
                <div class="company-contact">
                    <b>Reg No:</b> {{ $companyRegNo }} <br>
                    <b>Address:</b> {{ $companyAddress }} <br>
                    <b>Email:</b> {{ $companyEmail }} &nbsp; <b>Phone:</b> {{ $companyPhone }}
                </div>
            </div>
            <div class="header-right">
                <div class="quotation-title">Quotation</div>
                <div class="meta-grid">
                    <div class="meta-item">Date: <b>{{ $quotationDate }}</b></div>
                    <div class="meta-item">Ref: <b>{{ $quotationSerial }}</b></div>
                    <div class="meta-item">Validity: <b>30 Days</b></div>
                </div>
            </div>
        </div>

        <!-- CLIENT INFO -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="bill-label">Customer Details</div>
                <div class="client-name">{{ $lead->beneficiary_name }}</div>
                <div class="client-details">
                    {{ $address }} <br>
                    Contact: {{ $lead->beneficiary_mobile }} <br>
                    System Capacity: {{ $kw }} kW Solar PV System
                </div>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-desc">Description of Solution</th>
                        <th class="col-qty">QTY</th>
                        <th class="col-rate">Unit Price</th>
                        <th class="col-total">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($billingItems as $it)
                    <tr>
                        <td class="col-desc">
                            {{ $it['description'] }}
                            <span class="item-make">Make/Brand: {{ $it['make'] ?? 'Standard' }}</span>
                        </td>
                        <td class="col-qty">1 Set</td>
                        <td class="col-rate">₹ {{ number_format($it['rate'], 2) }}</td>
                        <td class="col-total">₹ {{ number_format($it['rate'], 2) }}</td>
                    </tr>
                    @endforeach
                    <!-- Placeholder rows for visual balance if needed -->
                    @for($i = count($billingItems); $i < 3; $i++)
                    <tr style="height: 40px;"><td colspan="4"></td></tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- SUMMARY -->
        <div class="summary-block">
            <div class="summary-left">
                <div class="bill-label">Total in Words</div>
                <div class="amount-words">
                    {{ \NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($totalAmount) }} Rupees Only
                </div>
            </div>
            <div class="summary-right">
                <div class="summary-row">
                    <span class="summary-lbl">Sub Total (Taxable)</span>
                    <span class="summary-val">₹ {{ number_format($baseAmount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-lbl">GST ({{ $gstPercentage }}%) <small>(CGST + SGST)</small></span>
                    <span class="summary-val">₹ {{ number_format($gstAmount, 2) }}</span>
                </div>
                <div class="summary-row total">
                    <span class="total-lbl">Grand Total</span>
                    <span class="total-val">₹ {{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- BANK & SIGNATURE -->
        <div class="bottom-sections">
            <div class="bank-details">
                <div class="bank-box">
                    <b>Bank Details for Payment</b>
                    Acc Holder: {{ $bankAccountName }} <br>
                    Bank Name: {{ $bankBranch }} <br>
                    Account No: <b>{{ $bankAccountNumber }}</b> <br>
                    IFSC Code: <b>{{ $bankIfsc }}</b>
                </div>
            </div>
            <div class="signature-box">
                @if($sigBase64)
                    <img src="{{ $sigBase64 }}" class="sig-img" alt="Authorized Signature">
                @else
                    <div style="height:45px"></div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-text">Authorized Signatory</div>
            </div>
        </div>

        <div class="footer-note">
            This is a computer-generated Pro Forma Quotation and requires no physical seal if digitally signed.
        </div>
    </div>
</div>

</body>
</html>