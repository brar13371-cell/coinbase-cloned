@echo off
echo ========================================
echo Composer Install - Windows Fix
echo ========================================
echo.

REM Check if composer is installed
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Composer is not installed or not in PATH
    echo Please install Composer and add it to your PATH
    pause
    exit /b 1
)

echo Installing PHP dependencies with Windows compatibility fixes...
echo.

REM Method 1: Ignore platform requirements
echo Method 1: Installing with --ignore-platform-reqs
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
if %errorlevel% equ 0 (
    echo.
    echo SUCCESS: Dependencies installed successfully!
    echo.
    goto :success
)

echo.
echo Method 1 failed, trying Method 2...
echo.

REM Method 2: Use Windows-compatible composer.json
echo Method 2: Using Windows-compatible composer.json
if exist composer-windows.json (
    copy composer.json composer-original.json
    copy composer-windows.json composer.json
    composer install --no-dev --optimize-autoloader
    if %errorlevel% equ 0 (
        echo.
        echo SUCCESS: Dependencies installed with Windows-compatible config!
        echo.
        goto :success
    ) else (
        copy composer-original.json composer.json
        del composer-original.json
    )
)

echo.
echo Method 2 failed, trying Method 3...
echo.

REM Method 3: Install without problematic packages
echo Method 3: Installing without problematic packages
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
if %errorlevel% equ 0 (
    echo.
    echo SUCCESS: Dependencies installed without problematic packages!
    echo.
    goto :success
)

echo.
echo All methods failed. Please check the error messages above.
echo.
echo Troubleshooting:
echo 1. Make sure PHP 8.1+ is installed and in PATH
echo 2. Make sure Composer is installed and in PATH
echo 3. Check your internet connection
echo 4. Try running: composer clear-cache
echo.
pause
exit /b 1

:success
echo ========================================
echo Installation completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Run: php artisan key:generate
echo 2. Configure your .env file
echo 3. Import database schema
echo 4. Run: php artisan serve
echo.
pause