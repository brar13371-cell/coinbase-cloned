# CryptoExchange Pro - Windows Installation Guide

## 🚀 Quick Start (Recommended)

### Option 1: Automated Installation
1. **Run the installer** (as Administrator):
   ```cmd
   install-windows.bat
   ```
   OR
   ```powershell
   .\install-windows.ps1
   ```

### Option 2: Manual Installation
Follow the step-by-step guide below.

## 📋 Prerequisites

### Required Software
1. **XAMPP** (Apache + PHP 8.1+ + MySQL 8.0+)
   - Download: https://www.apachefriends.org/download.html
   - Install with Apache, MySQL, and PHP checked

2. **Node.js** 18+
   - Download: https://nodejs.org/
   - Choose LTS version
   - Check "Add to PATH" during installation

3. **Composer**
   - Download: https://getcomposer.org/download/
   - Download Composer-Setup.exe
   - Check "Add to PATH" during installation

4. **Git** (Optional but recommended)
   - Download: https://git-scm.com/download/win

## 🔧 Step-by-Step Installation

### Step 1: Download and Extract
1. Download the project files
2. Extract to `C:\xampp\htdocs\crypto-exchange-pro\`

### Step 2: Install Dependencies
Open Command Prompt as Administrator and navigate to the project directory:

```cmd
cd C:\xampp\htdocs\crypto-exchange-pro
```

#### Install PHP Dependencies
```cmd
composer install --no-dev --optimize-autoloader
```

#### Install Node.js Dependencies
```cmd
npm install
```

### Step 3: Configure Environment
```cmd
copy .env.example .env
php artisan key:generate
```

### Step 4: Set Up Database
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin: http://localhost/phpmyadmin
4. Create database: `crypto_exchange`
5. Import schema:
   ```cmd
   mysql -u root -p crypto_exchange < database/enhanced_schema.sql
   ```

### Step 5: Build Frontend
```cmd
npm run build
```

### Step 6: Set Up Storage Directories
```cmd
mkdir storage\app\public
mkdir storage\framework\cache
mkdir storage\framework\sessions
mkdir storage\framework\views
mkdir storage\logs
```

### Step 7: Start the Application
```cmd
php artisan serve
```

Visit: http://localhost:8000

## 🌐 Production Setup

### Apache Virtual Host Configuration
Create `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName crypto-exchange-pro.local
    DocumentRoot "C:/xampp/htdocs/crypto-exchange-pro/public"
    
    <Directory "C:/xampp/htdocs/crypto-exchange-pro/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "C:/xampp/logs/crypto-exchange-pro_error.log"
    CustomLog "C:/xampp/logs/crypto-exchange-pro_access.log" common
</VirtualHost>
```

### Windows Hosts File
Add to `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 crypto-exchange-pro.local
```

## 🔧 Configuration

### Environment Variables (.env)
```env
APP_NAME="CryptoExchange Pro"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://crypto-exchange-pro.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crypto_exchange
DB_USERNAME=root
DB_PASSWORD=

# Add your API keys
BINANCE_API_KEY=your_binance_key
BINANCE_SECRET_KEY=your_binance_secret
STRIPE_PUBLISHABLE_KEY=your_stripe_key
STRIPE_SECRET_KEY=your_stripe_secret
```

## 🚨 Troubleshooting

### Common Issues

#### 1. "composer not found" error
- Make sure Composer is installed and in PATH
- Restart Command Prompt after installation
- Try: `C:\ProgramData\ComposerSetup\bin\composer.bat install`

#### 2. "php not found" error
- Make sure PHP is installed and in PATH
- Add PHP to PATH: `C:\xampp\php`
- Restart Command Prompt

#### 3. "node not found" error
- Make sure Node.js is installed and in PATH
- Restart Command Prompt after installation

#### 4. Permission errors
- Run Command Prompt as Administrator
- Check file permissions on storage directories

#### 5. Database connection errors
- Make sure MySQL is running in XAMPP
- Check database credentials in .env
- Verify database exists

#### 6. "Class not found" errors
- Run: `composer dump-autoload`
- Clear cache: `php artisan cache:clear`

### Performance Optimization

#### Enable OPcache (PHP)
Add to `C:\xampp\php\php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

#### Enable Redis (Optional)
1. Download Redis for Windows
2. Update .env:
   ```env
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

## 📁 Project Structure
```
crypto-exchange-pro/
├── app/                    # Application code
├── bootstrap/              # Bootstrap files
├── config/                 # Configuration files
├── database/               # Database files
├── public/                 # Web root
├── resources/              # Views and assets
├── routes/                 # Route definitions
├── storage/                # Storage directory
├── vendor/                 # Composer dependencies
├── artisan                 # Laravel CLI
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
└── .env                    # Environment configuration
```

## 🔐 Security Considerations

1. **Change default passwords** immediately
2. **Use HTTPS** in production
3. **Configure firewall** rules
4. **Regular backups** of database
5. **Keep dependencies updated**
6. **Monitor logs** for suspicious activity

## 📞 Support

If you encounter issues:
1. Check the logs in `storage/logs/`
2. Verify all prerequisites are installed
3. Check file permissions
4. Ensure all services are running

## 🎉 Success!

Once installed, you can access:
- **Main Website**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin
- **API**: http://localhost:8000/api

**Default Admin Credentials:**
- Email: admin@cryptoexchange.com
- Password: admin123

**⚠️ Important**: Change the default admin password immediately!