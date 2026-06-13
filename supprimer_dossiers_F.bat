@echo off
chcp 65001 >nul
setlocal

echo ============================================================
echo  Suppression de dossiers avec chemins/noms trop longs
echo ============================================================
echo.
echo Dossiers cibles :
echo   1. F:\Partage_i.bouyahia_NEUF
echo   2. F:\Partage i.bouyahiya
echo.

if exist "F:\Partage_i.bouyahia_NEUF" (echo   [TROUVE]      F:\Partage_i.bouyahia_NEUF) else (echo   [INTROUVABLE] F:\Partage_i.bouyahia_NEUF)
if exist "F:\Partage i.bouyahiya"     (echo   [TROUVE]      F:\Partage i.bouyahiya)     else (echo   [INTROUVABLE] F:\Partage i.bouyahiya)
echo.
echo ATTENTION : suppression DEFINITIVE (pas de corbeille) !
echo.
set /p REP="Taper OUI pour confirmer la suppression : "
if /i not "%REP%"=="OUI" (
  echo Annule.
  pause
  exit /b
)

echo.
echo --- Methode 1 : suppression via prefixe long-path \\?\ ---
if exist "F:\Partage_i.bouyahia_NEUF" rd /s /q "\\?\F:\Partage_i.bouyahia_NEUF"
if exist "F:\Partage i.bouyahiya"     rd /s /q "\\?\F:\Partage i.bouyahiya"

echo.
echo --- Methode 2 (si necessaire) : vidage par robocopy miroir ---
if exist "F:\Partage_i.bouyahia_NEUF" (
  mkdir "%TEMP%\dossier_vide_tf" 2>nul
  robocopy "%TEMP%\dossier_vide_tf" "F:\Partage_i.bouyahia_NEUF" /MIR /NFL /NDL /NJH /NJS /NC /NS /NP
  rd /s /q "F:\Partage_i.bouyahia_NEUF"
)
if exist "F:\Partage i.bouyahiya" (
  mkdir "%TEMP%\dossier_vide_tf" 2>nul
  robocopy "%TEMP%\dossier_vide_tf" "F:\Partage i.bouyahiya" /MIR /NFL /NDL /NJH /NJS /NC /NS /NP
  rd /s /q "F:\Partage i.bouyahiya"
)
rd /q "%TEMP%\dossier_vide_tf" 2>nul

echo.
echo --- Resultat ---
if exist "F:\Partage_i.bouyahia_NEUF" (echo   [ECHEC]    F:\Partage_i.bouyahia_NEUF existe encore) else (echo   [SUPPRIME] F:\Partage_i.bouyahia_NEUF)
if exist "F:\Partage i.bouyahiya"     (echo   [ECHEC]    F:\Partage i.bouyahiya existe encore)     else (echo   [SUPPRIME] F:\Partage i.bouyahiya)
echo.
echo Si un dossier resiste encore : fichiers verrouilles par un programme
echo ouvert, ou droits insuffisants (relancer en tant qu'administrateur).
echo.
pause
