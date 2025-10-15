# CryptoExchange Pro - Installation Guide

## Prerequisites

Before installing CryptoExchange Pro, ensure you have the following installed:

- **XAMPP** (Apache + PHP 8.1+ + MySQL 8.0+)
- **Node.js** 18+ and npm
- **Composer** (PHP dependency manager)
- **Git** (for cloning the repository)

## Quick Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd crypto-exchange-pro
   ```

2. **Run the setup script:**
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

3. **Update configuration:**
   Edit the `.env` file with your database credentials and API keys.

4. **Access the application:**
   - Frontend: http://localhost/crypto-exchange-pro/public
   - Admin: http://localhost/crypto-exchange-pro/public/admin
   - API: http://localhost/crypto-exchange-pro/public/api

## Manual Installation

If you prefer to install manually, follow these steps:

### 1. Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 2. Install Node.js Dependencies

```bash
npm install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit the `.env` file with your configuration:

```env
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
# ... other API keys
```

### 4. Set Up Database

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE crypto_exchange;"

# Import schema
mysql -u root -p crypto_exchange < database/schema.sql
```

### 5. Set Up Storage

```bash
mkdir -p storage/app/public
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
chmod -R 775 storage
php artisan storage:link
```

### 6. Build Frontend

```bash
npm run build
```

### 7. Start Services

```bash
# Start Apache and MySQL (XAMPP)
# Start Laravel queue worker
php artisan queue:work

# Start WebSocket server (optional)
php artisan websockets:serve
```

## Configuration

### Database Configuration

Update your database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crypto_exchange
DB_USERNAME=root
DB_PASSWORD=your_password
```

### API Keys Configuration

Add your third-party API keys to `.env`:

```env
# Market Data
BINANCE_API_KEY=your_binance_key
BINANCE_SECRET_KEY=your_binance_secret
COINGECKO_API_KEY=your_coingecko_key

# Payment Processing
STRIPE_PUBLISHABLE_KEY=your_stripe_publishable_key
STRIPE_SECRET_KEY=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

# KYC/AML
JUMIO_API_TOKEN=your_jumio_token
JUMIO_API_SECRET=your_jumio_secret

# Notifications
SENDGRID_API_KEY=your_sendgrid_key
TWILIO_ACCOUNT_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_token
```

### Apache Configuration

Create a virtual host for better URL structure:

```apache
<VirtualHost *:80>
    ServerName crypto-exchange-pro.local
    DocumentRoot /path/to/crypto-exchange-pro/public
    
    <Directory /path/to/crypto-exchange-pro/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to `/etc/hosts`:
```
127.0.0.1 crypto-exchange-pro.local
```

## Default Credentials

After installation, you can log in with these default credentials:

**Admin Account:**
- Email: admin@cryptoexchange.com
- Password: admin123

**⚠️ Important:** Change the default admin password immediately after first login.

## Features

### User Features
- ✅ User registration and authentication
- ✅ Email verification
- ✅ Two-factor authentication (2FA)
- ✅ KYC/AML verification
- ✅ Multi-currency wallet management
- ✅ Trading interface (basic)
- ✅ Portfolio dashboard
- ✅ Transaction history
- ✅ Notifications

### Admin Features
- ✅ User management
- ✅ KYC approval workflow
- ✅ Transaction monitoring
- ✅ Trading pair management
- ✅ System configuration
- ✅ Audit logs

### Technical Features
- ✅ RESTful API
- ✅ Real-time WebSocket support
- ✅ Queue system for background jobs
- ✅ File upload handling
- ✅ Rate limiting
- ✅ Security middleware
- ✅ Comprehensive logging

## API Documentation

The API is available at `/api` endpoint. Key endpoints include:

- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/wallets` - Get user wallets
- `POST /api/trading/orders` - Place trading order
- `GET /api/market/pairs` - Get trading pairs
- `POST /api/kyc/submit` - Submit KYC documents

## Troubleshooting

### Common Issues

1. **Permission Errors:**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

2. **Database Connection Issues:**
   - Check MySQL is running
   - Verify database credentials in `.env`
   - Ensure database exists

3. **Composer Issues:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Node.js Build Issues:**
   ```bash
   npm install
   npm run build
   ```

5. **Apache Issues:**
   - Check Apache is running
   - Verify virtual host configuration
   - Check file permissions

### Logs

Check these log files for debugging:

- Laravel logs: `storage/logs/laravel.log`
- Apache logs: `/var/log/apache2/error.log`
- MySQL logs: `/var/log/mysql/error.log`

## Security Considerations

1. **Change default passwords** immediately
2. **Use HTTPS** in production
3. **Configure firewall** rules
4. **Regular backups** of database
5. **Keep dependencies updated**
6. **Monitor logs** for suspicious activity

## Production Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Use a production database
4. Configure SSL certificates
5. Set up monitoring and logging
6. Configure backup strategies
7. Use a CDN for static assets

## Support

For technical support:

1. Check the logs for error messages
2. Review this documentation
3. Check GitHub issues
4. Contact support team

## License

This project is for educational and demonstration purposes. Ensure compliance with local regulations before production use.