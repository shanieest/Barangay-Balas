<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Barangay Official Scanner</title>
  <script src="https://unpkg.com/html5-qrcode@2.4.9/minified/html5-qrcode.min.js"></script>
  <style>
    #reader { width: 100%; max-width: 500px; margin: auto; }
    #result { margin-top: 12px; }
  </style>
</head>
<body>
  <h3>Official Document Scanner</h3>
  <div id="reader"></div>
  <div id="result"></div>

  <script>
    // Replace with your scanner_key from scanner_keys table
    const SCANNER_KEY = 'replace-with-a-strong-random-key-ABC123xyz';

    function onScanSuccess(decodedText, decodedResult) {
      // We expect the QR to be the base64 payload string (payload_b64)
      console.log(`Scanned: ${decodedText}`);
      // Pause scanning
      html5QrcodeScanner.clear().then(() => {
        // Post to server verify endpoint
        fetch('/public/verify.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            scanner_key: SCANNER_KEY,
            scanned: decodedText
          })
        })
        .then(r => r.json())
        .then(res => {
          const rdiv = document.getElementById('result');
          if (!res.success) {
            rdiv.innerHTML = '<p style="color:red;">' + (res.message || 'Verification failed') + '</p>';
            return;
          }
          if (res.status === 'Approved' && res.document && res.document.pdf_url) {
            rdiv.innerHTML = `<p>Approved: ${res.document.document_type} for ${res.document.name}</p>
                              <p>Starting download...</p>`;
            // redirect to download link to force browser download
            window.location.href = res.document.pdf_url;
          } else {
            rdiv.innerHTML = `<p>Status: ${res.status}</p>
                              <p>Notes: ${res.notes ?? 'None'}</p>`;
          }
        })
        .catch(err => {
          document.getElementById('result').innerHTML = '<p style="color:red;">Network error</p>';
          console.error(err);
        });
      }).catch(err => console.error(err));
    }

    function onScanError(errorMessage) {
      // console.log('Scan error', errorMessage);
    }

    const html5QrcodeScanner = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        const cameraId = cameras[0].id;
        html5QrcodeScanner.start(cameraId, config, onScanSuccess, onScanError)
        .catch(err => console.error('start failed', err));
      } else {
        alert('No camera found');
      }
    }).catch(err => {
      console.error(err);
      alert('Camera permission needed');
    });
  </script>
</body>
</html>
