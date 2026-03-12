<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ $companyName ?? 'Suryamitra' }} — {{ $user->name ?? 'User' }}</title>
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
      padding-top: 15px; /* Reduced from 25px for larger logo */
      position: relative;
    }


    /* The visual curved arc at the bottom of the header */
    .header-curve {
      position: absolute;
      top: 130px; /* Adjusted for reduced header */
      left: -50px;
      width: 460px;
      height: 60px; /* Reduced height */
      background: #FFFFFF;
      border-radius: 50%; /* Smooth wide arc */
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


    /* ══════ PHOTO (PHP GD HEXAGON FIX) ══════ */
    .photo-anchor {
      position: absolute;
      top: 85px; /* Moved up for reduced header */
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
    }
    
    .initials-avatar {
      position: absolute; top: 0; left: 0; width: 122px; height: 122px;
      line-height: 122px; text-align: center;
      font-size: 40px; font-weight: 800; color: #0A1931; border-radius: 61px;
    }



    /* ══════ MAIN CONTENT ══════ */
    .card-body {
      padding: 45px 30px 10px; /* Reduced from 60px */
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
    
    .data-row { margin-bottom: 8px; width: 100%; height: 38px; } /* Increased height to 38px */
    .data-col { position: relative; width: 50%; float: left; padding: 0 5px; box-sizing: border-box; }

    .label-tag { font-size: 9.5px; font-weight: 800; color: #555E70; text-transform: uppercase; letter-spacing: 1px; display: block; } /* Increased from 8px */
    .value-tag { font-size: 12.5px; font-weight: 800; color: #0A1931; margin-top: 1px; display: block; } /* Increased from 10px */




    /* ══════ SIGNATURE ══════ */
    /* signature-wrap removed as per plan */


    /* ══════ FOOTER ══════ */
    .footer-lock {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 360px;
      height: 95px; /* Reduced from 110px to avoid overlap */
      background: #FFFFFF; 
      border-top: 1.2px solid #F1F5F9;
    }

    /* Lower zone with different background for hierarchy */
    .footer-base-zone {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 42px; /* Slightly reduced */
      background: #F8FAFC;
      border-top: 1px solid #F1F5F9;
    }







    .bottom-blue-bar {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 10px;
      background: #0A1931;
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
    .status-text { font-size: 7px; color: #10B981; font-weight: 800; display: block; }
    .verified-text { font-size: 9px; font-weight: 800; color: #0A1931; margin-top: 1px; display: block; }

    .sig-footer-img {
      height: 30px;
      width: auto;
      display: block;
      margin-left: auto;
      margin-bottom: -2px;
    }





    /* Backside Overrides */
      /* ── QR Code Section (replaces barcode on back card) ── */
      .qr-section {
          position: absolute;
          bottom: 30px;
          left: 50%;
          transform: translateX(-50%);
          text-align: center;
          z-index: 10;
          width: 100%;
      }

      .qr-wrapper {
          width: 80px;
          height: 80px;
          background: white;
          padding: 4px;
          border-radius: 4px;
          box-shadow: 
              0 0 0 2px rgba(255,149,0,0.6),
              0 0 0 6px rgba(255,149,0,0.12),
              0 8px 30px rgba(0,0,0,0.5);
          margin: 0 auto 6px;
      }



      .qr-img {
          width: 100%;
          height: 100%;
          display: block;
      }

      .qr-placeholder {
          width: 80px; height: 80px;
          background: rgba(255,255,255,0.08);
          border: 1.5px solid rgba(255,255,255,0.15);
          border-radius: 4px;
          display: flex; align-items: center; justify-content: center;
          margin: 0 auto 6px;
      }

      .qr-placeholder-text {
          font-family: 'Cinzel', serif;
          font-size: 8pt; font-weight: 700;
          color: rgba(255,255,255,0.2);
          letter-spacing: 2px;
      }

      .qr-label {
          font-size: 5pt;
          font-weight: 600;
          color: rgba(255,255,255,0.35);
          letter-spacing: 1.5px;
          text-transform: uppercase;
          margin-bottom: 3px;
      }

      .qr-card-number {
          font-family: 'DM Sans', sans-serif;
          font-size: 7pt;
          font-weight: 700;
          color: rgba(255,255,255,0.5);
          letter-spacing: 2px;
      }


    .back-content {
      position: absolute;
      top: 130px; /* Moved up by 50px */
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
        font-family: 'DM Sans', sans-serif;
        font-size: 6.5pt;
        font-weight: 700;
        line-height: 1.8;
        color: #555E70;
        letter-spacing: 0.3px;
    }

    .contact-website {
        color: #FF9500;           /* orange — makes website stand out */
        font-weight: 600;
        font-size: 6.8pt;
        letter-spacing: 0.5px;
    }

    .qr-container { 
      width: 90px; 
      height: 90px; 
      margin: 0 auto 30px;
      background: #FFFFFF;
      padding: 10px; 
      border-radius: 15px;
      border: 1px solid #E2E8F0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08); /* World-class soft shadow */
    }



    .qr-container img { width: 100%; height: 100%; object-fit: contain; }

    /* ══════ DECORATIVE STRIPES ══════ */
    .edge-bars { position: absolute; z-index: 10; }
    .bar-yellow { background: #F7B100; position: absolute; border-radius: 4px; opacity: 1; }
    .bar-blue { background: #0A1931; position: absolute; border-radius: 4px; opacity: 0.2; }

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
  </style>
</head>
<body>

  @php
    /* Image Conversion for photo still happens here as it involves GD processing */
    $photoBase64 = null;
    if(isset($user->profile_photo) && $user->profile_photo && file_exists(storage_path('app/public/' . $user->profile_photo))) {
        $path = storage_path('app/public/' . $user->profile_photo);
        $processed = false;
        if (function_exists('getimagesize') && function_exists('imagecrop')) {
            $info = @getimagesize($path);
            if ($info) {
                $img = null;
                if ($info['mime'] == 'image/jpeg') { $img = @imagecreatefromjpeg($path); }
                elseif ($info['mime'] == 'image/png') { $img = @imagecreatefrompng($path); }
                elseif ($info['mime'] == 'image/webp') { $img = @imagecreatefromwebp($path); }
                
                if ($img) {
                    $w = imagesx($img);
                    $h = imagesy($img);
                    $size = min($w, $h);
                    $cropped = @imagecrop($img, ['x' => ($w - $size) / 2, 'y' => ($h - $size) / 2, 'width' => $size, 'height' => $size]);
                    if ($cropped) {
                        $size = 200;
                        $resized = imagecreatetruecolor($size, $size);
                        imagecopyresampled($resized, $cropped, 0, 0, 0, 0, $size, $size, imagesx($cropped), imagesy($cropped));
                        
                        // PHP Hexagon Transparency Mask
                        $hexMask = imagecreatetruecolor($size, $size);
                        $magenta = imagecolorallocate($hexMask, 255, 0, 255);
                        imagefill($hexMask, 0, 0, $magenta);
                        $white = imagecolorallocate($hexMask, 255, 255, 255);
                        
                        // Hexagon points for size=200
                        $points = [100,2, 194,50, 194,150, 100,198, 6,150, 6,50];
                        imagefilledpolygon($hexMask, $points, 6, $white);


                        
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                        for ($x = 0; $x < $size; $x++) {
                            for ($y = 0; $y < $size; $y++) {
                                $c = imagecolorat($hexMask, $x, $y);
                                if ($c == $magenta) {
                                    imagesetpixel($resized, $x, $y, $transparent);
                                }
                            }
                        }
                        
                        // Draw Navy Blue Border on Hexagon
                        $navy = imagecolorallocate($resized, 10, 25, 49);
                        imagesetthickness($resized, 3);
                        imagepolygon($resized, $points, 6, $navy);
                        
                        imagedestroy($hexMask);
                        
                        ob_start();
                        imagepng($resized);
                        $photoBase64 = 'data:image/png;base64,' . base64_encode(ob_get_clean());
                        imagedestroy($resized);
                        imagedestroy($cropped);
                        $processed = true;
                    }
                    imagedestroy($img);
                }
            }
        }
        if (!$processed) {
            $photoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }
    }

    /* Barcode and Brand assets are now passed from ICardService as base64 */
    $barcodeBase64 = $barcodeBase64 ?? null;
    $logoBase64    = $logoBase64    ?? null;
    $logoBase64_2  = $logoBase64_2  ?? null;
    $sigBase64     = $sigBase64     ?? null;
    $sealBase64    = $sealBase64    ?? null;
  @endphp

  <!-- Front Side -->
  <div class="pdf-page">
    <div class="id-card">
      @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="watermark-bg" alt="">
      @endif
      <div class="edge-bars" style="top: 230px; left: 0; width: 20px; height: 160px;">
         <div class="bar-yellow" style="top: 0; left: 0; width: 12px; height: 160px; border-radius: 0 4px 4px 0;"></div>
         <div class="bar-blue" style="top: 30px; left: 14px; width: 6px; height: 100px;"></div>
      </div>
      <div class="edge-bars" style="top: 230px; right: 0; width: 20px; height: 160px;">
         <div class="bar-yellow" style="top: 0; right: 0; width: 12px; height: 160px; border-radius: 4px 0 0 4px;"></div>
         <div class="bar-blue" style="top: 30px; right: 14px; width: 6px; height: 100px;"></div>
      </div>





      <div class="header-zone">
        <div class="header-curve"></div>
        
        @if($logoBase64)
          <img src="{{ $logoBase64 }}" class="brand-logo-img" alt="Logo">
        @else
          <svg class="brand-logo-img" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="5" fill="#F7B100"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="#F7B100" stroke-width="2" stroke-linecap="round"/></svg>
        @endif
        
        <div class="brand-name">{{ $companyName ?? 'SURYAMITRA' }}</div>
      </div>


      <div class="photo-anchor">
        @if($photoBase64)
          <img class="user-crop-img" src="{{ $photoBase64 }}" alt="Photo">
        @else
          <div class="initials-avatar">{{ $initials ?? 'SM' }}</div>
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
              <span class="value-tag">{{ $user->dob ? $user->dob->format('d-m-Y') : 'N/A' }}</span>
            </div>
            <div class="data-col">
              <span class="label-tag">Blood Group</span>
              <span class="value-tag">{{ $user->blood_group ?? 'N/A' }}</span>
            </div>

          </div>
          <div class="data-row">
            <div class="data-col">
              <span class="label-tag">Contact Number</span>
              <span class="value-tag">{{ $user->mobile ?? 'N/A' }}</span>
            </div>
            <div class="data-col" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              <span class="label-tag">Date of Joining</span>
              <span class="value-tag">{{ $joiningDate }}</span>
            </div>



          </div>
        </div>
      </div>

      <!-- signature-wrap removed -->


      <div class="footer-lock">
        <div class="barcode-wrap" style="top: 10px;">
          @if($barcodeBase64)
            <img src="{{ $barcodeBase64 }}" class="barcode-img" alt="Barcode">
          @endif
          <span class="label-tag" style="font-size: 6px;">{{ $cardNumber ?? '' }}-V6</span>
        </div>
        <div class="status-wrap" style="top: 10px;">
          @if($sigBase64)
            <img src="{{ $sigBase64 }}" class="sig-footer-img" alt="Signature" style="margin-bottom: -2px; height: 25px;">
          @endif
          <span class="verified-text" style="font-size: 8.5px;">VERIFIED IDENTITY</span>
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
      @if($logoBase64_2)
        <img src="{{ $logoBase64_2 }}" class="watermark-bg" alt="">
      @elseif($logoBase64)
        <img src="{{ $logoBase64 }}" class="watermark-bg" alt="">
      @endif
      <div class="edge-bars" style="top: 230px; left: 0; width: 20px; height: 160px;">
         <div class="bar-yellow" style="top: 0; left: 0; width: 12px; height: 160px; border-radius: 0 4px 4px 0;"></div>
         <div class="bar-blue" style="top: 30px; left: 14px; width: 6px; height: 100px;"></div>
      </div>
      <div class="edge-bars" style="top: 230px; right: 0; width: 20px; height: 160px;">
         <div class="bar-yellow" style="top: 0; right: 0; width: 12px; height: 160px; border-radius: 4px 0 0 4px;"></div>
         <div class="bar-blue" style="top: 30px; right: 14px; width: 6px; height: 100px;"></div>
      </div>




      <div class="header-zone">
        <div class="header-curve"></div>

        @if($logoBase64_2)
          <img src="{{ $logoBase64_2 }}" class="brand-logo-img" alt="Logo">
        @elseif($logoBase64)
          <img src="{{ $logoBase64 }}" class="brand-logo-img" alt="Logo">
        @else
          <svg class="brand-logo-img" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="5" fill="#F7B100"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="#F7B100" stroke-width="2" stroke-linecap="round"/></svg>
        @endif
        
        @if($companyAffiliatedWith)
          <div class="brand-name">{{ $companyAffiliatedWith }}</div>
        @endif
        
        @if($companyRegNo)
          <div class="brand-meta" style="margin-top: 6px;">Reg No: {{ $companyRegNo }}</div>
        @endif
      </div>

      <div class="back-content">
        <div class="qr-container">
           @if($qrCodeBase64)
             <img src="{{ $qrCodeBase64 }}" alt="QR">
           @else
             <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzBBMTkzMSI+PHBhdGggZD0iTTMgM2g4djhIM1Yzem0yIDJ2NGg0VjVINFptOC0yaDh2OGgtOFYzem0yIDJ2NGg0VjVoLTR6TTMgMTNoOHY4SDN2LTh6bTIgMnY0aDR2LTRINFptMTMtMmgzdjJoLTN2LTJ6bS0zIDBoMnYzaC0ydi0zem0zIDNoM3YyaC0zdi0yem0tMyAyaDJ2M2gtMnYtM3ptMyAxaDN2MmgtM3YtMnptLTMgMmg1djJoLTV2LTJ6Ii8+PC9zdmc+" alt="QR">
           @endif
        </div>

        <div class="warning-text">
          THIS IDENTITY INSTRUMENT IS ISSUED BY <br><strong>{{ $companyName ?? 'Suryamitra Solar Infrastructure' }}</strong>. 
          ISSUED FOR SECURE ACCESS ONLY. 
          <br><br>
          IF FOUND, PLEASE RETURN TO A REGIONAL FACILITY OR THE RESIDENCY ROAD HQ.
        </div>

      </div>

      <div class="footer-lock">
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 5px;">
          <tr>
            <td style="width: 48%; padding-left: 20px; vertical-align: middle;">
              <div style="border: 2px solid #F7B100; color: #0A1931; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 9px; background: #FFFBEB; display: inline-block; white-space: nowrap;">
                EMERGENCY: {{ $companyEmergency ?? '9906766655' }}
              </div>
            </td>
            <td style="width: 52%; padding-right: 20px; text-align: right; vertical-align: middle; position: relative;">
              @if($sigBase64)
                <img src="{{ $sigBase64 }}" class="sig-footer-img" alt="Signature" style="margin-left: auto; margin-bottom: -15px; position: relative; z-index: 10;">
              @endif
              <span class="label-tag" style="display: block; font-size: 7px; color: #555E70; text-transform: uppercase; font-weight: 800; position: relative; z-index: 5;">Verified By</span>
              <div style="font-size: 10px; font-weight: 800; color: #0A1931; line-height: 1.1; margin-top: 1px; position: relative; z-index: 5;">
                {{ $icardVerifiedBy ?? 'CHIEF OPERATIONS OFFICER' }}
              </div>
            </td>
          </tr>
        </table>

        <!-- Dedicated lower zone for company details -->
        <div class="contact-block">
            <div class="contact-line" style="font-size: 5.5pt; margin-bottom: 2px; color: #94A3B8; line-height: 1.2;">
                {{ $companyAddress ?? 'Srinagar, Jammu & Kashmir' }}
            </div>
            <div style="width: 200px; height: 1px; background: #E2E8F0; margin: 3px auto;"></div>
            <div class="contact-line" style="margin-top: 2px;">
                Phone: {{ $companyPhone ?? '+91 99067 66655' }} &nbsp; | &nbsp; Email: {{ $companyEmail ?? 'info@suryamitra.in' }}
            </div>
        </div>
    </div>



    </div>
  </div>

</body>
</html>