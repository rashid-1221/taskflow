<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$baseDir   = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sauvegarde';
$manuelDir = $baseDir . DIRECTORY_SEPARATOR . 'manuel';
$dailyDir  = $baseDir . DIRECTORY_SEPARATOR . 'daily';

$files = [];

// ── Sauvegardes manuelles ────────────────────────────────────────────────────
if (is_dir($manuelDir)) {
    foreach (glob($manuelDir . DIRECTORY_SEPARATOR . 'taskflow-*.json') ?: [] as $f) {
        $files[] = [
            'name'   => basename($f),
            'path'   => 'sauvegarde/manuel/' . basename($f),
            'folder' => 'Manuel',
            'size'   => _humanSize(filesize($f)),
            'date'   => date('d/m/Y H:i', filemtime($f)),
            'mtime'  => filemtime($f),
        ];
    }
}

// ── Auto-saves ───────────────────────────────────────────────────────────────
if (is_dir($dailyDir)) {
    foreach (glob($dailyDir . DIRECTORY_SEPARATOR . 'auto_*.json') ?: [] as $f) {
        $files[] = [
            'name'   => basename($f),
            'path'   => 'sauvegarde/daily/' . basename($f),
            'folder' => 'Auto',
            'size'   => _humanSize(filesize($f)),
            'date'   => date('d/m/Y H:i', filemtime($f)),
            'mtime'  => filemtime($f),
        ];
    }
}

// Tri décroissant (plus récent en premier)
usort($files, fn($a,$b) => $b['mtime'] - $a['mtime']);

// Retirer mtime du résultat
foreach ($files as &$f) unset($f['mtime']);

echo json_encode(['ok' => true, 'files' => $files], JSON_UNESCAPED_UNICODE);

function _humanSize($bytes) {
    if ($bytes < 1024)       return $bytes . ' o';
    if ($bytes < 1048576)    return round($bytes/1024, 1) . ' Ko';
    return round($bytes/1048576, 1) . ' Mo';
}
