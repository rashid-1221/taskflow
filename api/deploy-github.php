<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$gitExe  = 'C:\\Program Files\\Git\\bin\\git.exe';
$repoDir = 'C:\\MAMP\\htdocs\\taskflow';
$log     = [];
$ok      = true;

function runGit($gitExe, $repoDir, $args) {
    $cmd    = "\"$gitExe\" -C \"$repoDir\" $args 2>&1";
    $output = [];
    $code   = 0;
    exec($cmd, $output, $code);
    return ['cmd' => $args, 'output' => implode("\n", $output), 'code' => $code];
}

// 1. git add -A
$r = runGit($gitExe, $repoDir, 'add -A');
$log[] = $r;

// 2. git status --short (pour savoir s'il y a des changements)
$status = runGit($gitExe, $repoDir, 'status --short');
$log[] = $status;
$hasChanges = trim($status['output']) !== '';

// 3. git commit (seulement s'il y a des changements)
if ($hasChanges) {
    $date = date('Y-m-d H:i');
    $msg  = "Deploy $date";
    $r = runGit($gitExe, $repoDir, "commit -m \"$msg\"");
    $log[] = $r;
    if ($r['code'] !== 0) $ok = false;
}

// 4. git push origin main
$r = runGit($gitExe, $repoDir, 'push origin main');
$log[] = $r;
if ($r['code'] !== 0) $ok = false;

// Résumé
$pushed = trim($r['output']);
echo json_encode([
    'ok'          => $ok,
    'hasChanges'  => $hasChanges,
    'pushOutput'  => $pushed,
    'log'         => $log
], JSON_UNESCAPED_UNICODE);
