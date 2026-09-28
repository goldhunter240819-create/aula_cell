@echo off
title Backup Aula Cell ke GitHub
color 0B
echo ========================================================
echo         BACKUP SISTEM KEUANGAN AULA CELL KE GITHUB
echo ========================================================
echo.
echo Memeriksa file yang berubah...
cd /d %~dp0
git add .
git status -s
echo.
set /p desc="Masukkan pesan/keterangan backup (tekan enter untuk 'Auto backup'): "
if "%desc%"=="" set desc=Auto backup Aula Cell

echo.
echo Sedang menyimpan perubahan...
git commit -m "%desc%"
echo.
echo Sedang mengupload ke GitHub...
git push
echo.
echo ========================================================
echo                 BACKUP SELESAI!
echo ========================================================
pause
