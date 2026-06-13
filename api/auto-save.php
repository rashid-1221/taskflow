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

// Nom unique avec secondes pour ne jamais écraser pendant la journée
$now      = new DateTime();
$filename = 'auto_' . $now->format('Y-m-d_H-i-s') . '.json';
$filepath = $saveDir . $filename;

file_put_contents($filepath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// ── Rétention : 1 sauvegarde par jour, 30 jours ─────────────────────────────
// 1. Grouper tous les fichiers auto_*.json par date (Y-m-d)
$files = glob($saveDir . 'auto_*.json') ?: [];
sort($files); // ordre chronologique (le format Y-m-d_H-i-s trie bien)

$byDay = [];
foreach ($files as $f) {
    $base = basename($f);
    // Format attendu : auto_YYYY-MM-DD_HH-II-SS.json
    if (preg_match('/^auto_(\d{4}-\d{2}-\d{2})_/', $base, $m)) {
        $byDay[$m[1]][] = $f;
    }
}

// Trier les jours du plus récent au plus ancien
krsort($byDay);

$deleted = 0;
$dayCount = 0;
$today = $now->format('Y-m-d');

foreach ($byDay as $day => $dayFiles) {
    sort($dayFiles); // ordre croissant dans la journée → dernier = plus récent
    $dayCount++;

    if ($day === $today) {
        // Aujourd'hui : garder les 3 dernières sauvegardes (éviter de tout perdre en cas de bug)
        $toDelete = array_slice($dayFiles, 0, max(0, count($dayFiles) - 3));
    } elseif ($dayCount <= 30) {
        // Jours passés dans les 30 jours : garder uniquement la dernière du jour
        $toDelete = array_slice($dayFiles, 0, count($dayFiles) - 1);
    } else {
        // Plus de 30 jours : tout supprimer
        $toDelete = $dayFiles;
    }

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
    'days'      => count($byDay),
    'remaining' => count($remaining),
    'files'     => array_map('basename', array_slice($remaining, -10))
], JSON_UNESCAPED_UNICODE);
