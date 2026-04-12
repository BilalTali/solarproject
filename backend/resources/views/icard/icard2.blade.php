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
  <style>
    /* Reset & Fonts */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body, p, span, div, strong, table, td, tr {
      font-family: Helvetica, Arial, sans-serif !important;
    }

    @page { 
      size: 360px 620px;
      margin: 0; 
    }

    body {
      background: #FFFFFF;
      margin: 0; 
      padding: 0;
    }

    .pdf-page {
      width: 360px;
      height: 620px;
      page-break-after: always;
      position: relative;
      overflow: hidden;
    }
    .pdf-page:last-child {
      page-break-after: avoid;
    }

    /* ─── FRONT CARD ─── */
    .front-bg {
      position: absolute;
      top: 0; left: 0;
      width: 360px; height: 620px;
      background: #f5f3ee;
      z-index: 1;
    }
    .front-frame {
      position: absolute;
      top: 6px; left: 6px;
      width: 348px; height: 608px;
      border: 1px solid rgba(255,149,0,0.25);
      border-radius: 15px;
      z-index: 100;
    }

    .navy-header {
      position: absolute;
      top: 0; left: 0;
      width: 360px; height: 260px;
      background: #04111F;
      z-index: 2;
    }
    .navy-header::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 3px;
      background: #FF9500; /* gold-line substitute */
    }

    .curve {
      position: absolute;
      top: 240px; 
      left: -20px;
      width: 400px;
      height: 40px;
      background: #f5f3ee;
      border-radius: 50% 50% 0 0 / 100% 100% 0 0;
      z-index: 5;
    }

    /* Photo Ring */
    .photo-center {
      position: absolute;
      top: 90px;
      left: 125px;
      width: 110px;
      height: 110px;
      z-index: 10;
    }
    .ring-bg {
      position: absolute;
      top: 0; left: 0;
      width: 110px; height: 110px;
      border-radius: 50%;
      background: #FF9500;
    }

    /* Photo */
    .photo-img {
      position: absolute;
      top: 4px; left: 4px;
      width: 102px; height: 102px;
      border-radius: 50%;
      border: 3px solid #04111F;
      z-index: 11;
      background: #0a1f35;
    }

    /* Role Badge */
    .badge {
      position: absolute;
      top: 212px;
      left: 100px;
      width: 160px;
      text-align: center;
      background: #FF9500;
      color: #04111F;
      font-size: 11px;
      font-weight: bold;
      padding: 5px 0;
      border-radius: 20px;
      z-index: 50;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Name Area */
    .name-text {
      position: absolute;
      top: 265px;
      left: 0;
      width: 360px;
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      color: #04111F;
      text-transform: uppercase;
      z-index: 10;
    }

    /* Divider */
    .divider-wrap {
      position: absolute;
      top: 300px;
      left: 30px;
      width: 300px;
      height: 10px;
      z-index: 10;
    }
    .line { height: 1px; background: rgba(255,149,0,0.4); margin-top: 5px; }
    .dot { position: absolute; left: 147px; top: 3px; width: 5px; height: 5px; border-radius: 50%; background: #FF9500; }

    /* Grid */
    .grid-wrap {
      position: absolute;
      top: 325px;
      left: 20px;
      width: 320px;
      z-index: 10;
    }
    .grid-row { clear: both; margin-bottom: 10px; width: 100%; height: 45px; }
    .grid-cell { 
      float: left; width: 152px; height: 42px; background: #FFF; border: 1px solid rgba(4,17,31,0.07); 
      border-radius: 8px; margin-right: 8px; border-left: 3px solid #FF9500; padding: 4px 8px;
    }
    .grid-cell:last-child { margin-right: 0; }
    .lbl { color: #8a9bb0; font-size: 8px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
    .val { color: #04111F; font-size: 11.5px; font-weight: bold; }
    .val-gold { color: #FF9500; font-size: 14px; }

    /* Barcode Area */
    .barcode-area {
      position: absolute;
      top: 510px;
      left: 22px;
      width: 316px;
      height: 40px;
      z-index: 10;
    }
    .barcode-img { float: left; width: 180px; height: 32px; }
    .barcode-val { float: right; color: #8a9bb0; font-size: 10px; padding-top: 10px; }

    /* Footer */
    .footer-bar {
      position: absolute;
      top: 580px;
      left: 0;
      width: 360px;
      height: 40px;
      background: #04111F;
      padding: 12px 22px;
      z-index: 10;
    }
    .footer-left { float: left; color: #FF9500; font-size: 10px; font-weight: bold; text-transform: uppercase; }
    .footer-right { float: right; color: #8a9bb0; font-size: 9px; }
    .pulse-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #22c55e; margin-right: 5px; }

    /* ─── BACK CARD ─── */
    .back-bg {
      position: absolute;
      top: 0; left: 0;
      width: 360px; height: 620px;
      background: #04111F;
      z-index: 1;
    }
    .back-header {
      position: absolute;
      top: 25px; left: 25px;
      width: 310px; height: 50px;
      border-bottom: 1px solid rgba(255,149,0,0.15);
      z-index: 10;
    }
    .back-logo { float: left; width: 40px; height: 40px; background: #FF9500; border-radius: 10px; text-align: center; }
    .back-brand { float: left; margin-left: 12px; }
    .back-brand h2 { color: #FF9500; font-size: 14px; font-weight: bold; }
    .back-brand p { color: #8a9bb0; font-size: 8px; margin-top: 2px; }

    .qr-area {
      position: absolute;
      top: 100px;
      left: 0;
      width: 360px;
      text-align: center;
      z-index: 10;
    }
    .qr-box { 
      display: inline-block; background: #FFF; padding: 12px; border-radius: 15px; 
      border: 3px solid #FF9500; /* simulating custom frame */
    }
    .qr-label { color: #8a9bb0; font-size: 9px; text-transform: uppercase; margin-top: 8px; letter-spacing: 1px; }

    .notice-box {
      position: absolute;
      top: 310px;
      left: 30px;
      width: 300px;
      background: rgba(255,149,0,0.07);
      border: 1px solid rgba(255,149,0,0.18);
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      z-index: 10;
    }
    .notice-box p { font-size: 10px; color: #c8d8e8; line-height: 1.6; }
    .notice-box strong { color: #FFF; }

    .emergency-btn {
      position: absolute;
      top: 420px;
      left: 70px;
      width: 220px;
      border: 1.5px solid #FF9500;
      border-radius: 30px;
      padding: 8px;
      text-align: center;
      color: #FF9500;
      font-size: 12px;
      font-weight: bold;
      z-index: 10;
    }

    .sig-footer {
      position: absolute;
      top: 480px;
      left: 20px;
      width: 320px;
      height: 100px;
      z-index: 10;
      border-top: 1px solid rgba(255,149,0,0.15);
      padding-top: 15px;
    }
    .sig-block { width: 100%; text-align: center; }
    .sig-line { width: 100px; height: 1px; background: rgba(255,149,0,0.4); margin: 0 auto 5px; }
    .sig-img-wrap { position: relative; height: 40px; width: 120px; margin: 0 auto; }
    .sig-img { position: absolute; left: 10px; top: 5px; height: 30px; z-index: 10; }
    .seal-img { position: absolute; left: 35px; top: -5px; height: 45px; opacity: 0.6; z-index: 5; }

    .back-final {
      position: absolute;
      top: 575px;
      width: 360px;
      text-align: center;
      color: #8a9bb0;
      font-size: 8px;
      z-index: 10;
    }
  </style>
</head>
<body>

  <!-- FRONT -->
  <div class="pdf-page">
    <div class="front-bg">
      <div class="front-frame"></div>
      
      <div class="navy-header">
         <!-- Header Content -->
         <div style="position: absolute; top: 18px; left: 22px; width: 316px;">
            <div style="float: left; width: 44px; height: 44px; background: #FF9500; border-radius: 10px; text-align: center;">
               @if($logoBase64)
                 <img src="{{ $logoBase64 }}" style="width: 26px; height: 26px; margin-top: 9px;">
               @endif
            </div>
            <div style="float: left; margin-left: 12px; padding-top: 4px;">
               <h1 style="color: #FFF; font-size: 10.5px; font-weight: bold;">{{ $companyName ?? 'ANDLEEB CLUSTER OF SERVICES' }}</h1>
               <p style="color: #FF9500; font-size: 9px; margin-top: 2px;">Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Agency' }}</p>
            </div>
         </div>
      </div>

      <div class="curve"></div>

      <div class="photo-center">
         <div class="ring-bg"></div>
         @if($profilePhotoBase64)
           <img src="{{ $profilePhotoBase64 }}" class="photo-img">
         @else
           <div class="photo-img" style="text-align: center; line-height: 102px; color: #FF9500; font-size: 32px; font-weight: bold;">{{ $initials ?? 'SM' }}</div>
         @endif
      </div>

      <div class="badge">{{ $designation ?? 'Administrator' }}</div>

      <div class="name-text">{{ $user->name ?? 'User Name' }}</div>

      <div class="divider-wrap">
         <div class="line"></div>
         <div class="dot"></div>
      </div>

      <div class="grid-wrap">
         <div class="grid-row">
            <div class="grid-cell">
               <div class="lbl">Employee ID</div>
               <div class="val val-gold">{{ $cardNumber ?? 'ADM-0002' }}</div>
            </div>
            <div class="grid-cell">
               <div class="lbl">Date of Birth</div>
               <div class="val">{{ $dob ?? '02 Feb 1980' }}</div>
            </div>
         </div>
         <div class="grid-row">
            <div class="grid-cell">
               <div class="lbl">Father's Name</div>
               <div class="val" style="overflow: hidden; white-space: nowrap;">{{ $user->father_name ?? 'Habib Ullah' }}</div>
            </div>
            <div class="grid-cell">
               <div class="lbl">Joining Date</div>
               <div class="val">{{ $joiningDate ?? '11 Apr 2026' }}</div>
            </div>
         </div>
         <div class="grid-row">
            <div class="grid-cell">
               <div class="lbl">Contact</div>
               <div class="val">{{ $mobile ?? '9797287817' }}</div>
            </div>
            <div class="grid-cell">
               <div class="lbl">Address</div>
               <div class="val" style="font-size: 9px;">{{ $address ?? 'Pampore, Pulwama' }}</div>
            </div>
         </div>
      </div>

      <div class="barcode-area">
         @if($barcodeBase64)
           <img src="{{ $barcodeBase64 }}" class="barcode-img">
         @endif
         <div class="barcode-val">{{ $cardNumber ?? 'ADM-0002' }}</div>
      </div>

      <div class="footer-bar">
         <div class="footer-left"><span class="pulse-dot"></span> Verified Identity</div>
         <div class="footer-right">{{ $companyWebsite ?? 'andleebsurya.in' }}</div>
      </div>
    </div>
  </div>

  <!-- BACK -->
  <div class="pdf-page">
    <div class="back-bg">
      <div class="front-frame"></div> <!-- reuse border frame -->

      <div class="back-header">
         <div class="back-logo">
            @if($globalLogoBase64)
               <img src="{{ $globalLogoBase64 }}" style="width: 24px; height: 24px; margin-top: 8px;">
            @endif
         </div>
         <div class="back-brand">
            <h2>{{ $globalName ?? 'MALIK SURYA' }}</h2>
            <p>REG NO: {{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</p>
         </div>
      </div>

      <div class="qr-area">
         <div class="qr-box">
            @if($qrBase64)
              <img src="{{ $qrBase64 }}" style="width: 120px; height: 120px;">
            @endif
         </div>
         <div class="qr-label">Scan to Verify</div>
      </div>

      <div class="notice-box">
         <p>
           This identity instrument is issued by<br>
           <strong>{{ $companyName ?? 'Andleeb Cluster of Services' }}</strong><br>
           Issued for secure access only.<br>
           If found, please return to a regional facility<br>
           or the <strong>Residency Road HQ.</strong>
         </p>
      </div>

      <div class="emergency-btn">Emergency: {{ $companyEmergency ?? '9906766655' }}</div>

      <div class="sig-footer">
         <div class="sig-block">
            <div class="sig-img-wrap">
               @if($sigBase64)
                 <img src="{{ $sigBase64 }}" class="sig-img">
               @endif
               @if($sealBase64)
                 <img src="{{ $sealBase64 }}" class="seal-img">
               @endif
            </div>
            <div class="sig-line"></div>
            <p>Verified By<br>Chief Operations Officer</p>
         </div>
      </div>

      <div class="back-final">
         <span style="color: rgba(255,149,0,0.6);">{{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</span><br>
         {{ $companyAddress ?? 'Srinagar, J&K' }}<br>
         {{ $companyPhone ?? '99067 66655' }} | {{ $companyEmail ?? 'info@andleebsurya.in' }}
      </div>
    </div>
  </div>

</body>
</html>
