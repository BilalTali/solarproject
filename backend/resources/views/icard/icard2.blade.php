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
  <title>SuryaMitra iCard – {{ $user->name ?? 'User' }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body, h1, h2, h3, p, span, div, strong, table, td, tr {
      font-family: Helvetica, Arial, sans-serif !important;
    }

    /* Paper size match DomPDF settings: width 270pt (~360px), height 465pt (~620px) */
    @page { 
      size: 270pt 465pt;
      margin: 0; 
    }

    body {
      background: #f5f3ee;
      margin: 0; 
      padding: 0;
    }

    .page {
      width: 360px;
      height: 620px;
      position: relative;
      overflow: hidden;
      background: #f5f3ee;
    }

    .page-back {
      width: 360px;
      height: 620px;
      position: relative;
      overflow: hidden;
      background: #04111F;
      page-break-before: always;
    }

    .gold-border {
      position: absolute;
      top: 6px;
      left: 6px;
      right: 6px;
      bottom: 6px;
      border: 1px solid rgba(255,149,0,0.3);
      border-radius: 12px;
      z-index: 50; 
      pointer-events: none;
    }

    /* ════ FRONT PAGE ════ */

    .front-header {
      position: absolute;
      top: 0;
      left: 0;
      width: 360px;
      height: 65px;
      background: #04111F;
      border-bottom: 2px solid #FF9500;
      z-index: 10;
    }

    /* Gold Box workaround for DOMPDF: use border trick if bg fails, but background should work if explicit block */
    .header-logo-box {
      position: absolute;
      top: 14px;
      left: 20px;
      width: 40px;
      height: 40px;
      background: #FF9500;
      border-radius: 8px;
      text-align: center;
    }

    .header-logo-img {
      position: absolute;
      top: 7px;
      left: 7px;
      width: 26px;
      height: 26px;
      object-fit: contain;
    }

    .header-text-box {
      position: absolute;
      top: 17px;
      left: 75px;
      width: 260px;
      color: #FFF;
      line-height: 1.2;
    }

    .header-text-h1 {
      font-size: 11px;
      font-weight: bold;
      color: #FFFFFF;
      letter-spacing: 0.5px;
      font-family: serif !important;
    }

    .header-text-p {
      font-size: 9px;
      color: #FF9500;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-top: 3px;
    }

    .photo-section-bg {
      position: absolute;
      top: 65px;
      left: 0;
      width: 360px;
      height: 130px;
      background: #04111F;
      z-index: 8;
    }

    .photo-section-curve {
      position: absolute;
      top: 165px;
      left: -40px;
      width: 440px; 
      height: 60px;
      background: #f5f3ee;
      border-radius: 50%;
      z-index: 9;
    }

    /* The photo profile */
    .photo-holder-outer {
      position: absolute;
      top: 85px;
      left: 130px; 
      width: 100px;
      height: 100px;
      background: #FF9500;
      border-radius: 50%;
      z-index: 15;
    }

    .photo-holder-inner {
      position: absolute;
      top: 4px;
      left: 4px;
      width: 92px;
      height: 92px;
      border: 3px solid #04111F;
      background: #0a1f35;
      border-radius: 50%;
      overflow: hidden;
    }

    .profile-photo {
      width: 92px;
      height: 92px;
      object-fit: cover;
      border-radius: 50%;
    }

    .initials-avatar {
      width: 92px;
      height: 92px;
      line-height: 92px;
      text-align: center;
      background: #0a1f35;
      color: #FF9500;
      font-size: 32px;
      font-weight: bold;
      border-radius: 50%;
    }

    .role-badge-box {
      position: absolute;
      top: 200px;
      left: 0;
      width: 360px;
      text-align: center;
      z-index: 20;
    }

    .role-badge {
      display: inline-block;
      background: #FF9500;
      color: #04111F;
      font-size: 11px;
      font-weight: bold;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 5px 16px;
      border-radius: 12px;
    }

    .name-h2 {
      position: absolute;
      top: 235px;
      left: 0;
      width: 360px;
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      color: #04111F;
      font-family: serif !important;
    }

    .divider-line {
      position: absolute;
      top: 268px;
      left: 30px;
      width: 300px;
      height: 1px;
      background: rgba(255,149,0,0.4);
    }

    .divider-dot {
      position: absolute;
      top: 266px;
      left: 177px;
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: #FF9500;
      z-index: 10;
    }

    /* Table Grid */
    .info-table {
      position: absolute;
      top: 285px;
      left: 20px;
      width: 320px; 
      border-collapse: separate;
      border-spacing: 8px;
    }

    .info-td {
      background: #FFFFFF;
      border: 1px solid rgba(4,17,31,0.07);
      border-radius: 10px;
      padding: 8px 10px;
      vertical-align: top;
      position: relative;
      width: 50%;
    }

    .info-td-border {
      position: absolute;
      left: 0;
      top: 10px;
      bottom: 10px;
      width: 3px;
      background: #FF9500;
      border-radius: 2px;
    }

    .info-label {
      font-size: 8px;
      font-weight: bold;
      color: #8a9bb0;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 2px;
    }

    .info-val {
      font-size: 11px;
      font-weight: bold;
      color: #04111F;
    }

    .info-val-gold {
      font-size: 14px;
      font-weight: bold;
      color: #FF9500;
      letter-spacing: 1px;
    }

    /* Barcode */
    .barcode-area {
      position: absolute;
      bottom: 50px;
      left: 25px;
      width: 310px;
      height: 40px;
    }

    .barcode-img {
      width: 160px;
      height: 30px;
      float: left;
    }

    .emp-id-label {
      float: right;
      font-size: 11px;
      font-weight: bold;
      color: #8a9bb0;
      letter-spacing: 1px;
      margin-top: 10px;
    }

    /* Footer border */
    .front-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 360px;
      height: 38px;
      background: #04111F;
    }

    .footer-left {
      position: absolute;
      left: 20px;
      top: 12px;
      font-size: 10px;
      font-weight: bold;
      color: #FF9500;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .verified-circle {
      display: inline-block;
      width: 7px;
      height: 7px;
      background: #22c55e;
      border-radius: 50%;
      margin-right: 5px;
    }

    .footer-right {
      position: absolute;
      right: 20px;
      top: 13px;
      font-size: 9px;
      color: #8a9bb0;
      letter-spacing: 0.5px;
    }


    /* ════ BACK PAGE ════ */

    .back-header-box {
      border-bottom: 1px solid rgba(255,149,0,0.15);
      position: absolute;
      top: 30px;
      left: 25px;
      width: 310px;
      height: 50px;
    }

    .back-logo-box {
      position: absolute;
      top: 0;
      left: 0;
      width: 38px;
      height: 38px;
      background: #FF9500;
      border-radius: 8px;
    }

    .back-logo-img {
      position: absolute;
      top: 8px;
      left: 8px;
      width: 22px;
      height: 22px;
      object-fit: contain;
    }

    .back-text-box {
      position: absolute;
      top: 4px;
      left: 50px;
    }

    .back-text-h2 {
      font-size: 13px;
      font-weight: bold;
      color: #FF9500;
      letter-spacing: 1px;
      font-family: serif !important;
    }

    .back-text-p {
      font-size: 8px;
      color: #8a9bb0;
      letter-spacing: 1px;
      margin-top: 2px;
    }

    .qr-area {
      position: absolute;
      top: 110px;
      left: 0;
      width: 360px;
      text-align: center;
    }

    .qr-box {
      display: inline-block;
      background: #FFFFFF;
      padding: 12px;
      border-radius: 12px;
      border: 1px solid #FF9500;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .qr-img {
      width: 100px;
      height: 100px;
    }

    .qr-label {
      margin-top: 10px;
      font-size: 9px;
      font-weight: bold;
      color: #8a9bb0;
      letter-spacing: 1.5px;
      text-transform: uppercase;
    }

    .notice-box {
      position: absolute;
      top: 300px;
      left: 30px;
      width: 300px; 
      background: rgba(255,149,0,0.07);
      border: 1px solid rgba(255,149,0,0.18);
      border-radius: 10px;
      padding: 14px;
      text-align: center;
    }

    .notice-p {
      font-size: 10px;
      color: #c8d8e8;
      line-height: 1.6;
    }

    .emergency-box {
      position: absolute;
      top: 410px;
      left: 0;
      width: 360px;
      text-align: center;
    }

    .emergency-pill {
      display: inline-block;
      border: 1.5px solid #FF9500;
      border-radius: 30px;
      padding: 8px 18px;
      font-size: 12px;
      font-weight: bold;
      color: #FF9500;
      letter-spacing: 1px;
    }

    .sig-table {
      position: absolute;
      top: 470px;
      left: 20px;
      width: 320px;
      border-collapse: collapse;
    }

    .sig-td {
      width: 50%;
      text-align: center;
      position: relative;
      height: 50px;
    }

    .sig-line {
      width: 80px;
      height: 1px;
      background: rgba(255,149,0,0.4);
      margin: 0 auto;
    }

    .sig-label {
      font-size: 8px;
      color: #8a9bb0;
      letter-spacing: 1px;
      text-transform: uppercase;
      margin-top: 4px;
      line-height: 1.4;
    }

    .sig-img {
      height: 30px;
      width: auto;
      margin-bottom: 2px;
    }

    .seal-img {
      position: absolute;
      right: 20px;
      bottom: 15px;
      height: 45px;
      width: auto;
      opacity: 0.6;
    }

    .back-footer {
      position: absolute;
      bottom: 12px;
      left: 0;
      width: 360px;
      text-align: center;
      border-top: 1px solid rgba(255,149,0,0.12);
      padding-top: 12px;
    }

    .back-footer-p {
      font-size: 8px;
      color: #8a9bb0;
      line-height: 1.5;
      margin-bottom: 2px;
    }

    .back-footer-reg {
      font-size: 9px;
      color: rgba(255,149,0,0.6);
      letter-spacing: 1px;
      margin-bottom: 4px;
      display: block;
    }

  </style>
