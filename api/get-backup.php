<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$path = $_GET['path'] ?? '';

// Sécurité : n'autoriser que les chemins dans sauvegarde/
$allowed = ['sauvegarde/manuel/', 'sauvegarde/daily/'];
$safe = false;
foreach ($allowed as $prefix) {
    if (strpos($path, $prefix) === 0) { $safe = true; break; }
}

if (!$safe || strpos($path, '..') !== false) {
    echo json_encode(['ok'=>false,'error'=>'Chemin non autorisé']); exit;
}

$fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

if (!file_exists($fullPath)) {
    echo json_encode(['ok'=>false,'error'=>'Fichier introuvable']); exit;
}

$raw = file_get_contents($fullPath);
$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(['ok'=>false,'error'=>'JSON invalide']); exit;
}

echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE);
