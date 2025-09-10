<?php
// viewer.php
require 'db.php';
session_start();

$token = $_GET['token'] ?? '';
if (!$token || empty($_SESSION['viewer_tokens'][$token])) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}
$data = $_SESSION['viewer_tokens'][$token];
if ($data['expires'] < time()) {
    unset($_SESSION['viewer_tokens'][$token]);
    http_response_code(403);
    echo "Token expired";
    exit;
}
$request_id = intval($data['request_id']);

// fetch request to get pdf path
$stmt = $mysqli->prepare("SELECT document_file_path, status FROM document_requests WHERE id = ?");
$stmt->bind_param('i', $request_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { http_response_code(404); exit; }
$r = $res->fetch_assoc();
$stmt->close();

if ($r['status'] !== 'Approved') {
    http_response_code(403);
    echo "Not allowed";
    exit;
}

$pdfPath = $r['document_file_path'];
if (!file_exists($pdfPath)) { http_response_code(404); echo "File not found"; exit; }

// Option A: Serve the PDF file inline with headers (this allows browser's PDF viewer).
// But we want to show PDF.js viewer with download disabled — we'll embed PDF inside the viewer below.

// Provide a short page that loads PDF.js and disables download button (client-side)
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Document</title>
  <style>body{margin:0}</style>
</head>
<body>
  <!-- Simple PDF.js embed (assumes viewer is at /pdfjs/web/viewer.html hosted) -->
  <!-- If you host PDF.js viewer locally, you can pass the file via a protected endpoint. We'll use fetch to stream blob and attach to viewer -->
  <div id="viewer-root" style="height:100vh"></div>
  <script>
    // fetch the PDF as blob from protected endpoint stream_pdf.php
    (async () => {
      const token = "<?php echo htmlspecialchars($token); ?>";
      const resp = await fetch('/stream_pdf.php?token=' + encodeURIComponent(token));
      if (!resp.ok) {
        document.body.innerText = 'Failed to load document: ' + resp.status;
        return;
      }
      const blob = await resp.blob();
      // create object URL and open in an <iframe> that loads PDF.js viewer with the blob as data
      const url = URL.createObjectURL(blob);
      // If you have a local PDF.js viewer, you can call: /pdfjs/web/viewer.html?file=<url>
      // But many PDF.js builds disallow blob: URLs. A simpler approach: embed <iframe> pointing to a minimal in-page PDF viewer using <embed>.
      // We'll embed via <embed> and hope browser shows inline viewer (download controls may still exist).
      // To reduce download control visibility, use the PDF.js viewer hosted at /pdfjs/web/viewer.html?file=<url> (recommended).
      const iframe = document.createElement('iframe');
      iframe.style.width='100%';
      iframe.style.height='100vh';
      // If you hosted pdfjs web viewer at /pdfjs/web/viewer.html:
      // iframe.src = '/pdfjs/web/viewer.html?file=' + encodeURIComponent(url) + '&disableDownload=true';
      // Fallback: use embed:
      iframe.src = url;
      document.getElementById('viewer-root').appendChild(iframe);
    })();
  </script>
</body>
</html>
<?php
// Do NOT destroy or invalidate token yet if you want multiple scans. If you want single-use: unset($_SESSION['viewer_tokens'][$token]);
