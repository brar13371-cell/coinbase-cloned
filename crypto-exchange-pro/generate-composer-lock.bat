@echo off
echo ========================================
echo Generate Composer Lock File
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

echo Generating composer.lock file...
echo This ensures consistent dependency versions across all environments.
echo.

REM Method 1: Install with lock file generation
echo Method 1: Installing dependencies (generates lock file)
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
if %errorlevel% equ 0 (
    goto :check_lock
)

echo.
echo Method 1 failed, trying Method 2...
echo.

REM Method 2: Update to generate lock file
echo Method 2: Updating dependencies (generates/updates lock file)
composer update --no-dev --optimize-autoloader --ignore-platform-reqs
if %errorlevel% equ 0 (
    goto :check_lock
)

echo.
echo Method 2 failed, trying Method 3...
echo.

REM Method 3: Generate lock without installing
echo Method 3: Generating lock file without installing
composer install --no-install --ignore-platform-reqs
if %errorlevel% equ 0 (
    goto :check_lock
)

echo.
echo All methods failed. Please check the error messages above.
pause
exit /b 1

:check_lock
echo.
echo Checking if composer.lock was created...

if exist composer.lock (
    echo ✓ SUCCESS: composer.lock file created!
    echo.
    echo File details:
    dir composer.lock
    echo.
    echo The lock file contains exact versions of all dependencies.
    echo This ensures consistent installs across all environments.
    echo.
    echo Next steps:
    echo 1. Commit the composer.lock file to version control
    echo 2. Share with your team
    echo 3. Use 'composer install' for future installs
    echo.
) else (
    echo ✗ ERROR: composer.lock file was not created
    echo Please check the error messages above and try again.
    echo.
)

echo ========================================
pause