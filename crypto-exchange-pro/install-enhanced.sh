#!/bin/bash

# Enhanced CryptoExchange Pro Installation Script
# This script installs the complete enterprise-grade cryptocurrency exchange platform
# with all advanced features: futures, margin, staking, DeFi, NFTs, and admin god powers

set -e

echo "🚀 Installing Enhanced CryptoExchange Pro..."
echo "=============================================="
echo "This will install a complete cryptocurrency exchange platform"
echo "with 200+ API endpoints and enterprise-grade features."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
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

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_header() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_feature() {
    echo -e "${PURPLE}[FEATURE]${NC} $1"
}

# Check if running on Linux
if [[ "$OSTYPE" != "linux-gnu"* ]]; then
    print_error "This script is designed for Linux systems. Please run on a Linux environment."
    exit 1
fi

print_header "Checking system requirements..."

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

print_success "XAMPP components found ✓"

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
if (( $(echo "$PHP_VERSION < 8.1" | bc -l) )); then
    print_error "PHP 8.1+ is required. Current version: $PHP_VERSION"
    exit 1
fi

print_success "PHP version $PHP_VERSION ✓"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    print_warning "Composer is not installed. Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    print_success "Composer installed ✓"
else
    print_success "Composer found ✓"
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

NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 18 ]; then
    print_error "Node.js 18+ is required. Current version: $(node -v)"
    exit 1
fi

print_success "Node.js version $(node -v) ✓"

# Check if Redis is available (optional)
if command -v redis-server &> /dev/null; then
    print_success "Redis found ✓"
    REDIS_AVAILABLE=true
else
    print_warning "Redis not found. Will use file-based cache."
    REDIS_AVAILABLE=false
fi

print_header "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

print_header "Installing Node.js dependencies..."
npm install

print_header "Setting up environment configuration..."

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    print_status "Creating .env file..."
    cp .env.example .env
    
    # Generate application key
    php artisan key:generate --no-interaction
    
    print_warning "Please update the .env file with your database credentials and API keys."
else
    print_success ".env file already exists ✓"
fi

print_header "Setting up database..."

# Check if database exists
DB_NAME="crypto_exchange_pro"
DB_USER="root"
DB_PASS=""

# Create database if it doesn't exist
mysql -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" 2>/dev/null || {
    print_warning "Could not create database automatically. Please create the database manually:"
    print_warning "Database name: $DB_NAME"
    print_warning "Then run: mysql -u $DB_USER -p < database/enhanced_schema.sql"
}

# Import enhanced database schema
if [ -f "database/enhanced_schema.sql" ]; then
    print_status "Importing enhanced database schema..."
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < database/enhanced_schema.sql
    print_success "Enhanced database schema imported ✓"
else
    print_warning "Enhanced database schema file not found. Please import it manually."
fi

print_header "Setting up storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p public/storage
mkdir -p storage/app/kyc
mkdir -p storage/app/nft
mkdir -p storage/app/defi
mkdir -p storage/app/staking
mkdir -p storage/app/lending
mkdir -p storage/app/futures
mkdir -p storage/app/margin
mkdir -p storage/app/analytics
mkdir -p storage/app/reports
mkdir -p storage/app/backups

# Set permissions
print_status "Setting up permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create symbolic link for storage
print_status "Creating storage symbolic link..."
php artisan storage:link

print_header "Building frontend assets..."
npm run build

print_header "Setting up Apache virtual host..."
APACHE_CONF="/etc/apache2/sites-available/crypto-exchange-pro-enhanced.conf"
DOCUMENT_ROOT=$(pwd)/public

if [ ! -f "$APACHE_CONF" ]; then
    sudo tee "$APACHE_CONF" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName crypto-exchange-pro-enhanced.local
    DocumentRoot $DOCUMENT_ROOT
    
    <Directory $DOCUMENT_ROOT>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Enable mod_rewrite
    RewriteEngine On
    
    # Handle API routes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/(.*)$ index.php [QSA,L]
    
    # Handle admin routes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^admin/(.*)$ index.php [QSA,L]
    
    # Handle god routes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^god/(.*)$ index.php [QSA,L]
    
    ErrorLog \${APACHE_LOG_DIR}/crypto-exchange-pro-enhanced_error.log
    CustomLog \${APACHE_LOG_DIR}/crypto-exchange-pro-enhanced_access.log combined
