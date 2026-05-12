<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'POST requis']);
    exit;
}

$body = file_get_contents('php://input');
if (!$body) {
    echo json_encode(['ok' => false, 'error' => 'Corps vide']);
    exit;
}

$data = json_decode($body, true);
if (!$data) {
    echo json_encode(['ok' => false, 'error' => 'JSON invalide']);
    exit;
}

$saveDir = __DIR__ . '/../sauvegarde/daily/';
if (!is_dir($saveDir)) mkdir($saveDir, 0755, true);

// Nom unique avec secondes pour ne jamais écraser
$now      = new DateTime();
$filename = 'auto_' . $now->format('Y-m-d_H-i-s') . '.json';
$filepath = $saveDir . $filename;

file_put_contents($filepath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// ── Rétention : garder les 20 derniers snapshots ────────────────────────────
$files = glob($saveDir . 'auto_*.json') ?: [];
// Tri par nom (donc par date, le format Y-m-d_H-i-s trie bien)
sort($files);

$deleted = 0;
$keep = 20;
if (count($files) > $keep) {
    $toDelete = array_slice($files, 0, count($files) - $keep);
    foreach ($toDelete as $f) {
        @unlink($f);
        $deleted++;
    }
}

$remaining = glob($saveDir . 'auto_*.json') ?: [];
sort($remaining);

echo json_encode([
    'ok'        => true,
    'saved'     => $filename,
    'deleted'   => $deleted,
    'remaining' => count($remaining),
    'files'     => array_map('basename', array_slice($remaining, -10))
], JSON_UNESCAPED_UNICODE);
