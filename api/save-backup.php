<?php
/**
 * TaskFlow — Sauvegarde serveur
 * - Manuel  : /sauvegarde/taskflow-YYYY-MM-DD-HHmm.json  (7 max = 1 semaine)
 * - Journalier : /sauvegarde/daily/taskflow-daily-YYYY-MM-DD.json (7 max = 1 semaine)
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false, 'error'=>'Méthode non autorisée']); exit;
}

$raw = file_get_contents('php://input');
$req = json_decode($raw, true);

if (!$req || empty($req['data'])) {
    echo json_encode(['ok'=>false, 'error'=>'Données manquantes']); exit;
}

$data   = $req['data'];
$parsed = json_decode($data);
if (!$parsed) {
    echo json_encode(['ok'=>false, 'error'=>'JSON invalide']); exit;
}

$type    = isset($req['type']) ? $req['type'] : 'manual';
$baseDir = 'C:\\MAMP\\htdocs\\bourse de casablanca\\Claude\\Projet notes\\sauvegarde';

// Créer le dossier principal si nécessaire
if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0755, true)) {
        echo json_encode(['ok'=>false, 'error'=>"Impossible de créer $baseDir"]); exit;
    }
}

$pretty = json_encode(json_decode($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ── Sauvegarde journalière (16h-17h) ────────────────────────────────────────
if ($type === 'daily') {
    $dailyDir = $baseDir . DIRECTORY_SEPARATOR . 'daily';
    if (!is_dir($dailyDir)) {
        if (!mkdir($dailyDir, 0755, true)) {
            echo json_encode(['ok'=>false, 'error'=>"Impossible de créer $dailyDir"]); exit;
        }
    }

    $filename = 'taskflow-daily-' . date('Y-m-d') . '.json';
    $filepath = $dailyDir . DIRECTORY_SEPARATOR . $filename;

    if (file_put_contents($filepath, $pretty) === false) {
        echo json_encode(['ok'=>false, 'error'=>'Échec écriture fichier']); exit;
    }

    // Garder seulement les 7 derniers fichiers (1 semaine)
    $files = glob($dailyDir . DIRECTORY_SEPARATOR . 'taskflow-daily-*.json');
    if ($files) {
        usort($files, fn($a,$b) => filemtime($b) - filemtime($a));
        foreach (array_slice($files, 7) as $old) { @unlink($old); }
    }

    echo json_encode([
        'ok'   => true,
        'type' => 'daily',
        'path' => 'sauvegarde/daily/' . $filename,
        'size' => strlen($pretty),
        'ts'   => date('d/m/Y H:i'),
    ]);
    exit;
}

// ── Sauvegarde manuelle : directement dans /sauvegarde/ ─────────────────────
$filename = 'taskflow-' . date('Y-m-d-Hi') . '.json';
$filepath = $baseDir . DIRECTORY_SEPARATOR . $filename;

if (file_put_contents($filepath, $pretty) === false) {
    echo json_encode(['ok'=>false, 'error'=>"Échec d'écriture dans $filepath"]); exit;
}

// Garder seulement les 7 dernières sauvegardes manuelles (rétention 1 semaine)
$files = glob($baseDir . DIRECTORY_SEPARATOR . 'taskflow-????-??-??-????.json');
if ($files) {
    usort($files, fn($a,$b) => filemtime($b) - filemtime($a));
    foreach (array_slice($files, 7) as $old) { @unlink($old); }
}

echo json_encode([
    'ok'    => true,
    'type'  => 'manual',
    'path'  => 'sauvegarde/' . $filename,
    'count' => count(glob($baseDir . DIRECTORY_SEPARATOR . 'taskflow-????-??-??-????.json') ?: []),
    'size'  => strlen($pretty),
    'ts'    => date('d/m/Y H:i'),
]);
