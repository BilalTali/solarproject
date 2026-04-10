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
  <title>{{ $companyName ?? 'Suryamitra' }} — {{ isset($user) ? $user->name : 'User' }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body, h1, h2, h3, p, span, div, strong, table, td, tr {
      font-family: Helvetica, Arial, sans-serif !important;
    }

    @page {
      size: 360px 560px;
      margin: 0;
    }

    body {
      background: #FFFFFF;
      margin: 0; padding: 0;
    }

    .pdf-page {
      width: 360px;
      height: 560px;
      page-break-after: always;
      position: relative;
      overflow: hidden;
    }

    .pdf-page:last-child {
      page-break-after: auto;
    }

    .id-card {
      width: 360px;
      height: 560px;
      background: #FFFFFF;
      border-radius: 28px;
      overflow: hidden;
      position: relative;
    }

    /* ══════ SHARED HEADER ══════ */
    .header-zone {
      height: 160px;
      background: #0A1931;
      text-align: center;
      padding-top: 15px; 
      position: relative;
    }

    /* The visual curved arc at the bottom of the header */
    .header-curve {
      position: absolute;
      top: 130px; 
      left: -50px;
      width: 460px;
      height: 60px; 
      background: #FFFFFF;
      border-radius: 50%; 
      z-index: 5;
    }

    .brand-logo-img { 
      width: 45px; 
      height: 45px; 
      margin-bottom: 8px; 
      position: relative; 
      z-index: 10; 
    }

    .brand-name {
      font-size: 18px;
      letter-spacing: 2px;
      text-transform: uppercase;
      font-weight: 700;
      color: #F7B100;
      position: relative;
      z-index: 10;
      line-height: 1.2;
    }

    .brand-meta {
      font-size: 9px;
      font-weight: 600;
      color: #FFFFFF;
      opacity: 0.9;
      margin-top: 4px;
      position: relative;
      z-index: 10;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* ══════ PHOTO (DYNAMIC) ══════ */
    .photo-anchor {
      position: absolute;
      top: 130px; 
      left: 110px;
      width: 140px;
      height: 140px;
      z-index: 20;
    }

    .user-crop-img {
      position: absolute;
      top: 0; left: 0;
      width: 140px; 
      height: 140px; 
      object-fit: cover;
      clip-path: polygon(50% 0%, 95% 25%, 95% 75%, 50% 100%, 5% 75%, 5% 25%);
      z-index: 20;
    }
    
    .initials-avatar {
      position: absolute; top: 0; left: 0; width: 140px; height: 140px;
      line-height: 140px; text-align: center;
      background: transparent;
      border: none;
    }

    /* ══════ MAIN CONTENT ══════ */
    .card-body {
      padding: 85px 30px 10px; 
      text-align: center;
    }

    .name-h1 { font-size: 26px; font-weight: 800; color: #050A1A; margin-bottom: 8px; line-height: 1.1; text-transform: capitalize; }
    
    .role-pill {
      display: inline-block;
      padding: 4px 16px;
      background: #F7B100;
      color: #0A1931;
      border-radius: 100px;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 16px;
    }

    .data-grid {
      width: 100%;
      border-top: 1.5px solid #E2E8F0;
      padding-top: 15px;
      text-align: left;
    }
    
    .data-row { margin-bottom: 8px; width: 100%; height: 38px; } 
    .data-col { position: relative; width: 50%; float: left; padding: 0 5px; box-sizing: border-box; }

    .label-tag { font-size: 9.5px; font-weight: 800; color: #555E70; text-transform: uppercase; letter-spacing: 1px; display: block; } 
    .value-tag { font-size: 12.5px; font-weight: 800; color: #0A1931; margin-top: 1px; display: block; } 

    /* ══════ FOOTER ══════ */
    .footer-lock {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 360px;
      height: 95px; 
      background: #FFFFFF; 
      border-top: 1.2px solid #F1F5F9;
    }

    .card-footer {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 7mm;
        background: linear-gradient(90deg, #0A3D7A 0%, #0D4F9E 50%, #0A3D7A 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .footer-website {
        font-family: 'DM Sans', sans-serif;
        font-size: 6pt;
        font-weight: 600;
        color: rgba(255,255,255,0.7);
        letter-spacing: 1px;
        text-transform: lowercase;
    }

    .barcode-wrap {
      position: absolute;
      left: 20px;
      top: 15px;
      width: 140px;
    }
    .barcode-img { height: 25px; width: 130px; margin-bottom: 3px; display: block; }

    .status-wrap { 
      position: absolute;
      right: 20px;
      top: 10px;
      width: 140px;
      text-align: right; 
    }
    .verified-text { font-size: 9px; font-weight: 800; color: #0A1931; margin-top: 1px; display: block; }

    .sig-footer-img {
      height: 30px;
      width: auto;
      display: block;
      margin-left: auto;
      margin-bottom: -2px;
    }

    /* Backside Overrides */
    .back-content {
      position: absolute;
      top: 130px; 
      left: 50px;
      width: 260px;
      height: 290px;
      padding-top: 20px;
      text-align: center;
      display: block;
    }

    .warning-text { 
      font-size: 10px; 
      line-height: 1.6; 
      color: #555E70; 
      margin-bottom: 25px; 
      width: 100%;
      text-align: center;
      text-transform: uppercase;
      font-weight: 500;
      letter-spacing: 0.5px;
    }

    .warning-text strong {
      color: #0A1931;
      font-weight: 800;
    }

    .contact-block {
        text-align: center;
        margin-bottom: 3mm;
    }

    .contact-line {
        font-size: 6.5pt;
        font-weight: 700;
        line-height: 1.8;
        color: #555E70;
        letter-spacing: 0.3px;
    }

    .qr-container { 
      width: 90px; 
      height: 90px; 
      margin: 0 auto 30px;
      background: #FFFFFF;
      padding: 10px; 
      border-radius: 15px;
      border: 1px solid #E2E8F0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
    }

    .qr-container img { width: 100%; height: 100%; object-fit: contain; }

    /* ══════ WATERMARK ══════ */
    .watermark-bg {
      position: absolute;
      top: 310px;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 280px;
      height: 280px;
      opacity: 0.07;
      z-index: 1;
      pointer-events: none;
    }
  </style>
</head>
<body>

  <!-- Front Side -->
  <div class="pdf-page">
    <div class="id-card">
      @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="watermark-bg" alt="">
      @endif

      <div class="header-zone">
        <div class="header-curve"></div>
        <div style="position: absolute; top: 15px; width: 100%; display: flex; justify-content: center; align-items: center; gap: 10px; z-index: 10;">
          @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="brand-logo-img" alt="Logo">
          @endif
        </div>
        <div style="padding-top: 60px;">
          <div class="brand-name">{{ $companyName ?? 'SURYAMITRA' }}</div>
          @if(isset($affiliatedPartner))
            <div class="brand-meta" style="color: #F7B100; font-size: 8px;">Affiliated with: {{ $affiliatedPartner }}</div>
          @endif
        </div>
      </div>

      <div class="photo-anchor">
        <svg width="140" height="140" viewBox="0 0 140 140" style="position: absolute; top:0; left:0; z-index: 25;">
          <polygon points="70,3 135,36 135,104 70,137 5,104 5,36" fill="none" stroke="#0A1931" stroke-width="6" />
        </svg>

        @if($profilePhotoBase64)
          <img class="user-crop-img" src="{{ $profilePhotoBase64 }}" alt="Photo">
        @else
          <div class="initials-avatar">
            <svg width="140" height="140" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
              <polygon points="70,3 135,36 135,104 70,137 5,104 5,36" fill="#F7B100" />
              <text x="70" y="85" font-family="Arial" font-size="45" font-weight="900" fill="#0A1931" text-anchor="middle">{{ $initials ?? 'SM' }}</text>
            </svg>
          </div>
        @endif
      </div>

      <div class="card-body">
        <h2 class="name-h1">{{ $user->name ?? 'User Name' }}</h2>
        <span class="role-pill">{{ $designation ?? 'Member' }}</span>

        <div class="data-grid">
          <div class="data-row">
            <div class="data-col">
              <span class="label-tag">Employee ID</span>
              <span class="value-tag">{{ $cardNumber ?? 'N/A' }}</span>
            </div>
            <div class="data-col">
              <span class="label-tag">Father's Name</span>
              <span class="value-tag" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $user->father_name ?? 'N/A' }}</span>
            </div>
          </div>
          <div class="data-row">
            <div class="data-col">
              <span class="label-tag">DOB</span>
              <span class="value-tag">{{ $dob }}</span>

            </div>
            <div class="data-col">
              <span class="label-tag">Joining Date</span>
              <span class="value-tag">{{ $joiningDate }}</span>
            </div>
          </div>
          <div class="data-row">
            <div class="data-col">
              <span class="label-tag">Contact Number</span>
              <span class="value-tag">{{ $mobile }}</span>

            </div>
            <div class="data-col">
              <span class="label-tag">Address</span>
              <span class="value-tag" style="font-size: 10px;">{{ $address }}</span>

            </div>
          </div>
        </div>
      </div>

      <div class="footer-lock">
        <div class="barcode-wrap" style="top: 10px;">
          @if($barcodeBase64)
            <img src="{{ $barcodeBase64 }}" class="barcode-img" alt="Barcode">
          @endif
          <span class="label-tag" style="font-size: 6px;">{{ $cardNumber ?? '' }}</span>
        </div>
        <div class="status-wrap" style="top: 13px; right: 15px; width: 140px; height: 65px;">
          <div style="position: relative; height: 35px; margin-bottom: 2px;">
            @if($sealBase64)
              <img src="{{ $sealBase64 }}" alt="Seal" style="position: absolute; right: 40px; top: -10px; height: 45px; width: auto; opacity: 0.8; z-index: 5;">
            @endif
            @if($sigBase64)
              <img src="{{ $sigBase64 }}" alt="Signature" style="position: absolute; right: 0; top: 0; height: 32px; width: auto; z-index: 10;">
            @endif
          </div>
          <span class="verified-text" style="font-size: 8.5px; position: relative; z-index: 15;">VERIFIED IDENTITY</span>
        </div>

        <div class="card-footer">
            <span class="footer-website">Website: {{ $companyWebsite ?? 'suryamitra.in' }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Back Side -->
  <div class="pdf-page">
    <div class="id-card">
      @if($globalLogoBase64)
        <img src="{{ $globalLogoBase64 }}" class="watermark-bg" alt="">
      @endif

      <div class="header-zone">
        <div class="header-curve"></div>
        @if($globalLogoBase64)
          <img src="{{ $globalLogoBase64 }}" class="brand-logo-img" alt="Logo">
        @endif
        @if($globalAffiliatedPartner)
          <div class="brand-name">{{ $globalAffiliatedPartner }}</div>
        @endif
        @if($globalRegNo)
          <div class="brand-meta">Reg No: {{ $globalRegNo }}</div>
        @endif
      </div>

      <div class="back-content">
        <div class="qr-container">
           @if($qrBase64)
             <img src="{{ $qrBase64 }}" alt="QR">
           @endif
        </div>

        <div class="warning-text">
          THIS IDENTITY INSTRUMENT IS ISSUED BY <br><strong>{{ $companyName ?? 'Suryamitra Solar Infrastructure' }}</strong>. 
          ISSUED FOR SECURE ACCESS ONLY. 
          <br><br>
          IF FOUND, PLEASE RETURN TO A REGIONAL FACILITY OR THE RESIDENCY ROAD HQ.
        </div>
      </div>

      <div class="footer-lock" style="height: 115px; padding-bottom: 15px;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 5px;">
          <tr>
            <td style="width: 48%; padding-left: 20px; vertical-align: middle;">
              <div style="border: 2px solid #F7B100; color: #0A1931; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 9px; background: #FFFBEB; display: inline-block;">
                EMERGENCY: {{ $companyEmergency ?? '9906766655' }}
              </div>
            </td>
            <td style="width: 52%; padding-right: 20px; text-align: right; vertical-align: middle;">
              @if($globalSigBase64)
                <img src="{{ $globalSigBase64 }}" class="sig-footer-img" alt="Signature" style="margin-bottom: -15px;">
              @endif
              <span class="label-tag" style="font-size: 7px; color: #555E70; font-weight: 800;">Verified By</span>
              <div style="font-size: 10px; font-weight: 800; color: #0A1931;">
                {{ $icardVerifiedBy ?? 'CHIEF OPERATIONS OFFICER' }}
              </div>
            </td>
          </tr>
        </table>

        <div class="contact-block">
            <div class="contact-line" style="font-size: 5.5pt; color: #94A3B8;">
                {{ $companyAddress ?? 'Srinagar, Jammu & Kashmir' }}
            </div>
            <div style="width: 200px; height: 1px; background: #E2E8F0; margin: 3px auto;"></div>
            <div class="contact-line">
                Phone: {{ $companyPhone ?? '+91 99067 66655' }} &nbsp; | &nbsp; Email: {{ $companyEmail ?? 'info@suryamitra.in' }}
            </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>