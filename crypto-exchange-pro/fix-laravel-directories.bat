@echo off
echo ========================================
echo Fix Laravel Directory Structure
echo ========================================
echo.

echo Creating missing Laravel directories...
echo.

REM Create bootstrap/cache directory
if not exist bootstrap\cache (
    echo Creating bootstrap\cache directory...
    mkdir bootstrap\cache
) else (
    echo ✓ bootstrap\cache directory already exists
)

REM Create storage directories
echo Creating storage directories...

if not exist storage\app (
    mkdir storage\app
    echo ✓ Created storage\app
) else (
    echo ✓ storage\app already exists
)

if not exist storage\app\public (
    mkdir storage\app\public
    echo ✓ Created storage\app\public
) else (
    echo ✓ storage\app\public already exists
)

if not exist storage\framework (
    mkdir storage\framework
    echo ✓ Created storage\framework
) else (
    echo ✓ storage\framework already exists
)

if not exist storage\framework\cache (
    mkdir storage\framework\cache
    echo ✓ Created storage\framework\cache
) else (
    echo ✓ storage\framework\cache already exists
)

if not exist storage\framework\cache\data (
    mkdir storage\framework\cache\data
    echo ✓ Created storage\framework\cache\data
) else (
    echo ✓ storage\framework\cache\data already exists
)

if not exist storage\framework\sessions (
    mkdir storage\framework\sessions
    echo ✓ Created storage\framework\sessions
) else (
    echo ✓ storage\framework\sessions already exists
)

if not exist storage\framework\views (
    mkdir storage\framework\views
    echo ✓ Created storage\framework\views
) else (
    echo ✓ storage\framework\views already exists
)

if not exist storage\logs (
    mkdir storage\logs
    echo ✓ Created storage\logs
) else (
    echo ✓ storage\logs already exists
)

REM Create .gitkeep files
echo.
echo Creating .gitkeep files...

echo. > bootstrap\cache\.gitkeep
echo ✓ Created bootstrap\cache\.gitkeep

echo. > storage\app\.gitkeep
echo ✓ Created storage\app\.gitkeep

echo. > storage\app\public\.gitkeep
echo ✓ Created storage\app\public\.gitkeep

echo. > storage\framework\cache\.gitkeep
echo ✓ Created storage\framework\cache\.gitkeep

echo. > storage\framework\cache\data\.gitkeep
echo ✓ Created storage\framework\cache\data\.gitkeep

echo. > storage\framework\sessions\.gitkeep
echo ✓ Created storage\framework\sessions\.gitkeep

echo. > storage\framework\views\.gitkeep
echo ✓ Created storage\framework\views\.gitkeep

echo. > storage\logs\.gitkeep
echo ✓ Created storage\logs\.gitkeep

echo.
echo ========================================
echo Laravel Directory Structure Fixed!
echo ========================================
echo.

echo Testing Laravel installation...
php artisan --version
if %errorlevel% equ 0 (
    echo.
    echo ✓ SUCCESS: Laravel is working properly!
    echo.
    echo Next steps:
    echo 1. Run: php artisan key:generate
    echo 2. Configure your .env file
    echo 3. Run: php artisan serve
    echo.
) else (
    echo.
    echo ⚠ WARNING: Laravel test failed
    echo Please check the error messages above
    echo.
)

echo ========================================
pause