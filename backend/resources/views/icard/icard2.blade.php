@php
    /** @var string|null $logoBase64 */
    /** @var string|null $sigBase64 */
    /** @var string|null $sealBase64 */
    /** @var string|null $globalLogoBase64 */
    /** @var string|null $globalSigBase64 */
    /** @var string|null $globalAffiliatedPartner */
    /** @var string|null $globalRegNo */
    /** @var string|null $globalName */
    /** @var string|null $profilePhotoBase64 */
    /** @var string|null $initials */
    /** @var string|null $designation */
    /** @var string|null $cardNumber */
    /** @var string|null $dob */
    /** @var string|null $joiningDate */
    /** @var string|null $mobile */
    /** @var string|null $address */
    /** @var string|null $barcodeBase64 */
    /** @var string|null $companyWebsite */
    /** @var string|null $companyAffiliatedWith */
    /** @var string|null $companyRegNo */
    /** @var string|null $qrBase64 */
    /** @var string|null $companyEmergency */
    /** @var string|null $icardVerifiedBy */
    /** @var string|null $companyAddress */
    /** @var string|null $companyPhone */
    /** @var string|null $companyEmail */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $companyName ?? 'SuryaMitra' }} iCard – {{ isset($user) ? $user->name : 'User' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --navy: #04111F;
    --navy-mid: #0a1f35;
    --gold: #FF9500;
    --gold-light: #FFB340;
    --gold-dim: rgba(255,149,0,0.15);
    --white: #ffffff;
    --offwhite: #f5f3ee;
    --text-muted: #8a9bb0;
    --card-w: 360px;
    --card-h: 620px;
  }

  body {
    min-height: 100vh;
    background: #0d1b2a;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
    padding: 0;
    margin: 0;
    font-family: 'DM Sans', sans-serif;
    /* Removed body background for PDF generation */
    background: transparent;
  }

  @page {
    size: 360px 620px;
    margin: 0;
  }

  .pdf-page {
    width: 360px;
    height: 620px;
    page-break-after: always;
    position: relative;
    overflow: hidden;
  }
  .pdf-page:last-child {
    page-break-after: auto;
  }

  /* ── CARD WRAPPER ── */
  .card-scene {
    perspective: 1200px;
  }

  .card {
    width: var(--card-w);
    height: var(--card-h);
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    /* Box shadow removed for PDF */
    background: var(--offwhite);
  }

  /* ── FRONT ── */
  .front {
    width: 100%;
    height: 100%;
    background: var(--offwhite);
    display: flex;
    flex-direction: column;
    position: relative;
  }

  /* Gold border frame */
  .front::before {
    content: '';
    position: absolute;
    inset: 6px;
    border: 1px solid rgba(255,149,0,0.25);
    border-radius: 15px;
    pointer-events: none;
    z-index: 10;
  }

  /* ── HEADER BAND ── */
  .front-header {
    background: var(--navy);
    padding: 18px 22px 16px;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
    height: 80px;
  }

  .front-header::before {
    content: '';
    position: absolute;
    bottom: -1px; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
  }

  .header-top {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .logo-box {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: var(--gold);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
    float: left;
    margin-right: 10px;
  }

  .logo-box svg {
    width: 26px;
    height: 26px;
  }

  .company-text {
      width: 260px;
      float: left;
  }

  .company-text h1 {
    font-family: 'Cinzel', serif;
    font-size: 10.5px;
    font-weight: 700;
    color: var(--white);
    letter-spacing: 0.08em;
    line-height: 1.3;
  }

  .company-text p {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 9px;
    font-weight: 400;
    color: var(--gold);
    letter-spacing: 0.18em;
    text-transform: uppercase;
    margin-top: 2px;
  }

  /* ── PHOTO AREA ── */
  .photo-section {
    background: var(--navy);
    padding: 20px 0 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex-shrink: 0;
    height: 180px;
    text-align: center;
  }

  /* curved bottom transition */
  .photo-section::after {
    content: '';
    position: absolute;
    bottom: -20px; left: -10vw; width: 120vw;
    height: 40px;
    background: var(--offwhite);
    border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    z-index: 2;
  }

  .photo-ring {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: conic-gradient(var(--gold) 0deg, var(--gold-light) 90deg, var(--gold) 180deg, rgba(255,149,0,0.3) 270deg, var(--gold) 360deg);
    padding: 3px;
    position: relative;
    z-index: 1;
    margin: 0 auto;
  }

  .photo-inner {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: var(--navy-mid);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid var(--navy);
  }

  .photo-inner svg {
    width: 64px;
    height: 64px;
    opacity: 0.5;
  }

  .photo-inner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
  }

  /* role badge */
  .role-badge {
    margin: 12px auto 0;
    background: #FF9500;
    color: #04111F;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    padding: 5px 18px;
    border-radius: 20px;
    position: relative;
    z-index: 1;
    display: inline-block;
  }

  /* ── NAME ── */
  .name-block {
    text-align: center;
    padding: 28px 20px 8px;
    position: relative;
    z-index: 1;
  }

  .name-block h2 {
    font-family: 'Cinzel', serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--navy);
    letter-spacing: 0.04em;
    line-height: 1.2;
  }

  /* ── DIVIDER ── */
  .divider {
    text-align: center;
    padding: 0 24px;
    margin: 6px 0 14px;
  }

  .divider-inner {
      display: inline-block;
      width: 100%;
      position: relative;
      height: 5px;
  }

  .divider-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--gold);
    display: inline-block;
    position: absolute;
    left: 49%;
    top: 0;
  }

  .divider-line {
      width: 45%;
      height: 1px;
      background: rgba(255,149,0,0.4);
      display: inline-block;
      position: absolute;
      top: 2px;
  }

  /* ── INFO GRID ── */
  .info-grid {
    width: 100%;
    padding: 0 22px;
    margin-bottom: 20px;
  }

  .info-row {
      margin-bottom: 5px;
      clear: both;
      overflow: hidden;
  }

  .info-cell {
    background: var(--white);
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid rgba(4,17,31,0.07);
    position: relative;
    overflow: hidden;
    width: 48%;
    float: left;
    height: 48px;
    box-sizing: border-box;
  }

  .info-row .info-cell:nth-child(2) {
      margin-left: 4%;
  }

  .info-cell::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 2px;
    background: var(--gold);
    border-radius: 2px;
  }

  .info-cell label {
    display: block;
    font-size: 8.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 3px;
  }

  .info-cell span {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--navy);
    line-height: 1.2;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* employee id gets gold accent */
  .info-cell.highlight span {
    color: var(--gold);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 15px;
    letter-spacing: 0.05em;
  }

  /* full-width cell */
  .info-cell.full {
    width: 100%;
  }

  /* ── FOOTER BAR ── */
  .front-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--navy);
    padding: 10px 22px;
    height: 40px;
    overflow: hidden;
  }

  .verified-tag {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--gold);
    float: left;
    display: block;
    margin-top: 4px;
  }

  .verified-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
    display: inline-block;
    margin-right: 6px;
    position: relative;
    top: 1px;
  }

  .website {
    font-size: 9px;
    color: var(--text-muted);
    letter-spacing: 0.06em;
    float: right;
    display: block;
    margin-top: 4px;
  }

  /* ── BARCODE ── */
  .barcode-row {
    padding: 0 22px;
    text-align: left;
    margin-top: 5px;
  }

  .barcode-svg {
    height: 32px;
    max-width: 150px;
    display: inline-block;
    vertical-align: middle;
  }

  .emp-id-label {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 10px;
    color: var(--text-muted);
    letter-spacing: 0.12em;
    display: inline-block;
    vertical-align: middle;
    margin-left: 10px;
  }

  /* ══════════════════════════════
     BACK CARD
  ══════════════════════════════ */
  .back {
    width: 100%;
    height: 100%;
    background: var(--navy);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
  }

  /* gold frame */
  .back::after {
    content: '';
    position: absolute;
    inset: 6px;
    border: 1px solid rgba(255,149,0,0.2);
    border-radius: 15px;
    pointer-events: none;
    z-index: 10;
  }

  .back-header {
    padding: 22px 24px 16px;
    border-bottom: 1px solid rgba(255,149,0,0.15);
    position: relative;
    z-index: 1;
    overflow: hidden;
  }

  .back-logo {
    width: 40px; height: 40px;
    border-radius: 9px;
    background: var(--gold);
    display: flex; align-items: center; justify-content: center;
    float: left;
    margin-right: 14px;
  }

  .back-logo svg { width: 22px; height: 22px; }
  .back-logo img { width: 100%; height: 100%; object-fit: contain; }

  .back-brand {
      float: left;
      width: 250px;
      margin-top: 5px;
  }

  .back-brand h2 {
    font-family: 'Cinzel', serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--gold);
    letter-spacing: 0.1em;
  }

  .back-brand p {
    font-size: 9px;
    color: var(--text-muted);
    letter-spacing: 0.12em;
    margin-top: 2px;
  }

  /* QR area */
  .qr-section {
    padding: 20px 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    text-align: center;
  }

  .qr-frame {
    background: var(--white);
    border-radius: 14px;
    padding: 12px;
    position: relative;
    display: inline-block;
  }

  /* corner accents */
  .qr-frame::before, .qr-frame::after {
    content: '';
    position: absolute;
    width: 14px; height: 14px;
    border-color: var(--gold);
    border-style: solid;
  }

  .qr-frame::before {
    top: -2px; left: -2px;
    border-width: 2px 0 0 2px;
    border-radius: 4px 0 0 0;
  }

  .qr-frame::after {
    bottom: -2px; right: -2px;
    border-width: 0 2px 2px 0;
    border-radius: 0 0 4px 0;
  }

  .qr-placeholder {
    width: 120px;
    height: 120px;
    background: #1a1a1a;
  }
  
  .qr-placeholder img {
      width: 100%;
      height: 100%;
      object-fit: contain;
  }

  .qr-label {
    margin-top: 10px;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.18em;
    color: var(--text-muted);
    text-transform: uppercase;
  }

  /* notice block */
  .notice-block {
    margin: 0 20px;
    padding: 14px 16px;
    background: rgba(255,149,0,0.07);
    border: 1px solid rgba(255,149,0,0.18);
    border-radius: 10px;
    position: relative;
    z-index: 1;
    text-align: center;
  }

  .notice-block p {
    font-size: 10px;
    color: #c8d8e8;
    line-height: 1.65;
    text-align: center;
    letter-spacing: 0.02em;
  }

  .notice-block strong {
    color: var(--white);
  }

  /* emergency pill */
  .emergency-row {
    padding: 14px 20px 0;
    text-align: center;
    position: relative;
    z-index: 1;
  }

  .emergency-pill {
    display: inline-block;
    border: 1.5px solid var(--gold);
    border-radius: 30px;
    padding: 7px 18px;
  }

  .emergency-pill span {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: var(--gold);
  }

  /* signatures */
  .sig-row {
    padding: 14px 24px 0;
    position: relative;
    z-index: 1;
    height: 60px;
  }

  .sig-block {
    text-align: center;
    width: 50%;
  }

  .sig-block-left {
      float: left;
  }
  .sig-block-right {
      float: right;
  }

  .sig-line {
    width: 80px;
    height: 1px;
    background: rgba(255,149,0,0.4);
    margin: 30px auto 4px;
  }
  
  .sig-img {
      max-height: 35px;
      width: auto;
      margin: 0 auto 4px;
      display: block;
  }

  .sig-block p {
    font-size: 8px;
    color: var(--text-muted);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    line-height: 1.5;
  }

  /* back footer */
  .back-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 12px 22px;
    border-top: 1px solid rgba(255,149,0,0.12);
    text-align: center;
    position: relative;
    z-index: 1;
  }

  .back-footer p {
    font-size: 9px;
    color: var(--text-muted);
    letter-spacing: 0.06em;
    text-align: center;
    line-height: 1.6;
    margin-bottom: 4px;
  }

  .back-footer .reg {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 10px;
    color: rgba(255,149,0,0.6);
    letter-spacing: 0.14em;
    display: block;
  }