</VirtualHost>
EOF

    sudo a2ensite crypto-exchange-pro-enhanced.conf
    sudo systemctl reload apache2
    
    print_success "Apache virtual host created ✓"
    print_warning "Add '127.0.0.1 crypto-exchange-pro-enhanced.local' to your /etc/hosts file"
else
    print_success "Apache virtual host already exists ✓"
fi

print_header "Setting up system services..."

# Create systemd service for queue worker
print_status "Setting up queue worker service..."
SERVICE_FILE="/etc/systemd/system/crypto-exchange-pro-worker.service"

if [ ! -f "$SERVICE_FILE" ]; then
    sudo tee "$SERVICE_FILE" > /dev/null <<EOF
[Unit]
Description=CryptoExchange Pro Enhanced Queue Worker
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
    
    print_success "Queue worker service created and started ✓"
else
    print_success "Queue worker service already exists ✓"
fi

# Create systemd service for WebSocket server
print_status "Setting up WebSocket server service..."
WEBSOCKET_SERVICE_FILE="/etc/systemd/system/crypto-exchange-pro-websocket.service"

if [ ! -f "$WEBSOCKET_SERVICE_FILE" ]; then
    sudo tee "$WEBSOCKET_SERVICE_FILE" > /dev/null <<EOF
[Unit]
Description=CryptoExchange Pro Enhanced WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=$(pwd)
ExecStart=/usr/bin/php artisan websockets:serve --host=0.0.0.0 --port=6001
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    sudo systemctl daemon-reload
    sudo systemctl enable crypto-exchange-pro-websocket
    sudo systemctl start crypto-exchange-pro-websocket
    
    print_success "WebSocket server service created and started ✓"
else
    print_success "WebSocket server service already exists ✓"
fi

# Create systemd service for price updater
print_status "Setting up price updater service..."
PRICE_SERVICE_FILE="/etc/systemd/system/crypto-exchange-pro-price-updater.service"

if [ ! -f "$PRICE_SERVICE_FILE" ]; then
    sudo tee "$PRICE_SERVICE_FILE" > /dev/null <<EOF
[Unit]
Description=CryptoExchange Pro Enhanced Price Updater
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=$(pwd)
ExecStart=/usr/bin/php artisan price:update
Restart=always
RestartSec=30

[Install]
WantedBy=multi-user.target
EOF

    sudo systemctl daemon-reload
    sudo systemctl enable crypto-exchange-pro-price-updater
    sudo systemctl start crypto-exchange-pro-price-updater
    
    print_success "Price updater service created and started ✓"
else
    print_success "Price updater service already exists ✓"
fi

# Create systemd service for order matcher
print_status "Setting up order matcher service..."
ORDER_SERVICE_FILE="/etc/systemd/system/crypto-exchange-pro-order-matcher.service"

if [ ! -f "$ORDER_SERVICE_FILE" ]; then
    sudo tee "$ORDER_SERVICE_FILE" > /dev/null <<EOF
[Unit]
Description=CryptoExchange Pro Enhanced Order Matcher
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=$(pwd)
ExecStart=/usr/bin/php artisan order:match
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    sudo systemctl daemon-reload
    sudo systemctl enable crypto-exchange-pro-order-matcher
    sudo systemctl start crypto-exchange-pro-order-matcher
    
    print_success "Order matcher service created and started ✓"
else
    print_success "Order matcher service already exists ✓"
fi

print_header "Setting up cron jobs..."

# Create cron job for maintenance tasks
print_status "Setting up maintenance cron jobs..."
CRON_FILE="/etc/cron.d/crypto-exchange-pro-enhanced"

if [ ! -f "$CRON_FILE" ]; then
    sudo tee "$CRON_FILE" > /dev/null <<EOF
