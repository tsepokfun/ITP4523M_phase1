<?php
/**
 * image.php — Serve furniture image from /image/ folder
 * Usage: /image.php?fid=1
 * Files stored as: /image/{fid}.{png|jpg|jpeg|gif|webp}
 */
$fid = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;
if ($fid <= 0) { header('HTTP/1.0 404 Not Found'); exit; }

$baseDir = __DIR__ . '/image/';
$extensions = array('png', 'jpg', 'jpeg', 'gif', 'webp');
$filePath = null;
$ext = null;

foreach ($extensions as $e) {
    $testPath = $baseDir . $fid . '.' . $e;
    if (file_exists($testPath)) {
        $filePath = $testPath;
        $ext = $e;
        break;
    }
}

if ($filePath === null) {
    header('HTTP/1.0 404 Not Found');
    echo 'Image not found';
    exit;
}

$mimeTypes = array(
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
);
$mime = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'image/png';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=86400');
readfile($filePath);
