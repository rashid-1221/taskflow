@echo off
chcp 65001 >nul
REM ═══════════════════════════════════════════════════════════════════
REM  Installe la synchro Git automatique de TaskFlow sur CE PC.
REM  - Démarre en arrière-plan à chaque ouverture de session Windows
REM  - Se lance aussi immédiatement
REM  A exécuter une fois sur chaque PC où tu modifies le code.
REM ═══════════════════════════════════════════════════════════════════
set "SCRIPT=%~dp0auto-sync-git.ps1"
set "STARTUP=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
set "VBS=%STARTUP%\TaskFlow-GitSync.vbs"

> "%VBS%" echo CreateObject("WScript.Shell").Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File ""%SCRIPT%""", 0, False

if exist "%VBS%" (
  echo [OK] Synchro Git automatique installee au demarrage de Windows.
  wscript "%VBS%"
  echo [OK] Synchro lancee. Journal : %LOCALAPPDATA%\TaskFlow\git-sync.log
) else (
  echo [ERREUR] Impossible de creer %VBS%
)
pause
