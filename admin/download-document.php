<?php
if (!isset($_GET['file'])) {
    die("No file specified.");
}
$file = basename($_GET['file']); // prevent path traversal
$path = __DIR__ . "/../public/uploads/generated_docs/" . $file;

if (!file_exists($path)) {
    die("File not found.");
}

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=" . $file);
header("Content-Length: " . filesize($path));
readfile($path);
exit;
