<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$file  = __DIR__ . '/../data/pronostics.json';
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['nom'])) {
    echo json_encode(['ok' => false, 'error' => 'Nom manquant']);
    exit;
}

$list = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
if (!is_array($list)) $list = [];

$list = array_values(array_filter($list, fn($e) => $e['nom'] !== $input['nom']));

file_put_contents($file, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo json_encode(['ok' => true, 'total' => count($list)]);
