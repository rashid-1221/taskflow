<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$saveDir = __DIR__ . '/../sauvegarde/daily/';
$files   = glob($saveDir . 'auto_*.json') ?: [];
rsort($files); // plus récent en premier

$result = [];
foreach ($files as $f) {
    $base = basename($f);
    $size = filesize($f);
    // Lire label depuis le JSON sans tout charger
    $handle = fopen($f, 'r');
    $chunk  = fread($handle, 200);
    fclose($handle);
    preg_match('/"label"\s*:\s*"([^"]+)"/', $chunk, $m);
    preg_match('/"savedAt"\s*:\s*"([^"]+)"/', $chunk, $ts);
    $result[] = [
        'file'    => $base,
        'label'   => $m[1] ?? $base,
        'savedAt' => $ts[1] ?? '',
        'size'    => $size
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
