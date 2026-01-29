@echo off
echo ========================================
echo   Configuring PHP
echo ========================================
echo.

REM Check if php.ini exists
if exist "C:\php\php.ini" (
    echo php.ini already exists!
    echo.
    choice /C YN /M "Do you want to reconfigure it"
    if errorlevel 2 goto :end
)

REM Copy php.ini-development to php.ini
if exist "C:\php\php.ini-development" (
    echo Copying php.ini-development to php.ini...
    copy "C:\php\php.ini-development" "C:\php\php.ini" >nul
    echo ✅ php.ini created!
) else (
    echo ERROR: php.ini-development not found in C:\php
    pause
    exit /b 1
)

echo.
echo ========================================
echo   Next Steps:
echo ========================================
echo.
echo 1. Open C:\php\php.ini in Notepad
echo 2. Find and uncomment (remove ; from start):
echo    - extension=mysqli
echo    - extension=pdo_mysql
echo    - extension=curl
echo    - extension=openssl
echo    - extension=mbstring
echo 3. Find allow_url_fopen and set to: On
echo 4. Save the file
echo.
echo ========================================
pause
