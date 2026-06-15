<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$file = __DIR__ . '/../data/pronostics.json';
$list = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
echo json_encode(is_array($list) ? $list : [], JSON_UNESCAPED_UNICODE);
