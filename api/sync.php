<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

$dataFile = __DIR__ . '/../sauvegarde/sync_data.json';
$lockFile = $dataFile . '.lock';
$dir = dirname($dataFile);
if (!is_dir($dir)) mkdir($dir, 0755, true);

// ── GET : lire les données partagées ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($dataFile)) {
        echo json_encode(['ok' => true, 'data' => null, 'savedAt' => null]);
        exit;
    }
    $content = file_get_contents($dataFile);
    $parsed  = json_decode($content, true);
    echo json_encode([
        'ok'      => true,
        'data'    => $parsed['data']    ?? null,
        'savedAt' => $parsed['savedAt'] ?? null,
        'device'  => $parsed['device']  ?? null,
    ]);
    exit;
}

// ── POST : écrire les données partagées ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = file_get_contents('php://input');
    if (!$body) { echo json_encode(['ok' => false, 'error' => 'Corps vide']); exit; }
    $incoming = json_decode($body, true);
    if (!$incoming) { echo json_encode(['ok' => false, 'error' => 'JSON invalide']); exit; }

    // Verrou simple pour éviter les écritures concurrentes
    $fp = fopen($lockFile, 'w');
    if (flock($fp, LOCK_EX | LOCK_NB)) {
        $payload = [
            'savedAt' => date('c'),
            'device'  => $incoming['device'] ?? 'inconnu',
            'data'    => $incoming['data']   ?? $incoming,
        ];
        file_put_contents($dataFile, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    @unlink($lockFile);

    echo json_encode(['ok' => true, 'savedAt' => $payload['savedAt']]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Méthode non supportée']);