</head>
<body>

  <!-- ════════ FRONT ════════ -->
  <div class="page">
    <div class="gold-border"></div>

    <div class="front-header">
      <div class="header-logo-box">
        @if($logoBase64)
          <img src="{{ $logoBase64 }}" class="header-logo-img" alt="Logo">
        @endif
      </div>
      <div class="header-text-box">
        <div class="header-text-h1">{{ $companyName ?? 'ANDLEEB CLUSTER OF SERVICES PVT. LTD.' }}</div>
        <div class="header-text-p">Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Tech Agency' }}</div>
      </div>
    </div>

    <!-- Dark section behind photo -->
    <div class="photo-section-bg"></div>
    <!-- White Curve cutting into dark section -->
    <div class="photo-section-curve"></div>

    <!-- Profile photo -->
    <div class="photo-holder-outer">
      <div class="photo-holder-inner">
        @if($profilePhotoBase64)
          <img src="{{ $profilePhotoBase64 }}" class="profile-photo" alt="Photo">
        @else
          <div class="initials-avatar">{{ $initials ?? 'SM' }}</div>
        @endif
      </div>
    </div>

    <!-- Role Badge positioned right overlapping the curve -->
    <div class="role-badge-box">
      <div class="role-badge">{{ $designation ?? 'Administrator' }}</div>
    </div>

    <div class="name-h2">{{ $user->name ?? 'User Name' }}</div>

    <div class="divider-line"></div>
    <div class="divider-dot"></div>

    <!-- Information Table Grid -->
    <table class="info-table">
      <tr>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Employee ID</div>
          <div class="info-val info-val-gold">{{ $cardNumber ?? 'ADM-0002' }}</div>
        </td>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Date of Birth</div>
          <div class="info-val">{{ $dob ?? '02 Feb 1980' }}</div>
        </td>
      </tr>
      <tr>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Father's Name</div>
          <div class="info-val" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $user->father_name ?? 'Habib Ullah Tali' }}</div>
        </td>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Joining Date</div>
          <div class="info-val">{{ $joiningDate ?? '11 Apr 2026' }}</div>
        </td>
      </tr>
      <tr>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Contact</div>
          <div class="info-val">{{ $mobile ?? '9797287817' }}</div>
        </td>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Address</div>
          <div class="info-val" style="font-size: 9px;">{{ $address ?? 'Pampore, Pulwama' }}</div>
        </td>
      </tr>
    </table>

    <div class="barcode-area">
      @if($barcodeBase64)
        <img src="{{ $barcodeBase64 }}" class="barcode-img" alt="Barcode">
      @endif
      <div class="emp-id-label">{{ $cardNumber ?? 'ADM-0002' }}</div>
    </div>

    <div class="front-footer">
      <div class="footer-left">
        <span class="verified-circle"></span> Verified Identity
      </div>
      <div class="footer-right">{{ $companyWebsite ?? 'andleebsurya.in' }}</div>
    </div>

  </div>

  <!-- ════════ BACK ════════ -->
  <div class="page-back">
    <div class="gold-border"></div>

    <div class="back-header-box">
      <div class="back-logo-box">
        @if($globalLogoBase64)
          <img src="{{ $globalLogoBase64 }}" class="back-logo-img" alt="Logo">
        @endif
      </div>
      <div class="back-text-box">
        <div class="back-text-h2">{{ $companyName ?? 'MALIK SURYA' }}</div>
        <div class="back-text-p">REG NO: {{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</div>
      </div>
    </div>

    <div class="qr-area">
      <div class="qr-box">
        @if($qrBase64)
          <img src="{{ $qrBase64 }}" class="qr-img" alt="QR">
        @endif
      </div>
      <div class="qr-label">Scan to Verify</div>
    </div>

    <div class="notice-box">
      <div class="notice-p">
        This identity instrument is issued by <br>
        <strong style="color: #FFF;">{{ $companyName ?? 'Andleeb Cluster of Services Pvt. Ltd.' }}</strong><br>
        Issued for secure access only.<br>
        If found, please return to a regional facility<br>
        or the <strong>Residency Road HQ.</strong>
      </div>
    </div>

    <div class="emergency-box">
      <div class="emergency-pill">Emergency: {{ $companyEmergency ?? '9906766655' }}</div>
    </div>

    <table class="sig-table">
      <tr>
        <td class="sig-td" style="vertical-align: bottom;">
          <!-- Holding Signature -->
          <div class="sig-line"></div>
          <div class="sig-label">Issuing Authority<br>Principal Seal</div>
        </td>
        <td class="sig-td" style="vertical-align: bottom;">
          <!-- Authorized Signatory -->
          @if($sealBase64)
            <img src="{{ $sealBase64 }}" class="seal-img" alt="Seal">
          @endif
          @if($sigBase64)
            <img src="{{ $sigBase64 }}" class="sig-img" alt="Signature">
          @endif
          <div class="sig-line"></div>
          <div class="sig-label">Verified By<br>Chief Operations Officer</div>
        </td>
      </tr>
    </table>

    <div class="back-footer">
      <span class="back-footer-reg">{{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</span>
      <div class="back-footer-p">
        {{ $companyAddress ?? 'Tali Mohallah Sugin, Khag, Budgam – 193411' }}
      </div>
      <div class="back-footer-p">
        {{ $companyPhone ?? '+91-9797287817' }} &nbsp; | &nbsp; {{ $companyEmail ?? 'info@andleebsurya.in' }}
      </div>
    </div>

  </div>

</body>
</html>
