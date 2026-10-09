@echo off
REM Arrête et retire la synchro Git automatique de TaskFlow sur ce PC.
del "%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\TaskFlow-GitSync.vbs" 2>nul
powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \"Name='powershell.exe'\" | Where-Object { $_.CommandLine -like '*auto-sync-git.ps1*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force }"
echo [OK] Synchro Git automatique desinstallee.
pause
