@echo off
REM ============================================================
REM  ONESHOT - Secure Academic Portal (Windows + XAMPP MySQL)
REM  Cukup klik 2x file ini: MySQL dinyalakan, DB disiapkan,
REM  lalu server jalan di http://127.0.0.1:8000
REM  Login demo: dosenpbkk@its.ac.id / password
REM ============================================================
cd /d "%~dp0"

set MYSQLADMIN=C:\xampp\mysql\bin\mysqladmin.exe
set MYSQL=C:\xampp\mysql\bin\mysql.exe
set MYSQLD=C:\xampp\mysql\bin\mysqld.exe
set MYINI=C:\xampp\mysql\bin\my.ini

echo [1/3] Mengecek MySQL...
if exist "%MYSQLADMIN%" (
    "%MYSQLADMIN%" -u root status >nul 2>&1
    if errorlevel 1 (
        echo MySQL belum jalan, menyalakan XAMPP MySQL...
        start "" /min "%MYSQLD%" --defaults-file="%MYINI%" --standalone --console
        echo Menunggu MySQL siap...
        for /L %%i in (1,1,15) do (
            timeout /t 1 /nobreak >nul
            "%MYSQLADMIN%" -u root status >nul 2>&1
            if not errorlevel 1 goto mysql_ok
        )
        echo PERINGATAN: MySQL belum merespons. Pastikan XAMPP terinstal atau nyalakan manual via XAMPP Control Panel.
        goto setup
        :mysql_ok
        echo MySQL OK.
    ) else (
        echo MySQL OK.
    )
) else (
    echo XAMPP tidak ditemukan di C:\xampp, diasumsikan MySQL sudah jalan di 127.0.0.1:3306.
)

:setup
echo [2/3] Menyiapkan database (migrate + seed bila perlu)...
if exist "%MYSQL%" (
    "%MYSQL%" -u root -e "CREATE DATABASE IF NOT EXISTS academic_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
)
php artisan migrate --seed --force
if errorlevel 1 (
    echo.
    echo GAGAL menyiapkan database. Kemungkinan MySQL belum jalan.
    echo Nyalakan MySQL dulu (XAMPP Control Panel -^> Start MySQL), lalu jalankan file ini lagi.
    pause
    exit /b 1
)

echo [3/3] Menjalankan server...
echo Buka http://127.0.0.1:8000  ^|  Login: dosenpbkk@its.ac.id / password
php artisan serve --host=127.0.0.1 --port=8000
