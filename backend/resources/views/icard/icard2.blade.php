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
      width: 100%;
      height: 100%;
      position: relative;
      overflow: hidden;
    }

    .page-back {
      width: 100%;
      height: 100%;
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
      border-radius: 15px;
      z-index: 50; /* Above back items */
    }

    /* ════ FRONT PAGE ════ */

    .front-header {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 55px; /* Adjust height slightly for points mapping */
      background: #04111F;
      border-bottom: 3px solid #FF9500;
      z-index: 10;
    }

    .header-logo-box {
      position: absolute;
      top: 10px;
      left: 15px;
      width: 35px;
      height: 35px;
      background: #FF9500;
      border-radius: 8px;
    }

    .header-logo-img {
      position: absolute;
      top: 4px;
      left: 4px;
      width: 27px;
      height: 27px;
      object-fit: contain;
    }

    .header-text-box {
      position: absolute;
      top: 13px;
      left: 60px;
      color: #FFF;
    }

    .header-text-h1 {
      font-size: 11px;
      font-weight: bold;
      color: #FFFFFF;
      letter-spacing: 0.5px;
      margin-bottom: 2px;
    }

    .header-text-p {
      font-size: 8px;
      color: #FF9500;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .photo-section-bg {
      position: absolute;
      top: 55px;
      left: 0;
      width: 100%;
      height: 120px;
      background: #04111F;
      z-index: 8;
    }

    .photo-section-curve {
      position: absolute;
      top: 150px;
      left: -20px;
      width: 320px; /* Overflows width a bit to flatten curve */
      height: 60px;
      background: #f5f3ee;
      border-radius: 50%;
      z-index: 9;
    }

    .photo-holder-outer {
      position: absolute;
      top: 65px;
      left: 95px; /* Center approx 270pt/2 - width/2 */
      width: 80px;
      height: 80px;
      background: #FF9500;
      border-radius: 50%;
      z-index: 15;
    }

    .photo-holder-inner {
      position: absolute;
      top: 4px;
      left: 4px;
      width: 72px;
      height: 72px;
      border: 2px solid #04111F;
      background: #0a1f35;
      border-radius: 50%;
      overflow: hidden;
    }

    .profile-photo {
      width: 72px;
      height: 72px;
      object-fit: cover;
      border-radius: 50%;
    }

    .initials-avatar {
      width: 72px;
      height: 72px;
      line-height: 72px;
      text-align: center;
      background: #0a1f35;
      color: #FF9500;
      font-size: 26px;
      font-weight: bold;
      border-radius: 50%;
    }

    .role-badge-box {
      position: absolute;
      top: 155px;
      left: 0;
      width: 100%;
      text-align: center;
      z-index: 20;
    }

    .role-badge {
      display: inline-block;
      background: #FF9500;
      color: #04111F;
      font-size: 10px;
      font-weight: bold;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: 12px;
    }

    .name-h2 {
      position: absolute;
      top: 185px;
      left: 0;
      width: 100%;
      text-align: center;
      font-size: 18px;
      font-weight: bold;
      color: #04111F;
    }

    .divider-line {
      position: absolute;
      top: 215px;
      left: 20px;
      right: 20px;
      height: 1px;
      background: rgba(255,149,0,0.4);
    }

    .divider-dot {
      position: absolute;
      top: 212px;
      left: 132px;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #FF9500;
      z-index: 10;
    }

    /* Table Grid */
    .info-table {
      position: absolute;
      top: 230px;
      left: 15px;
      width: 240px; /* Within 270pt width */
      border-collapse: separate;
      border-spacing: 6px;
    }

    .info-td {
      background: #FFFFFF;
      border: 1px solid rgba(4,17,31,0.07);
      border-radius: 8px;
      padding: 6px 8px;
      vertical-align: top;
      position: relative;
      width: 50%;
    }

    .info-td-border {
      position: absolute;
      left: 0;
      top: 5px;
      bottom: 5px;
      width: 2px;
      background: #FF9500;
      border-radius: 2px;
    }

    .info-label {
      font-size: 7px;
      font-weight: bold;
      color: #8a9bb0;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 2px;
    }

    .info-val {
      font-size: 10px;
      font-weight: bold;
      color: #04111F;
    }

    .info-val-gold {
      font-size: 11px;
      font-weight: bold;
      color: #FF9500;
      letter-spacing: 0.5px;
    }

    /* Barcode */
    .barcode-area {
      position: absolute;
      bottom: 45px;
      left: 20px;
      width: 230px;
      height: 40px;
    }

    .barcode-img {
      width: 140px;
      height: 25px;
      float: left;
    }

    .emp-id-label {
      float: right;
      font-size: 10px;
      font-weight: bold;
      color: #555E70;
      letter-spacing: 1px;
      margin-top: 8px;
    }

    /* Footer border */
    .front-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 35px;
      background: #04111F;
    }

    .footer-left {
      position: absolute;
      left: 15px;
      top: 11px;
      font-size: 9px;
      font-weight: bold;
      color: #FF9500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .verified-circle {
      display: inline-block;
      width: 6px;
      height: 6px;
      background: #22c55e;
      border-radius: 50%;
      margin-right: 4px;
    }

    .footer-right {
      position: absolute;
      right: 15px;
      top: 11px;
      font-size: 8px;
      color: #8a9bb0;
      letter-spacing: 0.5px;
    }


    /* ════ BACK PAGE ════ */

    .back-header-box {
      border-bottom: 1px solid rgba(255,149,0,0.15);
      position: absolute;
      top: 20px;
      left: 20px;
      width: 230px;
      height: 45px;
    }

    .back-logo-box {
      float: left;
      width: 32px;
      height: 32px;
      background: #FF9500;
      border-radius: 6px;
      margin-right: 10px;
      margin-top: 3px;
    }

    .back-logo-img {
      width: 20px;
      height: 20px;
      margin: 6px;
      object-fit: contain;
    }

    .back-text-box {
      float: left;
      padding-top: 4px;
    }

    .back-text-h2 {
      font-size: 11px;
      font-weight: bold;
      color: #FF9500;
      letter-spacing: 0.5px;
    }

    .back-text-p {
      font-size: 7px;
      color: #8a9bb0;
      letter-spacing: 0.5px;
      margin-top: 2px;
    }

    .qr-area {
      position: absolute;
      top: 85px;
      left: 0;
      width: 100%;
      text-align: center;
    }

    .qr-box {
      display: inline-block;
      background: #FFFFFF;
      padding: 8px;
      border-radius: 10px;
      border: 1px solid #FF9500;
    }

    .qr-img {
      width: 80px;
      height: 80px;
    }

    .qr-label {
      margin-top: 6px;
      font-size: 8px;
      font-weight: bold;
      color: #8a9bb0;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .notice-box {
      position: absolute;
      top: 220px;
      left: 20px;
      width: 210px; /* padding included: 230px */
      background: rgba(255,149,0,0.07);
      border: 1px solid rgba(255,149,0,0.18);
      border-radius: 8px;
      padding: 10px;
      text-align: center;
    }

    .notice-p {
      font-size: 8.5px;
      color: #c8d8e8;
      line-height: 1.5;
    }

    .emergency-box {
      position: absolute;
      top: 300px;
      left: 0;
      width: 100%;
      text-align: center;
    }

    .emergency-pill {
      display: inline-block;
      border: 1.5px solid #FF9500;
      border-radius: 20px;
      padding: 6px 14px;
      font-size: 10px;
      font-weight: bold;
      color: #FF9500;
      letter-spacing: 0.5px;
    }

    .sig-table {
      position: absolute;
      top: 345px;
      left: 20px;
      width: 230px;
      border-collapse: collapse;
    }

    .sig-td {
      width: 50%;
      text-align: center;
      position: relative;
      height: 40px;
    }

    .sig-line {
      width: 60px;
      height: 1px;
      background: rgba(255,149,0,0.4);
      margin: 0 auto;
    }

    .sig-label {
      font-size: 7px;
      color: #8a9bb0;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-top: 3px;
    }

    .sig-img {
      height: 25px;
      width: auto;
      margin-bottom: 2px;
    }

    .seal-img {
      position: absolute;
      right: 15px;
      bottom: 8px;
      height: 40px;
      width: auto;
      opacity: 0.6;
    }

    .back-footer {
      position: absolute;
      bottom: 10px;
      left: 0;
      width: 100%;
      text-align: center;
      border-top: 1px solid rgba(255,149,0,0.12);
      padding-top: 8px;
    }

    .back-footer-p {
      font-size: 7px;
      color: #8a9bb0;
      line-height: 1.4;
      margin-bottom: 2px;
    }

    .back-footer-reg {
      font-size: 8px;
      color: rgba(255,149,0,0.6);
      letter-spacing: 1px;
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
        <div class="header-text-h1">{{ $companyName ?? 'SURYAMITRA' }}</div>
        <div class="header-text-p">Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Tech Agency' }}</div>
      </div>
    </div>

    <div class="photo-section-bg"></div>
    <div class="photo-section-curve"></div>

    <div class="photo-holder-outer">
      <div class="photo-holder-inner">
        @if($profilePhotoBase64)
          <img src="{{ $profilePhotoBase64 }}" class="profile-photo" alt="Photo">
        @else
          <div class="initials-avatar">{{ $initials ?? 'SM' }}</div>
        @endif
      </div>
    </div>

    <div class="role-badge-box">
      <div class="role-badge">{{ $designation ?? 'Administrator' }}</div>
    </div>

    <div class="name-h2">{{ $user->name ?? 'User Name' }}</div>

    <div class="divider-line"></div>
    <div class="divider-dot"></div>

    <table class="info-table">
      <tr>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Employee ID</div>
          <div class="info-val info-val-gold">{{ $cardNumber ?? 'N/A' }}</div>
        </td>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Joining Date</div>
          <div class="info-val">{{ $joiningDate }}</div>
        </td>
      </tr>
      <tr>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Date of Birth</div>
          <div class="info-val">{{ $dob }}</div>
        </td>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Contact</div>
          <div class="info-val">{{ $mobile }}</div>
        </td>
      </tr>
      <tr>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Father's Name</div>
          <div class="info-val" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $user->father_name ?? 'N/A' }}</div>
        </td>
        <td class="info-td">
          <div class="info-td-border"></div>
          <div class="info-label">Address</div>
          <div class="info-val" style="font-size: 8px;">{{ $address }}</div>
        </td>
      </tr>
    </table>

    <div class="barcode-area">
      @if($barcodeBase64)
        <img src="{{ $barcodeBase64 }}" class="barcode-img" alt="Barcode">
      @endif
      <div class="emp-id-label">{{ $cardNumber ?? '' }}</div>
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
        <div class="back-text-h2">{{ $companyName ?? 'ANDLEEB CLUSTER' }}</div>
        <div class="back-text-p">Affiliated with: {{ $globalAffiliatedPartner ?? 'Malik Surya Tech Agency' }}</div>
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
        THIS IDENTITY INSTRUMENT IS ISSUED BY <br>
        <strong style="color: #FFF;">{{ $companyName ?? 'Suryamitra Solar Infrastructure' }}</strong>.<br>
        ISSUED FOR SECURE ACCESS ONLY.<br><br>
        IF FOUND, PLEASE RETURN TO A REGIONAL FACILITY OR THE RESIDENCY ROAD HQ.
      </div>
    </div>

    <div class="emergency-box">
      <div class="emergency-pill">EMERGENCY: {{ $companyEmergency ?? '9906766655' }}</div>
    </div>

    <table class="sig-table">
      <tr>
        <td class="sig-td" style="vertical-align: bottom;">
          <!-- Left side signature / holder signature -->
          <div class="sig-line"></div>
          <div class="sig-label">HOLDER'S SIGNATURE</div>
        </td>
        <td class="sig-td" style="vertical-align: bottom;">
          <!-- Right side signature / authorizer -->
          @if($sealBase64)
            <img src="{{ $sealBase64 }}" class="seal-img" alt="Seal">
          @endif
          @if($sigBase64)
            <img src="{{ $sigBase64 }}" class="sig-img" alt="Signature">
          @endif
          <div class="sig-line"></div>
          <div class="sig-label">AUTHORIZED SIGNATORY</div>
        </td>
      </tr>
    </table>

    <div class="back-footer">
      <div class="back-footer-p">
        {{ $companyAddress ?? 'Srinagar, Jammu & Kashmir' }}
      </div>
      <div class="back-footer-p">
        Phone: {{ $companyPhone ?? '+91 99067 66655' }} &nbsp; | &nbsp; Email: {{ $companyEmail ?? 'info@suryamitra.in' }}
      </div>
      <div class="back-footer-reg">REG NO: {{ $globalRegNo ?? '123456789' }}</div>
    </div>

  </div>

</body>
</html>