# CryptoExchange Pro Enhanced Maintenance Tasks
# Run every minute
* * * * * www-data cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1

# Run every 5 minutes
*/5 * * * * www-data cd $(pwd) && php artisan price:update >> /dev/null 2>&1

# Run every hour
0 * * * * www-data cd $(pwd) && php artisan maintenance:cleanup >> /dev/null 2>&1

# Run every day at 2 AM
0 2 * * * www-data cd $(pwd) && php artisan maintenance:backup >> /dev/null 2>&1

# Run every week on Sunday at 3 AM
0 3 * * 0 www-data cd $(pwd) && php artisan maintenance:optimize >> /dev/null 2>&1
EOF

    print_success "Cron jobs created ✓"
else
    print_success "Cron jobs already exist ✓"
fi

print_header "Finalizing setup..."

# Run initial database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Seed initial data
print_status "Seeding initial data..."
php artisan db:seed --force

# Clear caches
print_status "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
print_status "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "Installation completed successfully! 🎉"
echo ""
echo "=============================================="
echo "🚀 Enhanced CryptoExchange Pro is ready!"
echo "=============================================="
echo ""
echo "Next steps:"
echo "1. Update your .env file with the correct database credentials and API keys"
echo "2. Add '127.0.0.1 crypto-exchange-pro-enhanced.local' to your /etc/hosts file"
echo "3. Access the application at: http://crypto-exchange-pro-enhanced.local"
echo "4. Or access directly at: http://localhost/$(basename $(pwd))/public"
echo ""
echo "Admin Access:"
echo "Frontend: http://crypto-exchange-pro-enhanced.local/admin"
echo "God Dashboard: http://crypto-exchange-pro-enhanced.local/god"
echo "API: http://crypto-exchange-pro-enhanced.local/api"
echo ""
echo "Default admin credentials:"
echo "Email: admin@cryptoexchange.com"
echo "Password: admin123"
echo ""
echo "Features Available:"
print_feature "✅ Complete Admin God Powers"
print_feature "✅ Service Provider Management"
print_feature "✅ Blockchain Network Management"
print_feature "✅ Cryptocurrency Management"
print_feature "✅ Trading Pair Management"
print_feature "✅ Futures Trading"
print_feature "✅ Margin Trading"
print_feature "✅ Staking Pools"
print_feature "✅ Lending Pools"
print_feature "✅ DeFi Integration"
print_feature "✅ NFT Marketplace"
print_feature "✅ Real-time WebSocket"
print_feature "✅ Advanced Security"
print_feature "✅ Comprehensive Monitoring"
print_feature "✅ Automated Backups"
print_feature "✅ Price Updates"
print_feature "✅ Order Matching"
print_feature "✅ Multi-blockchain Support"
print_feature "✅ Advanced Analytics"
print_feature "✅ Complete API (200+ endpoints)"
echo ""
echo "API Endpoints Available:"
echo "- Authentication: /api/auth/*"
echo "- Market Data: /api/market/*"
echo "- Trading: /api/trading/*"
echo "- Futures: /api/futures/*"
echo "- Margin: /api/margin/*"
echo "- Staking: /api/staking/*"
echo "- Lending: /api/lending/*"
echo "- DeFi: /api/defi/*"
echo "- NFT: /api/nft/*"
echo "- Portfolio: /api/portfolio/*"
echo "- Analytics: /api/analytics/*"
echo "- Admin: /api/admin/*"
echo "- God: /api/god/*"
echo ""
echo "Scripts Available:"
echo "- ./backup.sh - Create system backup"
echo "- ./monitor.sh - Check system status"
echo ""
print_warning "Remember to change the default admin password after first login!"
print_warning "Configure your API keys in the .env file for full functionality!"
echo ""
echo "For support and documentation, visit: https://github.com/your-repo/crypto-exchange-pro"
echo ""

# Start the development server (optional)
read -p "Do you want to start the development server now? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Starting development server..."
    php artisan serve --host=0.0.0.0 --port=8000
fi