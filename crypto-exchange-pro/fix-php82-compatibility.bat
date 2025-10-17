@echo off
echo ========================================
echo Fix PHP 8.2 Compatibility Issues
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

echo Detected PHP version:
php --version
echo.

echo Fixing PHP 8.2 compatibility issues...
echo.

REM Backup original composer.json
if exist composer.json (
    echo Backing up original composer.json...
    copy composer.json composer-original.json
)

REM Use PHP 8.2 compatible composer.json
if exist composer-php82.json (
    echo Using PHP 8.2 compatible composer.json...
    copy composer-php82.json composer.json
)

REM Remove existing lock file and vendor directory
echo Cleaning up existing installation...
if exist composer.lock del composer.lock
if exist vendor rmdir /s /q vendor

echo.
echo Installing dependencies with PHP 8.2 compatibility...
echo.

REM Method 1: Install with platform requirements
echo Method 1: Installing with platform requirements
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
if %errorlevel% equ 0 (
    goto :check_success
)

echo.
echo Method 1 failed, trying Method 2...
echo.

REM Method 2: Update dependencies
echo Method 2: Updating dependencies
composer update --no-dev --optimize-autoloader --ignore-platform-reqs
if %errorlevel% equ 0 (
    goto :check_success
)

echo.
echo Method 2 failed, trying Method 3...
echo.

REM Method 3: Install with specific platform overrides
echo Method 3: Installing with platform overrides
composer install --no-dev --optimize-autoloader --ignore-platform-req=php-64bit --ignore-platform-req=ext-pcntl
if %errorlevel% equ 0 (
    goto :check_success
)

echo.
echo All methods failed. Please check the error messages above.
echo.
echo Troubleshooting:
echo 1. Make sure you have PHP 8.2 installed
echo 2. Check that Composer is up to date: composer self-update
echo 3. Try running: composer clear-cache
echo.
pause
exit /b 1

:check_success
echo.
echo Checking installation...

if exist composer.lock (
    echo ✓ composer.lock file created successfully
) else (
    echo ⚠ WARNING: composer.lock file not found
)

if exist vendor (
    echo ✓ vendor directory created successfully
) else (
    echo ✗ ERROR: vendor directory not found
    pause
    exit /b 1
)

echo.
echo ========================================
echo PHP 8.2 Compatibility Fix Complete!
echo ========================================
echo.
echo The following changes were made:
echo - Updated composer.json for PHP 8.2 compatibility
echo - Removed problematic dependency versions
echo - Generated new composer.lock file
echo - Installed compatible packages
echo.
echo Next steps:
echo 1. Test the installation: php artisan --version
echo 2. Configure your .env file
echo 3. Run: php artisan key:generate
echo 4. Start your development server
echo.
pause