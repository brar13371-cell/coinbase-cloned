# Generate Composer Lock File (PowerShell)
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Generate Composer Lock File" -ForegroundColor Cyan
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

Write-Host "Generating composer.lock file..." -ForegroundColor Yellow
Write-Host "This ensures consistent dependency versions across all environments." -ForegroundColor Cyan
Write-Host ""

# Method 1: Install with lock file generation
Write-Host "Method 1: Installing dependencies (generates lock file)" -ForegroundColor Cyan
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
if ($LASTEXITCODE -eq 0) {
    goto check_lock
}

Write-Host ""
Write-Host "Method 1 failed, trying Method 2..." -ForegroundColor Yellow
Write-Host ""

# Method 2: Update to generate lock file
Write-Host "Method 2: Updating dependencies (generates/updates lock file)" -ForegroundColor Cyan
composer update --no-dev --optimize-autoloader --ignore-platform-reqs
if ($LASTEXITCODE -eq 0) {
    goto check_lock
}

Write-Host ""
Write-Host "Method 2 failed, trying Method 3..." -ForegroundColor Yellow
Write-Host ""

# Method 3: Generate lock without installing
Write-Host "Method 3: Generating lock file without installing" -ForegroundColor Cyan
composer install --no-install --ignore-platform-reqs
if ($LASTEXITCODE -eq 0) {
    goto check_lock
}

Write-Host ""
Write-Host "All methods failed. Please check the error messages above." -ForegroundColor Red
Read-Host "Press Enter to exit"
exit 1

check_lock:
Write-Host ""
Write-Host "Checking if composer.lock was created..." -ForegroundColor Yellow

if (Test-Path "composer.lock") {
    Write-Host "✓ SUCCESS: composer.lock file created!" -ForegroundColor Green
    Write-Host ""
    Write-Host "File details:" -ForegroundColor Cyan
    Get-Item "composer.lock" | Format-Table Name, Length, LastWriteTime
    Write-Host ""
    Write-Host "The lock file contains exact versions of all dependencies." -ForegroundColor Green
    Write-Host "This ensures consistent installs across all environments." -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "1. Commit the composer.lock file to version control" -ForegroundColor White
    Write-Host "2. Share with your team" -ForegroundColor White
    Write-Host "3. Use 'composer install' for future installs" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host "✗ ERROR: composer.lock file was not created" -ForegroundColor Red
    Write-Host "Please check the error messages above and try again." -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Read-Host "Press Enter to exit"