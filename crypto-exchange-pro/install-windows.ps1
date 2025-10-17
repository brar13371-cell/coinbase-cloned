# CryptoExchange Pro - Windows PowerShell Installer
# Run as Administrator if needed

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "CryptoExchange Pro - Windows Installer" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if PHP is installed
try {
    $phpVersion = php --version 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "PHP not found"
    }
    Write-Host "✓ PHP is installed" -ForegroundColor Green
} catch {
    Write-Host "✗ ERROR: PHP is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install PHP 8.1+ and add it to your PATH" -ForegroundColor Yellow
    Write-Host "Download from: https://windows.php.net/download/" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if Composer is installed
try {
    $composerVersion = composer --version 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Composer not found"
    }
    Write-Host "✓ Composer is installed" -ForegroundColor Green
} catch {
    Write-Host "✗ ERROR: Composer is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Composer and add it to your PATH" -ForegroundColor Yellow
    Write-Host "Download from: https://getcomposer.org/download/" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if Node.js is installed
try {
    $nodeVersion = node --version 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Node.js not found"
    }
    Write-Host "✓ Node.js is installed" -ForegroundColor Green
} catch {
    Write-Host "✗ ERROR: Node.js is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Node.js 18+ and add it to your PATH" -ForegroundColor Yellow
    Write-Host "Download from: https://nodejs.org/" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "All required tools are installed!" -ForegroundColor Green
Write-Host ""

# Install PHP dependencies
Write-Host "Installing PHP dependencies..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ ERROR: Failed to install PHP dependencies" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}
Write-Host "✓ PHP dependencies installed" -ForegroundColor Green

# Install Node.js dependencies
Write-Host "Installing Node.js dependencies..." -ForegroundColor Yellow
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ ERROR: Failed to install Node.js dependencies" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}
Write-Host "✓ Node.js dependencies installed" -ForegroundColor Green

# Create .env file if it doesn't exist
if (!(Test-Path ".env")) {
    Write-Host "Creating .env file..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    Write-Host "✓ .env file created" -ForegroundColor Green
}

# Generate application key
Write-Host "Generating application key..." -ForegroundColor Yellow
php artisan key:generate
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠ WARNING: Failed to generate application key" -ForegroundColor Yellow
    Write-Host "You may need to run: php artisan key:generate" -ForegroundColor Yellow
} else {
    Write-Host "✓ Application key generated" -ForegroundColor Green
}

# Create storage directories
Write-Host "Creating storage directories..." -ForegroundColor Yellow
$directories = @(
    "storage\app\public",
    "storage\framework\cache",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs"
)

foreach ($dir in $directories) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}
Write-Host "✓ Storage directories created" -ForegroundColor Green

# Build frontend assets
Write-Host "Building frontend assets..." -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠ WARNING: Failed to build frontend assets" -ForegroundColor Yellow
    Write-Host "You may need to run: npm run build" -ForegroundColor Yellow
} else {
    Write-Host "✓ Frontend assets built" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Installation completed successfully!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Configure your .env file with database credentials" -ForegroundColor White
Write-Host "2. Import the database schema: database\enhanced_schema.sql" -ForegroundColor White
Write-Host "3. Start XAMPP (Apache and MySQL)" -ForegroundColor White
Write-Host "4. Run: php artisan serve" -ForegroundColor White
Write-Host "5. Visit: http://localhost:8000" -ForegroundColor White
Write-Host ""
Write-Host "For production deployment:" -ForegroundColor Yellow
Write-Host "1. Set up a web server (Apache/Nginx)" -ForegroundColor White
Write-Host "2. Configure virtual host" -ForegroundColor White
Write-Host "3. Set APP_ENV=production in .env" -ForegroundColor White
Write-Host ""

Read-Host "Press Enter to exit"