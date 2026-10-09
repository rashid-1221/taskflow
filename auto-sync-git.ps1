# ════════════════════════════════════════════════════════════════════════════
#  TaskFlow — Synchronisation Git automatique
#  - Récupère (pull) les changements de GitHub au démarrage puis toutes les 60 s
#  - Envoie (commit + push) tes modifications de code automatiquement,
#    20 s après la dernière modification d'un fichier (pas de commit à moitié écrit)
#  Lancé en arrière-plan par installer-sync-auto.bat (au démarrage de Windows).
#  Journal : %LOCALAPPDATA%\TaskFlow\git-sync.log
# ════════════════════════════════════════════════════════════════════════════
param(
  [int]$Interval   = 60,   # secondes entre deux vérifications
  [int]$QuietDelay = 20    # secondes sans modification avant de commit
)

$repo = $PSScriptRoot
$logDir = Join-Path $env:LOCALAPPDATA 'TaskFlow'
New-Item -ItemType Directory -Force -Path $logDir | Out-Null
$log = Join-Path $repo 'git-sync.log'   # journal dans le dossier (ignoré par git)

function Log($msg) {
  $line = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')  $msg"
  Add-Content -Path $log -Value $line -Encoding UTF8
  # garde le journal léger
  if ((Get-Item $log).Length -gt 500KB) { Get-Content $log -Tail 2000 | Set-Content $log -Encoding UTF8 }
}

# Trouver git
$git = (Get-Command git -ErrorAction SilentlyContinue).Source
if (-not $git) { $git = 'C:\Program Files\Git\cmd\git.exe' }
if (-not (Test-Path $git)) { Log "ERREUR : git introuvable"; exit 1 }

# Une seule instance à la fois
$mutex = New-Object System.Threading.Mutex($false, 'Local\TaskFlowGitSync')
if (-not $mutex.WaitOne(0)) { exit 0 }

function G { & $git -C $repo @args 2>&1 | Out-String }

Log "Démarrage de la synchro ($env:COMPUTERNAME) sur $repo"

while ($true) {
  try {
    # 1. Modifications locales ?
    $status = (G status --porcelain).Trim()
    if ($status) {
      # Attendre que l'édition soit terminée (aucun fichier modifié depuis $QuietDelay s)
      $recent = Get-ChildItem -Path $repo -Recurse -File -Force -ErrorAction SilentlyContinue |
        Where-Object { $_.FullName -notmatch '\\\.git\\|\\sauvegarde\\' -and
                       $_.LastWriteTime -gt (Get-Date).AddSeconds(-$QuietDelay) }
      if (-not $recent) {
        G add -A | Out-Null
        $msg = "Auto-sync $(Get-Date -Format 'yyyy-MM-dd HH:mm') ($env:COMPUTERNAME)"
        $out = G commit -m $msg
        Log "Commit : $msg"
      }
    }

    # 2. Récupérer GitHub (rebase pour garder un historique propre)
    $out = G pull --rebase --autostash origin main
    if ($LASTEXITCODE -ne 0) {
      G rebase --abort | Out-Null
      Log "CONFLIT au pull — résolution manuelle nécessaire :`n$out"
    } elseif ($out -notmatch 'up to date|à jour') {
      Log "Pull : $($out.Trim())"
    }

    # 3. Envoyer si on a des commits en avance
    $ahead = (G rev-list --count origin/main..HEAD).Trim()
    if ($ahead -match '^\d+$' -and [int]$ahead -gt 0) {
      $out = G push origin main
      if ($LASTEXITCODE -eq 0) { Log "Push : $ahead commit(s) envoyé(s)" }
      else { Log "ERREUR push :`n$out" }
    }
  } catch {
    Log "ERREUR : $_"
  }
  Start-Sleep -Seconds $Interval
}
