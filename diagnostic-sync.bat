@echo off
cd /d "%~dp0"
echo Diagnostic en cours, patiente quelques secondes...
(
echo ===== %date% %time% =====
echo --- git ---
where git
git --version
echo --- statut ---
git status
git remote -v
git log -1 --oneline
echo --- programme de synchro en cours ? ---
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*auto-sync-git*' } | Select-Object ProcessId,Name,CommandLine | Format-List"
echo --- demarrage Windows ---
dir "%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
echo --- ancien journal ---
type "%LOCALAPPDATA%\TaskFlow\git-sync.log"
echo --- test d'un passage de synchro ---
git pull --rebase --autostash origin main
) > diagnostic.txt 2>&1
echo Termine. Tu peux fermer cette fenetre.
pause
