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
      background: #FFFFFF;
    }
    
    .pdf-page:last-child {
      page-break-after: auto;
    }

    /* Absolute containers prevent document flow height expansion bugs in DOMPdf */
    .id-card2 {
      position: absolute;
      top: 0;
      left: 0;
      width: 360px;
      height: 620px;
      background: #f5f3ee;
      border: 1px solid rgba(255,149,0,0.4);
      z-index: 1;
    }

    .id-card-back2 {
      position: absolute;
      top: 0;
      left: 0;
      width: 360px;
      height: 620px;
      background: #04111F;
      border: 1px solid rgba(255,149,0,0.4);
      z-index: 1;
    }

    /* ════ FRONT PAGE ════ */
    .header-zone2 {
      position: absolute;
      top: 0;
      left: 0;
      height: 200px;
      background: #04111F;
      width: 360px;
      border-bottom: 2px solid #FF9500;
      z-index: 2;
    }

    .header-curve2 {
      position: absolute;
      top: 175px; 
      left: -20px;
      width: 400px;
      height: 45px;
      background: #f5f3ee;
      border-radius: 50% 50% 0 0 / 100% 100% 0 0;
      z-index: 5;
    }

    .photo-anchor2 {
      position: absolute;
      top: 90px;
      left: 125px;
      width: 110px;
      height: 110px;
      z-index: 20;
    }

    .role-anchor2 {
      position: absolute;
      top: 185px;  
      left: 0;
      width: 360px;
      text-align: center;
      z-index: 25;
    }

    /* Moved card body to absolute top to avoid float pushing */
    .card-body2 {
      position: absolute;
      top: 225px;
      left: 0;
      width: 360px;
      padding: 0 20px;
      text-align: center;
      z-index: 10;
    }

    .data-grid2 {
      width: 100%;
      margin-top: 15px;
      text-align: left;
    }
    
    .data-row2 {
      margin-bottom: 12px;
      width: 100%;
      height: 45px;
    }

    .data-col2 {
      position: relative;
      width: 50%;
      float: left;
      padding: 0 4px;
      box-sizing: border-box;
    }

    .data-box2 {
      background: #FFFFFF;
      border: 1px solid rgba(4,17,31,0.07);
      border-radius: 8px;
      padding: 6px;
      height: 40px;
      overflow: hidden;
    }

    /* Fixed top anchoring for footer */
    .footer-lock2 {
      position: absolute;
      top: 535px; /* 620 - 85 */
      left: 0;
      width: 360px;
      height: 85px; 
      z-index: 30;
    }

    /* ════ BACK PAGE ════ */
    .back-header-box2 {
      position: absolute;
      top: 25px;
      left: 30px;
      width: 300px;
      height: 50px;
      border-bottom: 1px solid rgba(255,149,0,0.15);
      z-index: 10;
    }
    
    .back-content2 {
      position: absolute;
      top: 110px;
      left: 0;
      width: 360px;
      text-align: center;
      z-index: 10;
    }

    .footer-lock-back2 {
      position: absolute;
      top: 480px; /* 620 - 140 */
      left: 0;
      width: 360px;
      height: 140px;
      z-index: 10;
    }

  </style>
