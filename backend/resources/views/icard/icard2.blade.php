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
      font-family: Helvetica, sans-serif !important;
    }

    @page { 
      size: 360px 620px;
      margin: 0; 
    }

    body {
      background: #f5f3ee;
      margin: 0; 
      padding: 0;
    }

    .pdf-page {
      width: 360px;
      height: 620px;
      page-break-after: always;
      position: relative;
      overflow: hidden;
      background: #f5f3ee;
    }
    
    .pdf-page-back {
      background: #04111F;
      page-break-after: auto;
    }

    .gold-frame {
      position: absolute;
      top: 8px;
      left: 8px;
      right: 8px;
      bottom: 8px;
      border: 1px solid rgba(255,149,0,0.3);
      border-radius: 15px;
      pointer-events: none;
      z-index: 50;
    }

    /* FRONT PAGE */
    .front-dark-top {
      position: absolute;
      top: 0;
      left: 0;
      width: 360px;
      height: 260px;
      background: #04111F;
      z-index: 1;
    }

    .front-curve {
      position: absolute;
      bottom: -20px;
      left: -20px;
      width: 400px;
      height: 45px;
      background: #f5f3ee;
      border-radius: 50% 50% 0 0 / 100% 100% 0 0;
      z-index: 5;
    }

    .info-table {
      position: absolute;
      top: 310px;
      left: 15px;
      width: 330px;
      border-collapse: separate;
      border-spacing: 10px;
      z-index: 10;
    }

    .info-td {
      background: #FFFFFF;
      border: 1px solid rgba(4,17,31,0.07);
      border-radius: 8px;
      padding: 8px 10px;
      width: 50%;
      vertical-align: middle;
    }

  </style>
