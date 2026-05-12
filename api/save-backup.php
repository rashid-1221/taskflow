<?php
/**
 * TaskFlow — Sauvegarde serveur
 * Manuel  : sauvegarde/manuel/taskflow-YYYY-MM-DD_HH-mm-ss.json
 *   • Aujourd'hui  : toutes les sauvegardes conservées
 *   • Jours passés : 1 seule (la dernière) par jour — 30 jours max
 * Journalier : sauvegarde/daily/taskflow-daily-YYYY-MM-DD_HH-mm.json
 *   • Géré par auto-save.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'error'=>'Méthode non autorisée']); exit;
}

$raw = file_get_contents('php://input');
$req = json_decode($raw, true);

if (!$req || empty($req['data'])) {
    echo json_encode(['ok'=>false,'error'=>'Données manquantes']); exit;
}

$data   = $req['data'];
$parsed = json_decode($data);
if (!$parsed) {
    echo json_encode(['ok'=>false,'error'=>'JSON invalide']); exit;
}

$type    = $req['type'] ?? 'manual';
$baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sauvegarde';
$pretty  = json_encode(json_decode($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ── Sauvegarde journalière automatique ──────────────────────────────────────
if ($type === 'daily') {
    $dailyDir = $baseDir . DIRECTORY_SEPARATOR . 'daily';
    if (!is_dir($dailyDir)) mkdir($dailyDir, 0755, true);

    $filename = 'taskflow-daily-' . date('Y-m-d') . '.json';
    $filepath = $dailyDir . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($filepath, $pretty);

    // Garder 7 dernières journalières
    $files = glob($dailyDir . DIRECTORY_SEPARATOR . 'taskflow-daily-*.json') ?: [];
    usort($files, fn($a,$b) => strcmp(basename($b), basename($a)));
    foreach (array_slice($files, 7) as $old) @unlink($old);

    echo json_encode(['ok'=>true,'type'=>'daily','path'=>'sauvegarde/daily/'.$filename,'ts'=>date('d/m/Y H:i')]);
    exit;
}

// ── Sauvegarde manuelle ─────────────────────────────────────────────────────
$manuelDir = $baseDir . DIRECTORY_SEPARATOR . 'manuel';
if (!is_dir($manuelDir)) mkdir($manuelDir, 0755, true);

// Nom unique avec secondes → jamais d'écrasement
$filename = 'taskflow-' . date('Y-m-d_H-i-s') . '.json';
$filepath = $manuelDir . DIRECTORY_SEPARATOR . $filename;

if (file_put_contents($filepath, $pretty) === false) {
    echo json_encode(['ok'=>false,'error'=>"Échec écriture : $filepath"]); exit;
}

// ── Nettoyage : garder TOUT du jour en cours, 1 seule par jour passé (30j) ──
$today   = date('Y-m-d');
$allFiles = glob($manuelDir . DIRECTORY_SEPARATOR . 'taskflow-????-??-??_??-??-??.json') ?: [];

// Grouper par jour
$byDay = [];
foreach ($allFiles as $f) {
    $base = basename($f);
    if (preg_match('/^taskflow-(\d{4}-\d{2}-\d{2})_/', $base, $m)) {
        $byDay[$m[1]][] = $f;
    }
}

$deleted = 0;
krsort($byDay); // plus récent en premier
$dayCount = 0;

foreach ($byDay as $day => $dayFiles) {
    sort($dayFiles); // ordre croissant → dernier = plus récent

    if ($day === $today) {
        // Aujourd'hui : garder toutes les sauvegardes, rien supprimer
        $dayCount++;
        continue;
    }

    $dayCount++;
    if ($dayCount > 31) {
        // Plus de 30 jours passés : tout supprimer
        foreach ($dayFiles as $f) { @unlink($f); $deleted++; }
    } else {
        // Jours passés : garder uniquement la dernière
        $toDelete = array_slice($dayFiles, 0, count($dayFiles) - 1);
        foreach ($toDelete as $f) { @unlink($f); $deleted++; }
    }
}

// Comptage final
$remaining = count(glob($manuelDir . DIRECTORY_SEPARATOR . 'taskflow-*.json') ?: []);
$todayFiles = isset($byDay[$today]) ? count($byDay[$today]) : 0;

echo json_encode([
    'ok'         => true,
    'type'       => 'manual',
    'path'       => 'sauvegarde/manuel/' . $filename,
    'size'       => strlen($pretty),
    'ts'         => date('d/m/Y H:i:s'),
    'todayCount' => $todayFiles,
    'total'      => $remaining,
    'deleted'    => $deleted,
]);