</head>
<body>

  <!-- ════════ FRONT ════════ -->
  <div class="pdf-page">
    <div class="id-card2">
      
      <!-- Top Dark Header -->
      <div class="header-zone2">
         <div class="header-curve2"></div>
         <!-- Logo -->
         <div style="position: absolute; top: 25px; left: 25px; width: 42px; height: 42px; background: #FF9500; border-radius: 8px; text-align: center; z-index: 10;">
             @if($logoBase64)
               <img src="{{ $logoBase64 }}" style="width: 24px; height: 24px; margin-top: 9px;">
             @endif
         </div>
         <!-- Text -->
         <div style="position: absolute; top: 30px; left: 82px; width: 250px; z-index: 10;">
            <div style="color: #FFF; font-size: 11px; font-weight: bold; font-family: serif !important; letter-spacing: 0.5px; line-height: 1.3">
               {{ $companyName ?? 'ANDLEEB CLUSTER OF SERVICES PVT. LTD.' }}
            </div>
            <div style="color: #FF9500; font-size: 7px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin-top: 5px;">
               Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Tech Agency' }}
            </div>
         </div>
      </div>

      <!-- Photo Anchor -->
      <div class="photo-anchor2">
         <!-- The image mask rendered from backend now provides a perfect transparent circle! -->
         @if($profilePhotoBase64)
            <img src="{{ $profilePhotoBase64 }}" style="position: absolute; top: 4px; left: 4px; width: 102px; height: 102px; border-radius: 50%; z-index: 11; object-fit: cover;">
         @else
            <div style="position: absolute; top: 4px; left: 4px; width: 102px; height: 102px; border-radius: 50%; border: 3px solid #04111F; z-index: 11; background: #0a1f35; color: #FF9500; font-size: 32px; font-weight: bold; text-align: center; line-height: 102px;">{{ $initials ?? 'SM' }}</div>
         @endif
      </div>

      <!-- Role Anchor -->
      <div class="role-anchor2">
         <div style="display: inline-block; background: #FF9500; color: #04111F; font-size: 10.5px; font-weight: bold; padding: 6px 18px; border-radius: 12px; letter-spacing: 1.5px; text-transform: uppercase;">
            {{ $designation ?? 'Administrator' }}
         </div>
      </div>

      <!-- Content Flow explicitly top-positioned -->
      <div class="card-body2">
         <!-- Name -->
         <div style="font-size: 20px; font-weight: bold; color: #04111F; font-family: serif !important; text-transform: uppercase; margin-bottom: 10px;">
            {{ $user->name ?? 'User Name' }}
         </div>

         <!-- Divider -->
         <div style="position: relative; margin: 0 auto; width: 300px; height: 15px;">
             <div style="position: absolute; top: 7px; left: 0; width: 300px; height: 1px; background: rgba(255,149,0,0.4);"></div>
             <div style="position: absolute; top: 5px; left: 147px; width: 5px; height: 5px; background: #FF9500; border-radius: 50%;"></div>
         </div>

         <div class="data-grid2">
            <!-- Row 1 -->
            <div class="data-row2">
               <div class="data-col2">
                  <div class="data-box2">
                     <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
                        <div style="font-size: 7.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Employee ID</div>
                        <div style="font-size: 11px; font-weight: bold; color: #FF9500; margin-top: 2px;">{{ $cardNumber ?? 'ADM-0002' }}</div>
                     </div>
                  </div>
               </div>
               <div class="data-col2">
                  <div class="data-box2">
                     <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
                        <div style="font-size: 7.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Date of Birth</div>
                        <div style="font-size: 11px; font-weight: bold; color: #04111F; margin-top: 2px;">{{ $dob ?? '02 Feb 1980' }}</div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Row 2 -->
            <div class="data-row2">
               <div class="data-col2">
                  <div class="data-box2">
                     <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
                        <div style="font-size: 7.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Father's Name</div>
                        <div style="font-size: 10px; font-weight: bold; color: #04111F; margin-top: 2px; overflow: hidden; white-space: nowrap;">{{ $user->father_name ?? 'Habib Ullah' }}</div>
                     </div>
                  </div>
               </div>
               <div class="data-col2">
                  <div class="data-box2">
                     <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
                        <div style="font-size: 7.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Joining Date</div>
                        <div style="font-size: 11px; font-weight: bold; color: #04111F; margin-top: 2px;">{{ $joiningDate ?? '11 Apr 2026' }}</div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Row 3 -->
            <div class="data-row2">
               <div class="data-col2">
                  <div class="data-box2">
                     <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
                        <div style="font-size: 7.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Contact</div>
                        <div style="font-size: 11px; font-weight: bold; color: #04111F; margin-top: 2px;">{{ $mobile ?? '9797287817' }}</div>
                     </div>
                  </div>
               </div>
               <div class="data-col2">
                  <div class="data-box2">
                     <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
                        <div style="font-size: 7.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Address</div>
                        <div style="font-size: 9px; font-weight: bold; color: #04111F; margin-top: 2px; line-height: 1.2;">{{ $address ?? 'Pampore, Pulwama' }}</div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Footer rigidly locked at specific TOP pixel instead of BOTTOM 0 -->
      <div class="footer-lock2">
         <!-- Barcode -->
         <div style="position: absolute; top: 0; left: 25px; width: 310px; height: 40px;">
            @if($barcodeBase64)
              <img src="{{ $barcodeBase64 }}" style="float: left; width: 170px; height: 32px;">
            @endif
            <div style="float: right; font-size: 10.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; margin-top: 14px;">{{ $cardNumber ?? 'ADM-0002' }}</div>
         </div>
         <!-- Base Bar -->
         <div style="position: absolute; top: 45px; left: 0; width: 360px; height: 40px; background: #04111F; z-index: 10;">
            <div style="float: left; padding: 14px 0 0 20px; font-size: 9px; font-weight: bold; color: #FF9500; text-transform: uppercase; letter-spacing: 1.5px;">
               <span style="display:inline-block; width:6px; height:6px; background:#22c55e; border-radius:50%; margin-right:4px;"></span> VERIFIED IDENTITY
            </div>
            <div style="float: right; padding: 14px 20px 0 0; font-size: 8.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 0.5px;">
               {{ $companyWebsite ?? 'andleebsurya.in' }}
            </div>
         </div>
      </div>
    </div>
  </div>

  <!-- ════════ BACK ════════ -->
  <div class="pdf-page">
    <div class="id-card-back2">
      
      <!-- Back Header -->
      <div class="back-header-box2">
         <div style="float: left; width: 38px; height: 38px; background: #FF9500; border-radius: 8px; text-align: center;">
             @if($globalLogoBase64)
               <img src="{{ $globalLogoBase64 }}" style="width: 22px; height: 22px; margin-top: 8px;">
             @endif
         </div>
         <div style="float: left; margin-left: 15px; padding-top: 5px;">
             <div style="color: #FF9500; font-size: 14px; font-weight: bold; font-family: serif !important; letter-spacing: 1px;">{{ $globalName ?? 'MALIK SURYA' }}</div>
             <div style="color: #8a9bb0; font-size: 8px; letter-spacing: 1px; margin-top: 3px;">REG NO: {{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</div>
         </div>
      </div>

      <!-- Back Content -->
      <div class="back-content2">
         <div style="display: inline-block; background: #FFFFFF; padding: 10px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1);">
            @if($qrBase64)
               <img src="{{ $qrBase64 }}" style="width: 140px; height: 140px;">
            @endif
         </div>
         <div style="margin-top: 14px; margin-bottom: 25px; font-size: 10px; font-weight: bold; color: #8a9bb0; letter-spacing: 2px; text-transform: uppercase;">SCAN TO VERIFY</div>

         <div style="margin: 0 auto; width: 300px; background: rgba(255,149,0,0.05); border: 1px solid rgba(255,149,0,0.15); border-radius: 10px; padding: 15px; text-align: center;">
            <span style="font-size: 10px; color: #c8d8e8; line-height: 1.6;">
               This identity instrument is issued by <br>
               <strong style="color: #FFF;">{{ $companyName ?? 'Andleeb Cluster of Services Pvt. Ltd.' }}</strong><br>
               Issued for secure access only.<br>
               If found, please return to a regional facility<br>
               or the <strong style="color: #FFF;">Residency Road HQ.</strong>
            </span>
         </div>
         <div style="margin-top: 25px;">
            <div style="display: inline-block; border: 1.5px solid #FF9500; border-radius: 30px; padding: 8px 20px; font-size: 12px; font-weight: bold; color: #FF9500; letter-spacing: 1px;">
               Emergency: {{ $companyEmergency ?? '9906766655' }}
            </div>
         </div>
      </div>

      <!-- Back Footer Anchor -->
      <div class="footer-lock-back2">
         <div style="margin: 0 25px; border-bottom: 1px solid rgba(255,149,0,0.15); padding-bottom: 50px; position: relative;">
            <table style="width: 100%; text-align: center;">
               <tr>
                  <td style="width: 100%; vertical-align: bottom;">
                     <div style="position: relative; height: 35px; width: 100%; margin-bottom: 4px; text-align: center;">
                        <div style="display: inline-block; position: relative; width: 80px; height: 35px;">
                           @if($sigBase64)
                             <img src="{{ $sigBase64 }}" style="position: absolute; left: 10px; top: 10px; height: 25px; width: auto; z-index: 10;">
                           @endif
                           @if($sealBase64)
                             <img src="{{ $sealBase64 }}" style="position: absolute; left: 30px; top: 0; height: 35px; width: auto; opacity: 0.6; z-index: 5;">
                           @endif
                        </div>
                     </div>
                     <div style="font-size: 8px; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase; line-height: 1.4;">
                        Verified By<br>Chief Operations Officer
                     </div>
                  </td>
               </tr>
            </table>
         </div>

         <div style="position: absolute; top: 100px; width: 360px; text-align: center; font-size: 8px; color: #8a9bb0; line-height: 1.5;">
            <span style="color: rgba(255,149,0,0.7); font-weight: bold; letter-spacing: 1px;">{{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</span><br>
            <span style="display:inline-block; margin-top:2px;">{{ $companyAddress ?? 'Tali Mohallah Sugin, Khag, Budgam – 193411' }}</span><br>
            {{ $companyPhone ?? '+91-9797287817' }} &nbsp; | &nbsp; {{ $companyEmail ?? 'info@andleebsurya.in' }}
         </div>
      </div>
    </div>
  </div>

</body>
</html>
