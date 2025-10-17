# Fix Laravel Directory Structure (PowerShell)
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fix Laravel Directory Structure" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Creating missing Laravel directories..." -ForegroundColor Yellow
Write-Host ""

# Create bootstrap/cache directory
if (-not (Test-Path "bootstrap\cache")) {
    Write-Host "Creating bootstrap\cache directory..." -ForegroundColor Cyan
    New-Item -ItemType Directory -Path "bootstrap\cache" -Force | Out-Null
    Write-Host "✓ Created bootstrap\cache" -ForegroundColor Green
} else {
    Write-Host "✓ bootstrap\cache directory already exists" -ForegroundColor Green
}

# Create storage directories
Write-Host "Creating storage directories..." -ForegroundColor Yellow

$directories = @(
    "storage\app",
    "storage\app\public",
    "storage\framework",
    "storage\framework\cache",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "✓ Created $dir" -ForegroundColor Green
    } else {
        Write-Host "✓ $dir already exists" -ForegroundColor Green
    }
}

# Create .gitkeep files
Write-Host ""
Write-Host "Creating .gitkeep files..." -ForegroundColor Yellow

$gitkeepFiles = @(
    "bootstrap\cache\.gitkeep",
    "storage\app\.gitkeep",
    "storage\app\public\.gitkeep",
    "storage\framework\cache\.gitkeep",
    "storage\framework\cache\data\.gitkeep",
    "storage\framework\sessions\.gitkeep",
    "storage\framework\views\.gitkeep",
    "storage\logs\.gitkeep"
)

foreach ($file in $gitkeepFiles) {
    if (-not (Test-Path $file)) {
        New-Item -ItemType File -Path $file -Force | Out-Null
        Write-Host "✓ Created $file" -ForegroundColor Green
    } else {
        Write-Host "✓ $file already exists" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Laravel Directory Structure Fixed!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Testing Laravel installation..." -ForegroundColor Yellow
try {
    $artisanVersion = php artisan --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✓ SUCCESS: Laravel is working properly!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Yellow
        Write-Host "1. Run: php artisan key:generate" -ForegroundColor White
        Write-Host "2. Configure your .env file" -ForegroundColor White
        Write-Host "3. Run: php artisan serve" -ForegroundColor White
        Write-Host ""
    } else {
        throw "Laravel test failed"
    }
} catch {
    Write-Host ""
    Write-Host "⚠ WARNING: Laravel test failed" -ForegroundColor Yellow
    Write-Host "Please check the error messages above" -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Read-Host "Press Enter to exit"