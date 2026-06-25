<?php
/**
 * upload_images.php — Upload image PNGs to Furniture table as BLOB
 * Run: /Applications/XAMPP/xamppfiles/bin/php upload_images.php
 */
$dir = __DIR__ . '/image';
$mysqlBin = '/Applications/XAMPP/xamppfiles/bin/mysql';

for ($i = 1; $i <= 6; $i++) {
    $file = "$dir/$i.png";
    if (!file_exists($file)) {
        echo "MISSING: $i.png\n";
        continue;
    }

    $data = file_get_contents($file);
    $hex = bin2hex($data);
    $len = strlen($data);
    $tmpFile = sys_get_temp_dir() . "/mysql_upload_$i.sql";

    // Write SQL to temp file to avoid shell arg length limit
    $sql = "UPDATE Furniture SET image = 0x{$hex} WHERE furniture_id = {$i};\n";
    file_put_contents($tmpFile, $sql);

    // Execute via mysql CLI with input redirection
    $cmd = "$mysqlBin -u root projectDB < " . escapeshellarg($tmpFile) . " 2>&1";
    exec($cmd, $output, $exitCode);
    unlink($tmpFile);

    if ($exitCode === 0) {
        echo "OK: $i.png -> furniture_id $i ($len bytes)\n";
    } else {
        echo "FAIL: $i.png -> " . implode("\n", $output) . "\n";
    }
}
