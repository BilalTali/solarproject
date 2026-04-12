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
@endphp<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><style>
@page { size: 570pt 500pt; margin: 0; }
html, body { margin: 0; padding: 0; width: 570pt; height: 500pt; background: #0d1b2a; font-family: Helvetica, Arial, sans-serif; overflow: hidden; line-height: 1; }
.main-table { width: 560pt; height: 490pt; border-collapse: separate; border-spacing: 12pt 25pt; margin: 0; padding: 0; }
.card-cell { width: 255pt; height: 430pt; vertical-align: top; padding: 0; position: relative; }
.card-box { width: 255pt; height: 430pt; border-radius: 15pt; overflow: hidden; position: relative; box-sizing: border-box; border: 1pt solid #ff9500; }
.front-bg { background: #f5f3ee; width: 100%; height: 100%; position: absolute; top: 0; left: 0; }
.navy-header { position: absolute; top: 0; left: 0; width: 100%; height: 155pt; background: #04111F; z-index: 1; }
.gold-line { position: absolute; top: 153pt; left: 0; width: 100%; height: 2pt; background: #FF9500; z-index: 3; }
.curve-overlay { position: absolute; top: 130pt; left: -20pt; width: 295pt; height: 45pt; background: #f5f3ee; border-radius: 100% 100% 0 0; z-index: 2; }
.logo-area { position: absolute; top: 15pt; left: 15pt; z-index: 10; }
.logo-box { width: 34pt; height: 34pt; background: #FF9500; border-radius: 8pt; text-align: center; }
.company-header { position: absolute; top: 16pt; left: 58pt; width: 180pt; z-index: 10; }
.company-header h1 { color: #FFF; font-size: 8pt; margin: 0; font-weight: bold; text-transform: uppercase; line-height: 1.1; }
.company-header p { color: #FF9500; font-size: 6.5pt; margin: 2pt 0 0 0; font-weight: bold; }
.photo-area { position: absolute; top: 40pt; left: 87.5pt; width: 80pt; height: 80pt; z-index: 20; }
.photo-ring { width: 80pt; height: 80pt; border-radius: 40pt; background: #FF9500; padding: 3pt; box-sizing: border-box; }
.photo-img { width: 74pt; height: 74pt; border-radius: 37pt; border: 2pt solid #04111F; background: #0a1f35; display: block; }
.badge { position: absolute; top: 125pt; left: 67.5pt; width: 120pt; background: #FF9500; color: #04111F; text-align: center; padding: 4.5pt 0; border-radius: 15pt; font-size: 8pt; font-weight: bold; text-transform: uppercase; z-index: 30; letter-spacing: 0.5pt; }
.name-text { position: absolute; top: 170pt; left: 0; width: 100%; text-align: center; font-size: 14pt; font-weight: bold; color: #04111F; text-transform: uppercase; z-index: 10; }
.divider-container { position: absolute; top: 195pt; left: 0; width: 100%; text-align: center; z-index: 10; }
.divider { display: inline-block; width: 215pt; height: 1pt; background: #FF9500; opacity: 0.35; position: relative; }
.dot { position: absolute; top: -2.5pt; left: 105pt; width: 5pt; height: 5pt; background: #FF9500; border-radius: 2.5pt; }
.info-grid { position: absolute; top: 210pt; left: 15pt; width: 225pt; z-index: 10; border-collapse: separate; border-spacing: 0 4pt; }
.info-cell { width: 108pt; background: #FFF; border: 0.5pt solid rgba(4,17,31,0.1); border-left: 2pt solid #FF9500; border-radius: 5pt; padding: 4pt 7pt; vertical-align: top; }
.lbl { color: #8a9bb0; font-size: 6pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2pt; margin-top: 0; }
.val { color: #04111F; font-size: 7.5pt; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1; }
.val-gold { color: #FF9500; }
.barcode-area { position: absolute; top: 345pt; left: 15pt; width: 225pt; z-index: 10; }
.barcode-img { height: 24pt; width: 135pt; float: left; }
.barcode-text { float: right; color: #8a9bb0; font-size: 8pt; margin-top: 10pt; font-weight: bold; }
.footer-line { position: absolute; top: 395pt; left: 15pt; width: 225pt; height: 0.5pt; background: #FF9500; opacity: 0.2; z-index: 10; }
.footer-text { position: absolute; top: 405pt; left: 15pt; width: 225pt; font-size: 7.5pt; font-weight: bold; z-index: 10; }
.f-left { float: left; color: #FF9500; text-transform: uppercase; }
.f-right { float: right; color: #8a9bb0; font-weight: normal; }
.v-dot { display: inline-block; width: 3.5pt; height: 3.5pt; border-radius: 1.75pt; background: #28a745; margin-right: 4pt; margin-bottom: 1pt; vertical-align: middle; }
.back-bg { background: #04111F; width: 100%; height: 100%; position: absolute; top: 0; left: 0; }
.back-header { position: absolute; top: 15pt; left: 15pt; width: 225pt; padding-bottom: 7pt; border-bottom: 0.5pt solid rgba(255,149,0,0.15); }
.b-logo-box { float: left; width: 30pt; height: 30pt; background: #FF9500; border-radius: 6pt; text-align: center; }
.b-brand { float: left; margin-left: 10pt; width: 180pt; }
.b-brand h2 { color: #FF9500; font-size: 10pt; margin: 0; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt; }
.b-brand p { color: #8a9bb0; font-size: 6.5pt; margin: 1pt 0 0 0; font-weight: bold; text-transform: uppercase; }
.qr-section { position: absolute; top: 70pt; left: 0; width: 100%; text-align: center; }
.qr-box { display: inline-block; background: #FFF; padding: 9pt; border-radius: 10pt; border: 2pt solid #FF9500; }
.qr-img { width: 85pt; height: 85pt; }
.qr-lbl { color: #8a9bb0; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; margin-top: 7pt; letter-spacing: 0.5pt; }
.notice-box { position: absolute; top: 205pt; left: 22.5pt; width: 210pt; background: rgba(255,149,0,0.04); border: 0.5pt solid rgba(255,149,0,0.15); border-radius: 8pt; padding: 9pt; text-align: center; }
.notice-box p { color: #c8d8e8; font-size: 7.5pt; line-height: 1.4; margin: 0; }
.notice-box strong { color: #FFF; }
.emergency-pill { position: absolute; top: 275pt; left: 47.5pt; width: 160pt; border: 1.2pt solid #FF9500; border-radius: 18pt; padding: 5pt 0; text-align: center; color: #FF9500; font-size: 9pt; font-weight: bold; letter-spacing: 0.5pt; }
.sig-container { position: absolute; top: 325pt; left: 15pt; width: 225pt; padding-top: 8pt; border-top: 0.5pt solid rgba(255,149,0,0.15); }
.sig-box { width: 100pt; float: left; text-align: center; position: relative; }
.sig-img-wrap { height: 26pt; display: block; position: relative; margin-bottom: 3pt; }
.sig-img { height: 20pt; vertical-align: middle; }
.seal-img { height: 35pt; position: absolute; left: 32pt; top: -7pt; opacity: 0.35; }
.sig-line { width: 100%; height: 0.5pt; background: #FF9500; opacity: 0.35; }
.sig-lbl { font-size: 4.5pt; color: #8a9bb0; text-transform: uppercase; margin-top: 2pt; font-weight: bold; }
.back-footer { position: absolute; bottom: 15pt; left: 0; width: 100%; text-align: center; color: #c8d8e8; font-size: 8pt; line-height: 1.4; z-index: 10; }
.reg-no-footer { color: #FF9500; font-weight: bold; font-size: 9.5pt; margin-bottom: 2pt; letter-spacing: 0.2pt; }
</style></head><body><table class="main-table"><tr><td class="card-cell"><div class="card-box"><div class="front-bg"><div class="navy-header"><div class="logo-area"><div class="logo-box">@if($logoBase64)<img src="{{ $logoBase64 }}" style="width: 20pt; height: 20pt; margin-top: 7pt;">@endif</div></div><div class="company-header"><h1>{{ $companyName ?? 'ANDLEEB CLUSTER OF SERVICES' }}</h1><p>Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Tech Agency' }}</p></div><div class="gold-line"></div></div><div class="curve-overlay"></div><div class="photo-area"><div class="photo-ring">@if($profilePhotoBase64)<img src="{{ $profilePhotoBase64 }}" class="photo-img">@else<div class="photo-img" style="text-align: center; line-height: 74pt; color: #FF9500; font-size: 24pt; font-weight: bold;">{{ $initials ?? 'SM' }}</div>@endif</div></div><div class="badge">{{ $designation ?? 'Administrator' }}</div><div class="name-text">{{ $user->name ?? 'User Name' }}</div><div class="divider-container"><div class="divider"><div class="dot"></div></div></div><table class="info-grid"><tr><td class="info-cell"><div class="lbl">Employee ID</div><div class="val val-gold">{{ $cardNumber ?? 'ADM-0002' }}</div></td><td class="info-cell" style="padding-left: 5pt;"><div class="lbl">Date of Birth</div><div class="val">{{ $dob ?? '02 Feb 1980' }}</div></td></tr><tr><td class="info-cell"><div class="lbl">Father's Name</div><div class="val">{{ $user->father_name ?? 'Habib Ullah Tali' }}</div></td><td class="info-cell" style="padding-left: 5pt;"><div class="lbl">Joining Date</div><div class="val">{{ $joiningDate ?? '11 Apr 2026' }}</div></td></tr><tr><td class="info-cell"><div class="lbl">Contact</div><div class="val">{{ $mobile ?? '9797287817' }}</div></td><td class="info-cell" style="padding-left: 5pt;"><div class="lbl">Address</div><div class="val" style="font-size: 6.5pt; white-space: normal;">{{ $address ?? 'Pampore, Pulwama' }}</div></td></tr></table><div class="barcode-area">@if($barcodeBase64)<img src="{{ $barcodeBase64 }}" class="barcode-img">@endif<div class="barcode-text">{{ $cardNumber ?? 'ADM-0002' }}</div></div><div class="footer-line"></div><div class="footer-text"><div class="f-left"><span class="v-dot"></span>Verified Identity</div><div class="f-right">{{ $companyWebsite ?? 'andleebsurya.in' }}</div></div></div></div></td><td class="card-cell"><div class="card-box"><div class="back-bg"><div class="back-header"><div class="b-logo-box">@if($globalLogoBase64)<img src="{{ $globalLogoBase64 }}" style="width: 18pt; height: 18pt; margin-top: 6pt;">@endif</div><div class="b-brand"><h2>{{ $globalName ?? 'MALIK SURYA' }}</h2><p>REG NO: {{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</p></div></div><div class="qr-section"><div class="qr-box">@if($qrBase64)<img src="{{ $qrBase64 }}" class="qr-img">@endif</div><div class="qr-lbl">Scan to Verify</div></div><div class="notice-box"><p>This identity instrument is issued by<br><strong>{{ $companyName ?? 'Andleeb Cluster of Services' }}</strong><br>Issued for secure access only.<br>If found, please return to a regional facility<br>or the <strong>Residency Road HQ.</strong></p></div><div class="emergency-pill">Emergency: {{ $companyEmergency ?? '9906766655' }}</div><div class="sig-container"><div class="sig-box"><div class="sig-img-wrap">@if($sealBase64)<img src="{{ $sealBase64 }}" class="seal-img">@endif</div><div class="sig-line"></div><div class="sig-lbl">Issuing Authority<br>Principal Seal</div></div><div class="sig-box" style="float: right;"><div class="sig-img-wrap">@if($sigBase64)<img src="{{ $sigBase64 }}" class="sig-img">@endif</div><div class="sig-line"></div><div class="sig-lbl">Verified By<br>{{ $icardVerifiedBy ?? 'CHIEF OPERATIONS OFFICER' }}</div></div></div><div class="back-footer"><div class="reg-no-footer">{{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</div><div style="margin-bottom: 2pt;">{{ $companyAddress ?? 'Srinagar, Jammu & Kashmir' }}</div><div style="font-size: 7.5pt; color: #8a9bb0;">+91-{{ $companyPhone ?? '9797287817' }} | {{ $companyEmail ?? 'info@andleebsurya.in' }}</div></div></div></div></td></tr></table></body></html>
