@echo off
echo ========================================
echo CryptoExchange Pro - Windows Installer
echo ========================================
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP 8.1+ and add it to your PATH
    echo Download from: https://windows.php.net/download/
    pause
    exit /b 1
)

REM Check if Composer is installed
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Composer is not installed or not in PATH
    echo Please install Composer and add it to your PATH
    echo Download from: https://getcomposer.org/download/
    pause
    exit /b 1
)

REM Check if Node.js is installed
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Node.js is not installed or not in PATH
    echo Please install Node.js 18+ and add it to your PATH
    echo Download from: https://nodejs.org/
    pause
    exit /b 1
)

echo All required tools are installed!
echo.

REM Install PHP dependencies
echo Installing PHP dependencies...
echo Using Windows-compatible composer configuration...
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-pcntl
if %errorlevel% neq 0 (
    echo Trying alternative installation method...
    composer install --no-dev --optimize-autoloader --ignore-platform-reqs
    if %errorlevel% neq 0 (
        echo ERROR: Failed to install PHP dependencies
        echo Please check your PHP installation and try again
        pause
        exit /b 1
    )
)

REM Create Laravel directories
echo Creating Laravel directory structure...
if not exist bootstrap\cache mkdir bootstrap\cache
if not exist storage\app mkdir storage\app
if not exist storage\app\public mkdir storage\app\public
if not exist storage\framework mkdir storage\framework
if not exist storage\framework\cache mkdir storage\framework\cache
if not exist storage\framework\cache\data mkdir storage\framework\cache\data
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\views mkdir storage\framework\views
if not exist storage\logs mkdir storage\logs

REM Create .gitkeep files
echo. > bootstrap\cache\.gitkeep
echo. > storage\app\.gitkeep
echo. > storage\app\public\.gitkeep
echo. > storage\framework\cache\.gitkeep
echo. > storage\framework\cache\data\.gitkeep
echo. > storage\framework\sessions\.gitkeep
echo. > storage\framework\views\.gitkeep
echo. > storage\logs\.gitkeep

REM Install Node.js dependencies
echo Installing Node.js dependencies...
npm install
if %errorlevel% neq 0 (
    echo ERROR: Failed to install Node.js dependencies
    pause
    exit /b 1
)

REM Create .env file if it doesn't exist
if not exist .env (
    echo Creating .env file...
    copy .env.example .env
)

REM Generate application key
echo Generating application key...
php artisan key:generate
if %errorlevel% neq 0 (
    echo WARNING: Failed to generate application key
    echo You may need to run: php artisan key:generate
)

REM Create storage directories
echo Creating storage directories...
if not exist storage\app\public mkdir storage\app\public
if not exist storage\framework\cache mkdir storage\framework\cache
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\views mkdir storage\framework\views
if not exist storage\logs mkdir storage\logs

REM Set permissions (Windows doesn't need chmod, but we'll create the directories)
echo Setting up file permissions...

REM Build frontend assets
echo Building frontend assets...
npm run build
if %errorlevel% neq 0 (
    echo WARNING: Failed to build frontend assets
    echo You may need to run: npm run build
)

echo.
echo ========================================
echo Installation completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Configure your .env file with database credentials
echo 2. Import the database schema: database\enhanced_schema.sql
echo 3. Commit composer.lock file to version control
echo 4. Start XAMPP (Apache and MySQL)
echo 5. Run: php artisan serve
echo 6. Visit: http://localhost:8000
echo.
echo For production deployment:
echo 1. Set up a web server (Apache/Nginx)
echo 2. Configure virtual host
echo 3. Set APP_ENV=production in .env
echo.
pause