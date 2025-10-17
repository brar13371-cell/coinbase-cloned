# Composer Install - Windows Fix (PowerShell)
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Composer Install - Windows Fix" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if composer is installed
try {
    $composerVersion = composer --version 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Composer not found"
    }
    Write-Host "✓ Composer is installed" -ForegroundColor Green
} catch {
    Write-Host "✗ ERROR: Composer is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Composer and add it to your PATH" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Installing PHP dependencies with Windows compatibility fixes..." -ForegroundColor Yellow
Write-Host "This will generate composer.lock file for consistent dependencies..." -ForegroundColor Cyan
Write-Host ""

# Method 1: Ignore platform requirements
Write-Host "Method 1: Installing with --ignore-platform-reqs" -ForegroundColor Cyan
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "SUCCESS: Dependencies installed successfully!" -ForegroundColor Green
    Write-Host ""
    goto success
}

Write-Host ""
Write-Host "Method 1 failed, trying Method 2..." -ForegroundColor Yellow
Write-Host ""

# Method 2: Use Windows-compatible composer.json
Write-Host "Method 2: Using Windows-compatible composer.json" -ForegroundColor Cyan
if (Test-Path "composer-windows.json") {
    Copy-Item "composer.json" "composer-original.json"
    Copy-Item "composer-windows.json" "composer.json"
    composer install --no-dev --optimize-autoloader
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "SUCCESS: Dependencies installed with Windows-compatible config!" -ForegroundColor Green
        Write-Host ""
        goto success
    } else {
        Copy-Item "composer-original.json" "composer.json"
        Remove-Item "composer-original.json"
    }
}

Write-Host ""
Write-Host "Method 2 failed, trying Method 3..." -ForegroundColor Yellow
Write-Host ""

# Method 3: Install without problematic packages
Write-Host "Method 3: Installing without problematic packages" -ForegroundColor Cyan
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "SUCCESS: Dependencies installed without problematic packages!" -ForegroundColor Green
    Write-Host ""
    goto success
}

Write-Host ""
Write-Host "All methods failed. Please check the error messages above." -ForegroundColor Red
Write-Host ""
Write-Host "Troubleshooting:" -ForegroundColor Yellow
Write-Host "1. Make sure PHP 8.1+ is installed and in PATH" -ForegroundColor White
Write-Host "2. Make sure Composer is installed and in PATH" -ForegroundColor White
Write-Host "3. Check your internet connection" -ForegroundColor White
Write-Host "4. Try running: composer clear-cache" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
exit 1

success:
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Installation completed successfully!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if composer.lock was created
if (Test-Path "composer.lock") {
    Write-Host "✓ composer.lock file created successfully" -ForegroundColor Green
    Write-Host "✓ Dependencies are now locked to specific versions" -ForegroundColor Green
} else {
    Write-Host "⚠ WARNING: composer.lock file not found" -ForegroundColor Yellow
    Write-Host "This may cause version inconsistencies" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Run: php artisan key:generate" -ForegroundColor White
Write-Host "2. Configure your .env file" -ForegroundColor White
Write-Host "3. Import database schema" -ForegroundColor White
Write-Host "4. Commit composer.lock to version control" -ForegroundColor White
Write-Host "5. Run: php artisan serve" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"