<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            width: 100%;
            height: 100%;
        }

        .certificate {
            background: white;
            width: 100%;
            min-height: 100%;
            position: relative;
        }

        .header {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            padding: 20px 30px;
            position: relative;
            padding-bottom: 30px;
        }

        .header-content {
            position: relative;
        }

        .seal {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 50%;
            border: 3px solid #0e7490;
            position: absolute;
            top: 0;
        }

        .seal-left {
            left: 20px;
        }

        .seal-right {
            right: 20px;
        }

        .seal img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .header-text {
            text-align: center;
            padding: 0 100px;
        }

        .header-text p {
            margin: 2px 0;
            font-size: 11pt;
            font-weight: bold;
            color: #000;
        }

        .header-text .main-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 8px 0;
            letter-spacing: 1px;
        }

        .main-content {
            padding: 20px 30px;
        }

        .content-wrapper {
            display: table;
            width: 100%;
        }

        .sidebar {
            display: table-cell;
            background: linear-gradient(180deg, #0891b2 0%, #06b6d4 100%);
            width: 200px;
            border-radius: 15px;
            padding: 20px 15px;
            color: white;
            vertical-align: top;
        }

        .photo-container {
            width: 130px;
            height: 130px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.5);
            text-align: center;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .official-name {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3px;
            line-height: 1.3;
        }

        .official-title {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .section-title {
            font-weight: bold;
            font-size: 10pt;
            margin: 15px 0 8px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 8px;
        }

        .officials-list {
            font-size: 9pt;
            line-height: 1.6;
            text-align: center;
        }

        .officials-list p {
            margin: 2px 0;
        }

        .content-area {
            display: table-cell;
            padding: 10px 20px;
            vertical-align: top;
        }

        .cert-title {
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 25px;
            letter-spacing: 2px;
        }

        .salutation {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 20px;
        }

        .cert-body {
            font-size: 11pt;
            line-height: 1.7;
            text-align: justify;
            margin-bottom: 25px;
        }

        .cert-body p {
            margin-bottom: 12px;
            text-indent: 30px;
        }

        .signature-section {
            margin-top: 40px;
            text-align: right;
        }

        .signature-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 10pt;
            margin-bottom: 2px;
        }

        .signature-note {
            font-size: 9pt;
            font-style: italic;
            margin-top: 3px;
        }

        .doc-stamp {
            text-align: right;
            font-size: 9pt;
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <div class="header-content">
                <div class="seal seal-left">
                    <img src="${SEAL_LEFT}" alt="Seal 1">
                </div>
                <div class="seal seal-right">
                    <img src="${SEAL_RIGHT}" alt="Seal 2">
                </div>
                <div class="header-text">
                    <p>REPUBLIC OF THE PHILIPPINES</p>
                    <p>PROVINCE OF PAMPANGA</p>
                    <p>MUNICIPALITY OF MEXICO</p>
                    <p>BARANGAY BALAS</p>
                    <p class="main-title">OFFICE OF THE PUNONG BARANGAY</p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-wrapper">
                <div class="sidebar">
                    <div class="photo-container">
                        ${QR_CODE}
                    </div>
                    
                    <div class="official-name">HON. RONNIE D. MANALOTO</div>
                    <div class="official-title">PUNONG BARANGAY</div>

                    <div class="section-title">KAGAWAD</div>
                    <div class="officials-list">
                        <p>RAYMOND T. SESE</p>
                        <p>JESSIE V. TAMBO</p>
                        <p>RAYMOND P. PINEDA</p>
                        <p>ISAGAN L. PABALAN</p>
                        <p>RAUL A. AGAD</p>
                        <p>BILLY P. ARCILLA</p>
                        <p>MONICA T. MANALOTO</p>
                    </div>

                    <div class="section-title">SK CHAIRMAN</div>
                    <div class="officials-list">
                        <p>EJ RON Y. LENON</p>
                    </div>

                    <div class="section-title">BRGY. SECRETARY</div>
                    <div class="officials-list">
                        <p>MERCEDITA M. PANGILINAN</p>
                    </div>

                    <div class="section-title">BRGY. TREASURER</div>
                    <div class="officials-list">
                        <p>LOIDA J. MANITI</p>
                    </div>
                </div>

                <div class="content-area">
                    <h1 class="cert-title">CERTIFICATE OF INDIGENCY</h1>
                    
                    <p class="salutation">TO WHOM IT MAY CONCERN:</p>
                    
                    <div class="cert-body">
                        <p>This is to certify that <strong>${first_name} ${middle_name} ${last_name}</strong>, <strong>${age}</strong> years of age, is a bonafide resident of Barangay Balas, Mexico, Pampanga.</p>
                        
                        <p>This certification is issued upon the request of the above-mentioned individual for whatever legal purpose it may serve.</p>
                        
                        <p><strong>${issued_date}</strong>, at the Barangay Hall of Balas, Mexico, Pampanga.</p>
                    </div>

                    <div class="signature-section">
                        <div class="signature-name">HON. RONNIE D. MANALOTO</div>
                        <div class="signature-title">Punong Barangay</div>
                        <div class="signature-note">(Not Valid Without Dry Seal &amp; Thumb Mark)</div>
                    </div>

                    <div class="doc-stamp">QR Code: ${qr_code}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>