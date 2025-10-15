#!/bin/bash

# CryptoExchange Pro Setup Script
# This script sets up the complete crypto exchange application

set -e

echo "🚀 Setting up CryptoExchange Pro..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running on Linux
if [[ "$OSTYPE" != "linux-gnu"* ]]; then
    print_error "This script is designed for Linux systems. Please run on a Linux environment."
    exit 1
fi

# Check if XAMPP is installed
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install XAMPP first."
    exit 1
fi

if ! command -v mysql &> /dev/null; then
    print_error "MySQL is not installed. Please install XAMPP first."
    exit 1
fi

if ! command -v apache2 &> /dev/null && ! command -v httpd &> /dev/null; then
    print_error "Apache is not installed. Please install XAMPP first."
    exit 1
fi

print_status "XAMPP components found ✓"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    print_warning "Composer is not installed. Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    print_status "Composer installed ✓"
else
    print_status "Composer found ✓"
fi

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 18+ first."
    exit 1
fi

if ! command -v npm &> /dev/null; then
    print_error "npm is not installed. Please install Node.js 18+ first."
    exit 1
fi

print_status "Node.js and npm found ✓"

# Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm install

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    print_status "Creating .env file..."
    cp .env.example .env
    
    # Generate application key
    php artisan key:generate --no-interaction
    
    print_warning "Please update the .env file with your database credentials and API keys."
fi

# Set up database
print_status "Setting up database..."

# Check if database exists
DB_NAME="crypto_exchange"
DB_USER="root"
DB_PASS=""

# Create database if it doesn't exist
mysql -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" 2>/dev/null || {
    print_warning "Could not create database automatically. Please create the database manually:"
    print_warning "Database name: $DB_NAME"
    print_warning "Then run: mysql -u $DB_USER -p < database/schema.sql"
}

# Import database schema
if [ -f "database/schema.sql" ]; then
    print_status "Importing database schema..."
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql
    print_status "Database schema imported ✓"
else
    print_warning "Database schema file not found. Please import it manually."
fi

# Set up storage directories
print_status "Setting up storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p public/storage

# Set permissions
print_status "Setting up permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create symbolic link for storage
print_status "Creating storage symbolic link..."
php artisan storage:link

# Build frontend assets
print_status "Building frontend assets..."
npm run build

# Set up Apache virtual host (optional)
print_status "Setting up Apache virtual host..."
APACHE_CONF="/etc/apache2/sites-available/crypto-exchange-pro.conf"
DOCUMENT_ROOT=$(pwd)/public

if [ ! -f "$APACHE_CONF" ]; then
    sudo tee "$APACHE_CONF" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName crypto-exchange-pro.local
    DocumentRoot $DOCUMENT_ROOT
    
    <Directory $DOCUMENT_ROOT>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/crypto-exchange-pro_error.log
    CustomLog \${APACHE_LOG_DIR}/crypto-exchange-pro_access.log combined
</VirtualHost>
EOF

    sudo a2ensite crypto-exchange-pro.conf
    sudo systemctl reload apache2
    
    print_status "Apache virtual host created ✓"
    print_warning "Add '127.0.0.1 crypto-exchange-pro.local' to your /etc/hosts file"
else
    print_status "Apache virtual host already exists ✓"
fi

# Create systemd service for queue worker (optional)
print_status "Setting up queue worker service..."
SERVICE_FILE="/etc/systemd/system/crypto-exchange-pro-worker.service"

if [ ! -f "$SERVICE_FILE" ]; then
    sudo tee "$SERVICE_FILE" > /dev/null <<EOF
[Unit]
Description=CryptoExchange Pro Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=$(pwd)
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    sudo systemctl daemon-reload
    sudo systemctl enable crypto-exchange-pro-worker
    sudo systemctl start crypto-exchange-pro-worker
    
    print_status "Queue worker service created and started ✓"
else
    print_status "Queue worker service already exists ✓"
fi

# Final setup instructions
print_status "Setup completed successfully! 🎉"
echo ""
echo "Next steps:"
echo "1. Update your .env file with the correct database credentials and API keys"
echo "2. Add '127.0.0.1 crypto-exchange-pro.local' to your /etc/hosts file"
echo "3. Access the application at: http://crypto-exchange-pro.local"
echo "4. Or access directly at: http://localhost/$(basename $(pwd))/public"
echo ""
echo "Default admin credentials:"
echo "Email: admin@cryptoexchange.com"
echo "Password: admin123"
echo ""
echo "API Documentation: http://crypto-exchange-pro.local/api"
echo ""
print_warning "Remember to change the default admin password after first login!"

# Start the development server (optional)
read -p "Do you want to start the development server now? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Starting development server..."
    php artisan serve --host=0.0.0.0 --port=8000
fi