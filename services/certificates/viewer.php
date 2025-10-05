<?php
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


?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Document</title>
  <style>body{margin:0}</style>
</head>
<body>
  <div id="viewer-root" style="height:100vh"></div>
  <script>
    (async () => {
      const token = "<?php echo htmlspecialchars($token); ?>";
      const resp = await fetch('stream_pdf.php?token=' + encodeURIComponent(token));
      if (!resp.ok) {
        document.body.innerText = 'Failed to load document: ' + resp.status;
        return;
      }
      const blob = await resp.blob();
      const url = URL.createObjectURL(blob);

      const iframe = document.createElement('iframe');
      iframe.style.width='100%';
      iframe.style.height='100vh';

      iframe.src = url;
      document.getElementById('viewer-root').appendChild(iframe);
    })();
  </script>
</body>
</html>
<?php
