<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$file = basename($_GET['file'] ?? '');
if (!$file || !preg_match('/^auto_[\d_-]+\.json$/', $file)) {
    echo json_encode(['error' => 'Fichier invalide']);
    exit;
}

$path = __DIR__ . '/../sauvegarde/daily/' . $file;
if (!file_exists($path)) {
    echo json_encode(['error' => 'Fichier introuvable']);
    exit;
}

echo file_get_contents($path);