</style>
</head>
<body>

<!-- ════════ FRONT ════════ -->
<div class="pdf-page">
  <div class="card">
    <div class="front">

      <!-- Header -->
      <div class="front-header">
        <div class="header-top">
          <div class="logo-box">
             @if($logoBase64)
                 <img src="{{ $logoBase64 }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
             @else
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="5" fill="#04111F"/>
                  <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.93 4.93l2.12 2.12M16.95 16.95l2.12 2.12M4.93 19.07l2.12-2.12M16.95 7.05l2.12-2.12" stroke="#04111F" stroke-width="2" stroke-linecap="round"/>
                </svg>
             @endif
          </div>
          <div class="company-text">
            <h1>{{ $companyName ?? 'SURYAMITRA SOLAR NETWORK' }}</h1>
            @if(isset($affiliatedPartner))
              <p>Affiliated with: {{ $affiliatedPartner }}</p>
            @endif
          </div>
        </div>
      </div>

      <!-- Photo -->
      <div class="photo-section">
        <div class="photo-ring">
          <div class="photo-inner">
            @if($profilePhotoBase64)
                <img src="{{ $profilePhotoBase64 }}" alt="Photo">
            @else
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="32" cy="26" r="12" fill="rgba(255,149,0,0.4)"/>
                  <path d="M8 56c0-13.255 10.745-24 24-24s24 10.745 24 24" fill="rgba(255,149,0,0.2)"/>
                </svg>
            @endif
          </div>
        </div>
        <div class="role-badge">{{ $designation ?? 'Member' }}</div>
      </div>

      <!-- Name -->
      <div class="name-block">
        <h2>{{ $user->name ?? 'User Name' }}</h2>
      </div>

      <div class="divider">
        <div class="divider-inner">
            <div class="divider-line" style="left: 0;"></div>
            <div class="divider-dot"></div>
            <div class="divider-line" style="right: 0;"></div>
        </div>
      </div>

      <!-- Info Grid -->
      <div class="info-grid">
        <div class="info-row">
            <div class="info-cell highlight">
              <label>Employee ID</label>
              <span>{{ $cardNumber ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
              <label>Date of Birth</label>
              <span>{{ $dob }}</span>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-cell">
              <label>Father's Name</label>
              <span>{{ $user->father_name ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
              <label>Joining Date</label>
              <span>{{ $joiningDate }}</span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-cell">
              <label>Contact</label>
              <span>{{ $mobile }}</span>
            </div>
            <div class="info-cell">
              <label>Address</label>
              <span>{{ $address }}</span>
            </div>
        </div>
      </div>

      <!-- Barcode row -->
      <div class="barcode-row">
        @if($barcodeBase64)
            <img src="{{ $barcodeBase64 }}" class="barcode-svg" alt="Barcode">
        @endif
        <span class="emp-id-label">{{ $cardNumber ?? '' }}</span>
      </div>

      <!-- Footer -->
      <div class="front-footer">
        <div class="verified-tag">
          <div class="verified-dot"></div>
          Verified Identity
        </div>
        <span class="website">{{ $companyWebsite ?? 'suryamitra.in' }}</span>
      </div>

    </div>
  </div>
</div>

<!-- ════════ BACK ════════ -->
<div class="pdf-page">
  <div class="card card-back">
    <div class="back">

      <!-- Back Header -->
      <div class="back-header">
        <div class="back-logo">
            @if($globalLogoBase64 || $logoBase64)
                <img src="{{ $globalLogoBase64 ?? $logoBase64 }}" alt="Logo">
            @else
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="5" fill="#04111F"/>
                  <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.93 4.93l2.12 2.12M16.95 16.95l2.12 2.12M4.93 19.07l2.12-2.12M16.95 7.05l2.12-2.12" stroke="#04111F" stroke-width="2" stroke-linecap="round"/>
                </svg>
            @endif
        </div>
        <div class="back-brand">
          <h2>{{ $globalAffiliatedPartner ?? $companyName ?? 'SURYAMITRA SOLAR INFRA' }}</h2>
          @if($globalRegNo)
            <p>Reg No: {{ $globalRegNo }}</p>
          @endif
        </div>
      </div>

      <!-- QR area -->
      <div class="qr-section">
        <div class="qr-frame">
          <div class="qr-placeholder">
             @if($qrBase64)
                <img src="{{ $qrBase64 }}" alt="QR">
             @endif
          </div>
        </div>
        <div class="qr-label">Scan to Verify</div>
      </div>

      <!-- notice block -->
      <div class="notice-block">
        <p>THIS IDENTITY INSTRUMENT IS ISSUED BY <br><strong>{{ $companyName ?? 'Suryamitra Solar Infrastructure' }}</strong>. <br>ISSUED FOR SECURE ACCESS ONLY. <br><br>IF FOUND, PLEASE RETURN TO A REGIONAL FACILITY.</p>
      </div>

      <!-- emergency pill -->
      <div class="emergency-row">
        <div class="emergency-pill">
          <span>EMERGENCY: {{ $companyEmergency ?? '9906766655' }}</span>
        </div>
      </div>

      <!-- signatures -->
      <div class="sig-row">
        <div class="sig-block sig-block-left">
          @if($sigBase64)
            <img src="{{ $sigBase64 }}" alt="Signature" class="sig-img">
          @else
            <div class="sig-line"></div>
          @endif
          <p>{{ $icardVerifiedBy ?? 'CHIEF OPERATIONS OFFICER' }}</p>
        </div>
        <div class="sig-block sig-block-right">
          <div class="sig-line"></div>
          <p>HOLDER'S SIGNATURE</p>
        </div>
      </div>

      <!-- back footer -->
      <div class="back-footer">
        <p>{{ $companyAddress ?? 'Srinagar, Jammu & Kashmir' }}<br>Phone: {{ $companyPhone ?? '+91 99067 66655' }} | Email: {{ $companyEmail ?? 'info@suryamitra.in' }}</p>
        @if($companyRegNo)
          <span class="reg">REG NO: {{ $companyRegNo }}</span>
        @endif
      </div>

    </div>
  </div>
</div>

</body>
</html>