</head>
<body>

  <!-- ════════ FRONT ════════ -->
  <div class="pdf-page">
    <div class="gold-frame"></div>

    <div class="front-dark-top">
      <div class="front-curve"></div>

      <!-- Header -->
      <div style="position: absolute; top: 25px; left: 25px; width: 42px; height: 42px; background: #FF9500; border-radius: 8px; text-align: center; z-index: 10;">
         @if($logoBase64)
           <img src="{{ $logoBase64 }}" style="width: 24px; height: 24px; margin-top: 9px;">
         @endif
      </div>

      <div style="position: absolute; top: 30px; left: 82px; width: 250px; z-index: 10;">
         <div style="color: #FFF; font-size: 11px; font-weight: bold; font-family: serif !important; letter-spacing: 0.5px; line-height: 1.3">
            {{ $companyName ?? 'ANDLEEB CLUSTER OF SERVICES PVT. LTD.' }}
         </div>
         <div style="color: #FF9500; font-size: 7.5px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin-top: 4px;">
            Affiliated with: {{ $affiliatedPartner ?? 'Malik Surya Tech Agency' }}
         </div>
      </div>

      <!-- Photo -->
      <div style="position: absolute; top: 100px; left: 125px; width: 110px; height: 110px; border-radius: 50%; background: #FF9500; z-index: 10;">
        <div style="position: absolute; top: 3px; left: 3px; width: 104px; height: 104px; border: 3px solid #04111F; border-radius: 50%; background: #0a1f35; overflow: hidden; text-align: center;">
           @if($profilePhotoBase64)
             <img src="{{ $profilePhotoBase64 }}" style="width: 104px; height: 104px; object-fit: cover; border-radius: 50%;">
           @else
             <div style="line-height: 104px; font-size: 32px; font-weight: bold; color: #FF9500;">{{ $initials ?? 'SM' }}</div>
           @endif
        </div>
      </div>
    </div>

    <!-- Badge -->
    <div style="position: absolute; top: 200px; left: 0; width: 360px; text-align: center; z-index: 20;">
       <div style="display: inline-block; background: #FF9500; color: #04111F; font-size: 10.5px; font-weight: bold; padding: 6px 18px; border-radius: 12px; letter-spacing: 1.5px; text-transform: uppercase;">
          {{ $designation ?? 'Administrator' }}
       </div>
    </div>

    <!-- Name -->
    <div style="position: absolute; top: 255px; left: 0; width: 360px; text-align: center; font-size: 20px; font-weight: bold; color: #04111F; font-family: serif !important;">
       {{ $user->name ?? 'User Name' }}
    </div>

    <!-- Divider -->
    <div style="position: absolute; top: 290px; left: 30px; width: 300px; height: 1px; background: rgba(255,149,0,0.4); z-index: 10;"></div>
    <div style="position: absolute; top: 288px; left: 177px; width: 5px; height: 5px; background: #FF9500; border-radius: 50%; z-index: 15;"></div>

    <!-- Table Details -->
    <table class="info-table">
      <tr>
        <td class="info-td">
           <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
               <div style="font-size: 8px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Employee ID</div>
               <div style="font-size: 13px; font-weight: bold; color: #FF9500; margin-top: 2px;">{{ $cardNumber ?? 'ADM-0002' }}</div>
           </div>
        </td>
        <td class="info-td">
           <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
               <div style="font-size: 8px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Date of Birth</div>
               <div style="font-size: 11px; font-weight: bold; color: #04111F; margin-top: 2px;">{{ $dob ?? '02 Feb 1980' }}</div>
           </div>
        </td>
      </tr>
      <tr>
        <td class="info-td">
           <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
               <div style="font-size: 8px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Father's Name</div>
               <div style="font-size: 11px; font-weight: bold; color: #04111F; margin-top: 2px; overflow: hidden; white-space: nowrap;">{{ $user->father_name ?? 'Habib Ullah' }}</div>
           </div>
        </td>
        <td class="info-td">
           <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
               <div style="font-size: 8px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Joining Date</div>
               <div style="font-size: 11px; font-weight: bold; color: #04111F; margin-top: 2px;">{{ $joiningDate ?? '11 Apr 2026' }}</div>
           </div>
        </td>
      </tr>
      <tr>
        <td class="info-td">
           <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
               <div style="font-size: 8px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Contact</div>
               <div style="font-size: 11px; font-weight: bold; color: #04111F; margin-top: 2px;">{{ $mobile ?? '9797287817' }}</div>
           </div>
        </td>
        <td class="info-td">
           <div style="border-left: 3px solid #FF9500; padding-left: 6px; height: 100%;">
               <div style="font-size: 8px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase;">Address</div>
               <div style="font-size: 9px; font-weight: bold; color: #04111F; margin-top: 2px; line-height: 1.2;">{{ $address ?? 'Pampore, Pulwama' }}</div>
           </div>
        </td>
      </tr>
    </table>

    <!-- Barcode -->
    <div style="position: absolute; top: 520px; left: 25px; width: 310px; height: 40px;">
       @if($barcodeBase64)
         <img src="{{ $barcodeBase64 }}" style="float: left; width: 170px; height: 32px;">
       @endif
       <div style="float: right; font-size: 10.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 1px; margin-top: 14px;">{{ $cardNumber ?? 'ADM-0002' }}</div>
    </div>

    <!-- Footer -->
    <div style="position: absolute; bottom: 0; left: 0; width: 360px; height: 40px; background: #04111F; z-index: 10;">
      <div style="float: left; padding: 14px 0 0 20px; font-size: 9px; font-weight: bold; color: #FF9500; text-transform: uppercase; letter-spacing: 1.5px;">
         <span style="display:inline-block; width:6px; height:6px; background:#22c55e; border-radius:50%; margin-right:4px; margin-bottom: 1px;"></span> VERIFIED IDENTITY
      </div>
      <div style="float: right; padding: 14px 20px 0 0; font-size: 8.5px; font-weight: bold; color: #8a9bb0; letter-spacing: 0.5px;">
         {{ $companyWebsite ?? 'andleebsurya.in' }}
      </div>
    </div>
  </div>

  <!-- ════════ BACK ════════ -->
  <div class="pdf-page pdf-page-back">
    <div class="gold-frame"></div>

    <div style="position: absolute; top: 25px; left: 30px; width: 300px; height: 50px; border-bottom: 1px solid rgba(255,149,0,0.15);">
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

    <div style="position: absolute; top: 110px; width: 360px; text-align: center;">
       <div style="display: inline-block; background: #FFFFFF; padding: 10px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1);">
          @if($qrBase64)
             <img src="{{ $qrBase64 }}" style="width: 140px; height: 140px;">
          @endif
       </div>
       <div style="margin-top: 14px; font-size: 10px; font-weight: bold; color: #8a9bb0; letter-spacing: 2px; text-transform: uppercase;">SCAN TO VERIFY</div>
    </div>

    <div style="position: absolute; top: 320px; left: 30px; width: 300px;">
       <div style="background: rgba(255,149,0,0.05); border: 1px solid rgba(255,149,0,0.15); border-radius: 10px; padding: 15px; text-align: center;">
          <span style="font-size: 10px; color: #c8d8e8; line-height: 1.6;">
             This identity instrument is issued by <br>
             <strong style="color: #FFF;">{{ $companyName ?? 'Andleeb Cluster of Services Pvt. Ltd.' }}</strong><br>
             Issued for secure access only.<br>
             If found, please return to a regional facility<br>
             or the <strong style="color: #FFF;">Residency Road HQ.</strong>
          </span>
       </div>
    </div>

    <div style="position: absolute; top: 430px; width: 360px; text-align: center;">
       <div style="display: inline-block; border: 1.5px solid #FF9500; border-radius: 30px; padding: 8px 20px; font-size: 12px; font-weight: bold; color: #FF9500; letter-spacing: 1px;">
          <span style="font-size: 14px; display: inline-block; vertical-align: middle; margin-right: 5px;">&#9990;</span> Emergency: {{ $companyEmergency ?? '9906766655' }}
       </div>
    </div>

    <div style="position: absolute; top: 495px; left: 25px; width: 310px; border-bottom: 1px solid rgba(255,149,0,0.15); padding-bottom: 55px;">
       <!-- Signatures -->
       <table style="width: 100%; text-align: center; margin-top: 15px;">
          <tr>
             <td style="width: 50%; vertical-align: bottom;">
                <div style="font-size: 8px; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase; line-height: 1.4;">
                   Issuing Authority<br>Principal Seal
                </div>
             </td>
             <td style="width: 50%; vertical-align: bottom;">
                <div style="position: relative; height: 35px; width: 100%; margin-bottom: 4px;">
                   @if($sigBase64)
                     <img src="{{ $sigBase64 }}" style="position: absolute; right: 15px; bottom: 0; height: 30px; width: auto; z-index: 10;">
                   @endif
                   @if($sealBase64)
                     <img src="{{ $sealBase64 }}" style="position: absolute; right: 35px; bottom: 0; height: 40px; width: auto; opacity: 0.6; z-index: 5;">
                   @endif
                </div>
                <div style="font-size: 8px; color: #8a9bb0; letter-spacing: 1px; text-transform: uppercase; line-height: 1.4;">
                   Verified By<br>Chief Operations Officer
                </div>
             </td>
          </tr>
       </table>
    </div>

    <div style="position: absolute; bottom: 25px; width: 360px; text-align: center; font-size: 8px; color: #8a9bb0; line-height: 1.5;">
       <span style="color: rgba(255,149,0,0.7); font-weight: bold; letter-spacing: 1px;">{{ $globalRegNo ?? 'REG/SMS/2026/0892' }}</span><br>
       <span style="display:inline-block; margin-top:2px;">{{ $companyAddress ?? 'Tali Mohallah Sugin, Khag, Budgam – 193411' }}</span><br>
       {{ $companyPhone ?? '+91-9797287817' }} &nbsp; | &nbsp; {{ $companyEmail ?? 'info@andleebsurya.in' }}
    </div>

  </div>

</body>
</html>
