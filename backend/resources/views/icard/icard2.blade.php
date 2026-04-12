@php/** @var string|null $logoBase64 */ /** @var string|null $sigBase64 */ /** @var string|null $sealBase64 */ /** @var string|null $globalLogoBase64 */ /** @var string|null $globalSigBase64 */ /** @var string|null $globalAffiliatedPartner */ /** @var string|null $globalRegNo */ /** @var string|null $globalName */ /** @var string|null $profilePhotoBase64 */ /** @var string|null $initials */ /** @var string|null $designation */ /** @var string|null $cardNumber */ /** @var string|null $dob */ /** @var string|null $joiningDate */ /** @var string|null $mobile */ /** @var string|null $address */ /** @var string|null $barcodeBase64 */ /** @var string|null $companyWebsite */ /** @var string|null $companyAffiliatedWith */ /** @var string|null $companyRegNo */ /** @var string|null $qrBase64 */ /** @var string|null $companyEmergency */ /** @var string|null $icardVerifiedBy */ /** @var string|null $companyAddress */ /** @var string|null $companyPhone */ /** @var string|null $companyEmail */@endphp<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><style>
    html, body {
      margin: 0; padding: 0;
      width: 570pt; height: 500pt;
      overflow: hidden;
      background: #0d1b2a;
      font-family: Helvetica, sans-serif;
    }

    @page { 
      size: 570pt 500pt;
      margin: 0; 
    }

    .main-table {
      width: 570pt;
      height: 500pt;
      border-collapse: separate;
      border-spacing: 12pt;
      margin: 0;
      padding: 0;
    }

    .card-cell {
      width: 270pt;
      padding: 0;
      vertical-align: top;
    }

    .card-box {
      width: 270pt;
      height: 445pt;
      position: relative;
      background: #FFFFFF;
      overflow: hidden;
      border-radius: 12pt;
    }

    /* ─── FRONT CARD ─── */
    .front-bg {
      position: absolute;
      top: 0; left: 0;
      width: 270pt; height: 445pt;
      background: #f5f3ee;
    }
    .front-frame {
      position: absolute;
      top: 5pt; left: 5pt;
      width: 260pt; height: 435pt;
      border: 1pt solid rgba(255,149,0,0.2);
      border-radius: 10pt;
    }

    .navy-header {
      position: absolute;
      top: 0; left: 0;
      width: 270pt; height: 155pt;
      background: #04111F;
    }
    .gold-line {
      position: absolute;
      top: 153pt; left: 0; width: 270pt; height: 2.5pt;
      background: #FF9500;
    }

    .curve-mask {
      position: absolute;
      top: 135pt; left: -15pt; width: 300pt; height: 40pt;
      background: #f5f3ee;
      border-radius: 100% 100% 0 0;
    }

    .photo-area {
      position: absolute;
      top: 40pt; left: 95pt;
      width: 80pt; height: 80pt;
    }
    .photo-ring {
      width: 80pt; height: 80pt;
      border-radius: 40pt;
      background: #FF9500;
      padding: 2.5pt;
    }
    .photo-img {
      width: 75pt; height: 75pt;
      border-radius: 37.5pt;
      border: 2pt solid #04111F;
      background: #0a1f35;
    }

    .badge {
      position: absolute;
      top: 120pt;
      left: 75pt;
      width: 120pt;
      text-align: center;
      background: #FF9500;
      color: #04111F;
      font-size: 8pt;
      font-weight: bold;
      padding: 4.5pt 0;
      border-radius: 15pt;
      text-transform: uppercase;
    }

    .name-text {
      position: absolute;
      top: 165pt;
      left: 0; width: 270pt;
      text-align: center;
      font-size: 14pt;
      font-weight: bold;
      color: #04111F;
      text-transform: uppercase;
    }

    .divider {
      position: absolute;
      top: 188pt; left: 20pt; width: 230pt;
      height: 1pt; background: rgba(255,149,0,0.4);
    }
    .dot {
      position: absolute;
      top: 186pt; left: 133pt; width: 4pt; height: 4pt;
      background: #FF9500; border-radius: 2pt;
    }

    .info-table {
      position: absolute;
      top: 198pt; left: 15pt; width: 240pt;
      border-collapse: separate;
      border-spacing: 5pt;
    }
    .info-cell {
      width: 110pt; height: 32pt;
      background: #FFF; border: 0.5pt solid rgba(4,17,31,0.06);
      border-left: 2.5pt solid #FF9500; border-radius: 6pt;
      padding: 4pt 7pt;
    }
    .lbl { color: #8a9bb0; font-size: 6pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2pt; }
    .val { color: #04111F; font-size: 8pt; font-weight: bold; }
    .val-gold { color: #FF9500; font-size: 10pt; }

    .sig-row-front {
      position: absolute;
      top: 300pt; left: 15pt; width: 240pt;
    }
    .sig-box { width: 110pt; float: left; text-align: center; }
    .sig-img-wrap { height: 26pt; position: relative; margin-bottom: 0px; }
    .sig-img { height: 20pt; vertical-align: middle; }
    .seal-img { height: 35pt; position: absolute; left: 50%; transform: translateX(-50%); top: -5pt; opacity: 0.5; }
    .sig-line { width: 100%; height: 0.5pt; background: #FF9500; opacity: 0.4; }
    .sig-lbl { font-size: 5.5pt; color: #8a9bb0; text-transform: uppercase; margin-top: 2pt; }

    .barcode-area {
      position: absolute;
      top: 355pt; left: 15pt; width: 240pt;
    }
    .barcode-img { height: 24pt; width: 150pt; }
    .barcode-val { float: right; color: #8a9bb0; font-size: 7.5pt; margin-top: 6pt; }

    .footer-bar {
      position: absolute;
      top: 405pt; left: 0; width: 270pt; height: 28pt;
      background: #04111F; color: #FF9500;
      padding: 8pt 15pt;
    }
    .f-left { float: left; font-size: 7.5pt; font-weight: bold; }
    .f-right { float: right; font-size: 6.5pt; color: #8a9bb0; }

    /* ─── BACK CARD ─── */
    .back-bg {
      position: absolute;
      top: 0; left: 0;
      width: 270pt; height: 445pt;
      background: #04111F;
    }
    .back-header {
      position: absolute;
      top: 20pt; left: 20pt; width: 230pt;
      border-bottom: 0.5pt solid rgba(255,149,0,0.15);
      padding-bottom: 5pt;
    }
    .b-logo { float: left; width: 30pt; height: 30pt; background: #FF9500; border-radius: 8pt; text-align: center; }
    .b-brand { float: left; margin-left: 10pt; }
    .b-brand h2 { color: #FF9500; font-size: 11pt; margin: 0; }
    .b-brand p { color: #8a9bb0; font-size: 6.5pt; margin: 0; }

    .qr-area {
      position: absolute;
      top: 70pt; left: 0; width: 270pt;
      text-align: center;
    }
    .qr-box { display: inline-block; background: #FFF; padding: 10pt; border-radius: 12pt; border: 2.5pt solid #FF9500; }
    .qr-img { width: 90pt; height: 90pt; }
    .qr-lbl { color: #8a9bb0; font-size: 7.5pt; text-transform: uppercase; margin-top: 6pt; }

    .notice-box {
      position: absolute;
      top: 200pt; left: 20pt; width: 230pt;
      background: rgba(255,149,0,0.08);
      border: 0.5pt solid rgba(255,149,0,0.18);
      border-radius: 8pt; padding: 10pt;
      text-align: center;
    }
    .notice-box p { color: #c8d8e8; font-size: 7.5pt; line-height: 1.5; margin: 0; }
    .notice-box strong { color: #FFF; }

    .emergency-pill {
      position: absolute;
      top: 265pt; left: 55pt; width: 160pt;
      border: 1.5pt solid #FF9500; border-radius: 20pt;
      padding: 6pt; text-align: center;
      color: #FF9500; font-size: 9pt; font-weight: bold;
    }

    .sig-row-back {
      position: absolute;
      top: 305pt; left: 15pt; width: 240pt;
      border-top: 0.5pt solid rgba(255,149,0,0.15);
      padding-top: 10pt;
    }

    .back-footer {
      position: absolute;
      top: 375pt; width: 270pt;
      text-align: center;
      color: #8a9bb0; font-size: 6.5pt; line-height: 1.4;
    }
  </style></head><body><table class="main-table"><tr><td class="card-cell"><div class="card-box"><div class="front-bg"><div class="front-frame"></div><div class="navy-header"><div style="position: absolute; top: 15pt; left: 15pt; width: 240pt;"><div style="float: left; width: 34pt; height: 34pt; background: #FF9500; border-radius: 8pt; text-align: center;">@if($logoBase64)<img src="{{ $logoBase64 }}" style="width: 20pt; height: 20pt; margin-top: 7pt;">@endif</div><div style="float: left; margin-left: 10pt; padding-top: 3pt;"><h1 style="color: #FFF; font-size: 8pt; margin: 0;">{{ $companyName ?? 'ANDLEEB CLUSTER OF SERVICES' }}</h1><p style="color: #FF9500; font-size: 7pt; margin: 2pt 0 0 0;">Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Agency' }}</p></div></div><div class="gold-line"></div></div><div class="curve-mask"></div><div class="photo-area"><div class="photo-ring">@if($profilePhotoBase64)<img src="{{ $profilePhotoBase64 }}" class="photo-img">@else<div class="photo-img" style="text-align: center; line-height: 75pt; color: #FF9500; font-size: 24pt; font-weight: bold;">{{ $initials ?? 'SM' }}</div>@endif</div></div><div class="badge">{{ $designation ?? 'Administrator' }}</div><div class="name-text">{{ $user->name ?? 'User Name' }}</div><div class="divider"></div><div class="dot"></div><table class="info-table"><tr><td class="info-cell"><div class="lbl">Employee ID</div><div class="val val-gold">{{ $cardNumber ?? 'ADM-0002' }}</div></td><td class="info-cell"><div class="lbl">Date of Birth</div><div class="val">{{ $dob ?? '02 Feb 1980' }}</div></td></tr><tr><td class="info-cell"><div class="lbl">Father's Name</div><div class="val" style="overflow: hidden; white-space: nowrap;">{{ $user->father_name ?? 'Habib Ullah' }}</div></td><td class="info-cell"><div class="lbl">Joining Date</div><div class="val">{{ $joiningDate ?? '11 Apr 2026' }}</div></td></tr><tr><td class="info-cell"><div class="lbl">Contact</div><div class="val">{{ $mobile ?? '9797287817' }}</div></td><td class="info-cell"><div class="lbl">Address</div><div class="val" style="font-size: 7pt;">{{ $address ?? 'Pampore, Pulwama' }}</div></td></tr></table><div class="sig-row-front"><div class="sig-box"><div class="sig-img-wrap">@if($sealBase64)<img src="{{ $sealBase64 }}" class="seal-img">@endif</div><div class="sig-line"></div><div class="sig-lbl">Principal Seal</div></div><div class="sig-box" style="float: right;"><div class="sig-img-wrap">@if($sigBase64)<img src="{{ $sigBase64 }}" class="sig-img">@endif</div><div class="sig-line"></div><div class="sig-lbl">Verified By</div></div></div><div class="barcode-area">@if($barcodeBase64)<img src="{{ $barcodeBase64 }}" class="barcode-img">@endif<div class="barcode-val">{{ $cardNumber ?? 'ADM-0002' }}</div></div><div class="footer-bar"><div class="f-left">Verified Identity</div><div class="f-right">{{ $companyWebsite ?? 'andleebsurya.in' }}</div></div></div></div></td><td class="card-cell"><div class="card-box"><div class="back-bg"><div class="front-frame"></div><div class="back-header"><div class="b-logo">@if($globalLogoBase64)<img src="{{ $globalLogoBase64 }}" style="width: 18pt; height: 18pt; margin-top: 6pt;">@endif</div><div class="b-brand"><h2>{{ $globalName ?? 'MALIK SURYA' }}</h2><p>REG NO: {{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</p></div></div><div class="qr-area"><div class="qr-box">@if($qrBase64)<img src="{{ $qrBase64 }}" class="qr-img">@endif</div><div class="qr-lbl">Scan to Verify</div></div><div class="notice-box"><p>This identity instrument is issued by<br><strong>{{ $companyName ?? 'Andleeb Cluster of Services' }}</strong><br>Issued for secure access only.<br>If found, please return to a regional facility<br>or the <strong>Residency Road HQ.</strong></p></div><div class="emergency-pill">Emergency: {{ $companyEmergency ?? '9906766655' }}</div><div class="sig-row-back"><div class="sig-box"><div class="sig-img-wrap">@if($sealBase64)<img src="{{ $sealBase64 }}" class="seal-img">@endif</div><div class="sig-line"></div><div class="sig-lbl">Issuing Authority</div></div><div class="sig-box" style="float: right;"><div class="sig-img-wrap">@if($sigBase64)<img src="{{ $sigBase64 }}" class="sig-img">@endif</div><div class="sig-line"></div><div class="sig-lbl">Verified By</div></div></div><div class="back-footer"><span style="color: rgba(255,149,0,0.6);">{{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</span><br>{{ $companyAddress ?? 'Srinagar, J&K' }}<br>{{ $companyPhone ?? '99067 66655' }} | {{ $companyEmail ?? 'info@andleebsurya.in' }}</div></div></div></td></tr></table></body></html>
