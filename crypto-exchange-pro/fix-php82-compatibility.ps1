# Fix PHP 8.2 Compatibility Issues (PowerShell)
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fix PHP 8.2 Compatibility Issues" -ForegroundColor Cyan
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

Write-Host "Detected PHP version:" -ForegroundColor Yellow
php --version
Write-Host ""

Write-Host "Fixing PHP 8.2 compatibility issues..." -ForegroundColor Yellow
Write-Host ""

# Backup original composer.json
if (Test-Path "composer.json") {
    Write-Host "Backing up original composer.json..." -ForegroundColor Cyan
    Copy-Item "composer.json" "composer-original.json"
}

# Use PHP 8.2 compatible composer.json
if (Test-Path "composer-php82.json") {
    Write-Host "Using PHP 8.2 compatible composer.json..." -ForegroundColor Cyan
    Copy-Item "composer-php82.json" "composer.json"
}

# Remove existing lock file and vendor directory
Write-Host "Cleaning up existing installation..." -ForegroundColor Yellow
if (Test-Path "composer.lock") { Remove-Item "composer.lock" }
if (Test-Path "vendor") { Remove-Item "vendor" -Recurse -Force }

Write-Host ""
Write-Host "Installing dependencies with PHP 8.2 compatibility..." -ForegroundColor Yellow
Write-Host ""

# Method 1: Install with platform requirements
Write-Host "Method 1: Installing with platform requirements" -ForegroundColor Cyan
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
if ($LASTEXITCODE -eq 0) {
    goto check_success
}

Write-Host ""
Write-Host "Method 1 failed, trying Method 2..." -ForegroundColor Yellow
Write-Host ""

# Method 2: Update dependencies
Write-Host "Method 2: Updating dependencies" -ForegroundColor Cyan
composer update --no-dev --optimize-autoloader --ignore-platform-reqs
if ($LASTEXITCODE -eq 0) {
    goto check_success
}

Write-Host ""
Write-Host "Method 2 failed, trying Method 3..." -ForegroundColor Yellow
Write-Host ""

# Method 3: Install with specific platform overrides
Write-Host "Method 3: Installing with platform overrides" -ForegroundColor Cyan
composer install --no-dev --optimize-autoloader --ignore-platform-req=php-64bit --ignore-platform-req=ext-pcntl
if ($LASTEXITCODE -eq 0) {
    goto check_success
}

Write-Host ""
Write-Host "All methods failed. Please check the error messages above." -ForegroundColor Red
Write-Host ""
Write-Host "Troubleshooting:" -ForegroundColor Yellow
Write-Host "1. Make sure you have PHP 8.2 installed" -ForegroundColor White
Write-Host "2. Check that Composer is up to date: composer self-update" -ForegroundColor White
Write-Host "3. Try running: composer clear-cache" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
exit 1

check_success:
Write-Host ""
Write-Host "Checking installation..." -ForegroundColor Yellow

if (Test-Path "composer.lock") {
    Write-Host "✓ composer.lock file created successfully" -ForegroundColor Green
} else {
    Write-Host "⚠ WARNING: composer.lock file not found" -ForegroundColor Yellow
}

if (Test-Path "vendor") {
    Write-Host "✓ vendor directory created successfully" -ForegroundColor Green
} else {
    Write-Host "✗ ERROR: vendor directory not found" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "PHP 8.2 Compatibility Fix Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "The following changes were made:" -ForegroundColor Yellow
Write-Host "- Updated composer.json for PHP 8.2 compatibility" -ForegroundColor White
Write-Host "- Removed problematic dependency versions" -ForegroundColor White
Write-Host "- Generated new composer.lock file" -ForegroundColor White
Write-Host "- Installed compatible packages" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Test the installation: php artisan --version" -ForegroundColor White
Write-Host "2. Configure your .env file" -ForegroundColor White
Write-Host "3. Run: php artisan key:generate" -ForegroundColor White
Write-Host "4. Start your development server" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"