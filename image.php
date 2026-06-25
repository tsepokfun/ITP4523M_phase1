<?php
/**
 * image.php — Serve furniture image BLOB from database
 * Usage: /image.php?fid=1
 */
require_once __DIR__ . '/conn.php';

$fid = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;
if ($fid <= 0) { header('HTTP/1.0 404 Not Found'); exit; }

$stmt = mysqli_prepare($conn, "SELECT image FROM Furniture WHERE furniture_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $fid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);

if (!$row || $row['image'] === null || strlen($row['image']) === 0) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

$imageData = $row['image'];

// Detect MIME type from magic bytes
$mime = 'image/jpeg';
$head = substr($imageData, 0, 8);
if (strpos($head, "\x89PNG") === 0)       $mime = 'image/png';
elseif (strpos($head, 'GIF8') === 0)      $mime = 'image/gif';
elseif (strpos($head, "\xFF\xD8\xFF") === 0) $mime = 'image/jpeg';
elseif (strpos($head, 'RIFF') === 0)      $mime = 'image/webp';

header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($imageData));
header('Cache-Control: public, max-age=86400');
echo $imageData;
