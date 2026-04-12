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
        @page { size: 570pt 500pt; margin: 0; }
        body { margin: 0; padding: 0; background: #0d1b2a; font-family: Helvetica, sans-serif; overflow: hidden; }

        /* General positioning */
        .card { position: absolute; width: 260pt; height: 440pt; border-radius: 15pt; overflow: hidden; }
        .front { top: 30pt; left: 15pt; background: #f5f3ee; border: 1pt solid rgba(255,149,0,0.3); }
        .back { top: 30pt; left: 295pt; background: #04111F; border: 1pt solid rgba(255,149,0,0.3); }

        /* Front specific */
        .navy-header { position: absolute; top: 0; left: 0; width: 100%; height: 160pt; background: #04111F; }
        .gold-line { position: absolute; top: 158pt; left: 0; width: 100%; height: 2.5pt; background: #FF9500; }
        .curve-overlay { position: absolute; top: 135pt; left: -20pt; width: 300pt; height: 45pt; background: #f5f3ee; border-radius: 100% 100% 0 0; }
        
        .logo-area { position: absolute; top: 15pt; left: 15pt; }
        .logo-box { width: 36pt; height: 36pt; background: #FF9500; border-radius: 8pt; text-align: center; }
        .company-header { position: absolute; top: 16pt; left: 60pt; width: 185pt; }
        .company-header h1 { color: #FFF; font-size: 8.5pt; margin: 0; font-weight: bold; text-transform: uppercase; line-height: 1.1; }
        .company-header p { color: #FF9500; font-size: 7pt; margin: 2pt 0 0 0; font-weight: bold; }

        .photo-area { position: absolute; top: 45pt; left: 90pt; width: 80pt; height: 80pt; z-index: 10; }
        .photo-ring { width: 80pt; height: 80pt; border-radius: 40pt; background: #FF9500; padding: 3pt; box-sizing: border-box; }
        .photo-img { width: 74pt; height: 74pt; border-radius: 37pt; border: 2pt solid #04111F; background: #0a1f35; display: block; }

        .badge { position: absolute; top: 130pt; left: 70pt; width: 120pt; background: #FF9500; color: #04111F; text-align: center; padding: 5pt 0; border-radius: 15pt; font-size: 8.5pt; font-weight: bold; text-transform: uppercase; z-index: 20; letter-spacing: 0.5pt; }

        .name-text { position: absolute; top: 175pt; left: 0; width: 100%; text-align: center; font-size: 15pt; font-weight: bold; color: #04111F; text-transform: uppercase; }
        .divider-container { position: absolute; top: 200pt; left: 0; width: 100%; text-align: center; }
        .divider { display: inline-block; width: 220pt; height: 1pt; background: rgba(255,149,0,0.4); position: relative; }
        .dot { position: absolute; top: -2pt; left: 108pt; width: 5pt; height: 5pt; background: #FF9500; border-radius: 2.5pt; }

        .info-grid { position: absolute; top: 215pt; left: 15pt; width: 230pt; }
        .info-cell { width: 110pt; background: #FFF; border: 0.5pt solid rgba(4,17,31,0.06); border-left: 2.5pt solid #FF9500; border-radius: 6pt; padding: 5pt 8pt; margin-bottom: 5pt; vertical-align: top; }
        .lbl { color: #8a9bb0; font-size: 6.5pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2pt; }
        .val { color: #04111F; font-size: 8.5pt; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .val-gold { color: #FF9500; }

        .barcode-area { position: absolute; top: 355pt; left: 20pt; width: 220pt; }
        .barcode-img { height: 26pt; width: 140pt; float: left; }
        .barcode-text { float: right; color: #8a9bb0; font-size: 8.5pt; margin-top: 10pt; font-weight: bold; }

        .footer-line { position: absolute; top: 405pt; left: 15pt; width: 230pt; height: 0.5pt; background: rgba(255,149,0,0.25); }
        .footer-text { position: absolute; top: 415pt; left: 15pt; width: 230pt; font-size: 8pt; font-weight: bold; }
        .f-left { float: left; color: #FF9500; text-transform: uppercase; }
        .f-right { float: right; color: #8a9bb0; font-weight: normal; }
        .v-dot { display: inline-block; width: 4pt; height: 4pt; border-radius: 2pt; background: #28a745; margin-right: 5pt; vertical-align: middle; margin-top: -2pt; }

        /* Back specific */
        .back-header { position: absolute; top: 20pt; left: 20pt; width: 220pt; padding-bottom: 8pt; border-bottom: 0.5pt solid rgba(255,149,0,0.15); }
        .b-logo-box { float: left; width: 32pt; height: 32pt; background: #FF9500; border-radius: 8pt; text-align: center; }
        .b-brand { float: left; margin-left: 12pt; width: 170pt; }
        .b-brand h2 { color: #FF9500; font-size: 11pt; margin: 0; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt; }
        .b-brand p { color: #8a9bb0; font-size: 7pt; margin: 2pt 0 0 0; font-weight: bold; text-transform: uppercase; }

        .qr-section { position: absolute; top: 80pt; left: 0; width: 100%; text-align: center; }
        .qr-box { display: inline-block; background: #FFF; padding: 10pt; border-radius: 12pt; border: 2pt solid #FF9500; }
        .qr-img { width: 90pt; height: 90pt; }
        .qr-lbl { color: #8a9bb0; font-size: 8pt; font-weight: bold; text-transform: uppercase; margin-top: 8pt; letter-spacing: 1pt; }

        .notice-box { position: absolute; top: 215pt; left: 25pt; width: 210pt; background: rgba(255,149,0,0.06); border: 0.5pt solid rgba(255,149,0,0.18); border-radius: 10pt; padding: 10pt; text-align: center; }
        .notice-box p { color: #c8d8e8; font-size: 8pt; line-height: 1.5; margin: 0; }
        .notice-box strong { color: #FFF; }

        .emergency-pill { position: absolute; top: 285pt; left: 50pt; width: 160pt; border: 1.5pt solid #FF9500; border-radius: 20pt; padding: 6pt 0; text-align: center; color: #FF9500; font-size: 9.5pt; font-weight: bold; letter-spacing: 0.5pt; }

        .sig-container { position: absolute; top: 335pt; left: 15pt; width: 230pt; padding-top: 10pt; border-top: 0.5pt solid rgba(255,149,0,0.15); }
        .sig-box { width: 105pt; float: left; text-align: center; position: relative; }
        .sig-img-wrap { height: 28pt; display: block; position: relative; margin-bottom: 4pt; }
        .sig-img { height: 22pt; vertical-align: middle; }
        .seal-img { height: 38pt; position: absolute; left: 35pt; top: -8pt; opacity: 0.4; }
        .sig-line { width: 100%; height: 0.5pt; background: #FF9500; opacity: 0.4; }
        .sig-lbl { font-size: 5pt; color: #8a9bb0; text-transform: uppercase; margin-top: 3pt; font-weight: bold; letter-spacing: 0.3pt; }

        .back-footer { position: absolute; bottom: 15pt; left: 0; width: 100%; text-align: center; color: #8a9bb0; font-size: 7.5pt; line-height: 1.4; }
        .reg-no { color: #FF9500; font-weight: bold; font-size: 8.5pt; margin-bottom: 2pt; letter-spacing: 0.2pt; }
    </style>
</head>
<body>

    <!-- FRONT CARD -->
    <div class="card front">
        <div class="navy-header">
            <div class="logo-area">
                <div class="logo-box">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="width: 22pt; height: 22pt; margin-top: 7pt;">
                    @endif
                </div>
            </div>
            <div class="company-header">
                <h1>{{ $companyName ?? 'ANDLEEB CLUSTER OF SERVICES PVT. LTD.' }}</h1>
                <p>Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Tech Agency' }}</p>
            </div>
            <div class="gold-line"></div>
        </div>
        <div class="curve-overlay"></div>

        <div class="photo-area">
            <div class="photo-ring">
                @if($profilePhotoBase64)
                    <img src="{{ $profilePhotoBase64 }}" class="photo-img">
                @else
                    <div class="photo-img" style="text-align: center; line-height: 74pt; color: #FF9500; font-size: 24pt; font-weight: bold;">{{ $initials ?? 'SM' }}</div>
                @endif
            </div>
        </div>

        <div class="badge">{{ $designation ?? 'Administrator' }}</div>

        <div class="name-text">{{ $user->name ?? 'User Name' }}</div>
        <div class="divider-container">
            <div class="divider"><div class="dot"></div></div>
        </div>

        <table class="info-grid">
            <tr>
                <td class="info-cell">
                    <div class="lbl">Employee ID</div>
                    <div class="val val-gold">{{ $cardNumber ?? 'ADM-0002' }}</div>
                </td>
                <td class="info-cell">
                    <div class="lbl">Date of Birth</div>
                    <div class="val">{{ $dob ?? '02 Feb 1980' }}</div>
                </td>
            </tr>
            <tr>
                <td class="info-cell">
                    <div class="lbl">Father's Name</div>
                    <div class="val">{{ $user->father_name ?? 'Habib Ullah Tali' }}</div>
                </td>
                <td class="info-cell">
                    <div class="lbl">Joining Date</div>
                    <div class="val">{{ $joiningDate ?? '11 Apr 2026' }}</div>
                </td>
            </tr>
            <tr>
                <td class="info-cell">
                    <div class="lbl">Contact</div>
                    <div class="val">{{ $mobile ?? '9797287817' }}</div>
                </td>
                <td class="info-cell">
                    <div class="lbl">Address</div>
                    <div class="val" style="font-size: 7.5pt; white-space: normal;">{{ $address ?? 'Pampore, Pulwama' }}</div>
                </td>
            </tr>
        </table>

        <div class="barcode-area">
            @if($barcodeBase64)
                <img src="{{ $barcodeBase64 }}" class="barcode-img">
            @endif
            <div class="barcode-text">{{ $cardNumber ?? 'ADM-0002' }}</div>
        </div>

        <div class="footer-line"></div>
        <div class="footer-text">
            <div class="f-left"><span class="v-dot"></span>Verified Identity</div>
            <div class="f-right">{{ $companyWebsite ?? 'andleebsurya.in' }}</div>
        </div>
    </div>

    <!-- BACK CARD -->
    <div class="card back">
        <div class="back-header">
            <div class="b-logo-box">
                @if($globalLogoBase64)
                    <img src="{{ $globalLogoBase64 }}" style="width: 20pt; height: 20pt; margin-top: 6pt;">
                @endif
            </div>
            <div class="b-brand">
                <h2>{{ $globalName ?? 'MALIK SURYA' }}</h2>
                <p>REG NO: {{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</p>
            </div>
        </div>

        <div class="qr-section">
            <div class="qr-box">
                @if($qrBase64)
                    <img src="{{ $qrBase64 }}" class="qr-img">
                @endif
            </div>
            <div class="qr-lbl">Scan to Verify</div>
        </div>

        <div class="notice-box">
            <p>This identity instrument is issued by<br>
            <strong>{{ $companyName ?? 'Andleeb Cluster of Services' }}</strong><br>
            Issued for secure access only.<br>
            If found, please return to a regional facility<br>
            or the <strong>Residency Road HQ.</strong></p>
        </div>

        <div class="emergency-pill">Emergency: {{ $companyEmergency ?? '9906766655' }}</div>

        <div class="sig-container">
            <div class="sig-box">
                <div class="sig-img-wrap">
                    @if($sealBase64)
                        <img src="{{ $sealBase64 }}" class="seal-img">
                    @endif
                </div>
                <div class="sig-line"></div>
                <div class="sig-lbl">Issuing Authority<br>Principal Seal</div>
            </div>
            <div class="sig-box" style="float: right;">
                <div class="sig-img-wrap">
                    @if($sigBase64)
                        <img src="{{ $sigBase64 }}" class="sig-img">
                    @endif
                </div>
                <div class="sig-line"></div>
                <div class="sig-lbl">Verified By<br>{{ $icardVerifiedBy ?? 'CHIEF OPERATIONS OFFICER' }}</div>
            </div>
        </div>

        <div class="back-footer">
            <div class="reg-no">{{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</div>
            <div>{{ $companyAddress ?? 'Srinagar, Jammu & Kashmir' }}</div>
            <div>+91-{{ $companyPhone ?? '9797287817' }} | {{ $companyEmail ?? 'info@andleebsurya.in' }}</div>
        </div>
    </div>

</body>
</html>
