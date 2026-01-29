@echo off
echo ========================================
echo   CrossLife Development Server
echo ========================================
echo.

REM Check if PHP is installed
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH!
    echo.
    echo Please install PHP:
    echo 1. Download from: https://windows.php.net/download/
    echo 2. Extract to C:\php (or any folder)
    echo 3. Add PHP to Windows PATH
    echo 4. Restart Command Prompt
    echo.
    echo See PHP_SERVER_SETUP.md for detailed instructions
    echo.
    pause
    exit /b 1
)

echo PHP found! Starting server...
echo.
echo ========================================
echo   Server Information
echo ========================================
echo Server URL: http://localhost:8000
echo Sermons Page: http://localhost:8000/sermons.php
echo Admin Panel: http://localhost:8000/admin/login.php
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

REM Change to script directory
cd /d "%~dp0"

REM Start PHP built-in server
php -S localhost:8000

pause
