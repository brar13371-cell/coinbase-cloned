#!/bin/bash

echo "========================================"
echo "CryptoExchange Pro - Installation Verify"
echo "========================================"
echo ""

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ ERROR: Not in project root directory"
    echo "Please run this script from the crypto-exchange-pro directory"
    exit 1
fi

echo "✅ In correct project directory"
echo ""

# Check essential files
echo "Checking essential files..."

files=(
    "artisan"
    "bootstrap/app.php"
    "public/index.php"
    "public/.htaccess"
    "config/app.php"
    "config/database.php"
    "app/Providers/AppServiceProvider.php"
    "routes/web.php"
    "resources/views/welcome.blade.php"
    "install-windows.bat"
    "install-windows.ps1"
    "WINDOWS_INSTALLATION.md"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file - MISSING"
    fi
done

echo ""

# Check directories
echo "Checking directories..."

directories=(
    "storage"
    "storage/app"
    "storage/framework"
    "storage/framework/cache"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/logs"
    "resources"
    "resources/views"
    "config"
    "database"
    "database/migrations"
    "database/seeders"
    "database/factories"
)

for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        echo "✅ $dir/"
    else
        echo "❌ $dir/ - MISSING"
    fi
done

echo ""

# Check composer.json
echo "Checking composer.json..."
if grep -q "post-install-cmd" composer.json; then
    echo "✅ composer.json has Windows compatibility fixes"
else
    echo "❌ composer.json missing Windows fixes"
fi

echo ""

# Check if .env.example exists
if [ -f ".env.example" ]; then
    echo "✅ .env.example exists"
else
    echo "❌ .env.example missing"
fi

echo ""

# Check package.json
if [ -f "package.json" ]; then
    echo "✅ package.json exists"
else
    echo "❌ package.json missing"
fi

echo ""
echo "========================================"
echo "Verification complete!"
echo "========================================"
echo ""
echo "If all items show ✅, the project is ready for commit."
echo "If any items show ❌, those files need to be created."
echo ""