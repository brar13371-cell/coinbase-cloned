# CryptoExchange Pro - Production-Grade Crypto Trading Platform

A comprehensive, production-ready cryptocurrency exchange platform that replicates Coinbase's user experience and functionality, built for XAMPP stack.

## Features

### Core Trading Features
- **User Authentication**: Email/password with 2FA, email verification
- **KYC/AML Integration**: Complete identity verification flow
- **Multi-Currency Wallets**: Support for major cryptocurrencies and fiat
- **Trading Engine**: Market/limit orders, order book, real-time matching
- **Portfolio Management**: Real-time P&L, transaction history
- **Send/Receive**: Crypto transfers with QR codes
- **Fiat On/Off-Ramp**: Card payments, bank transfers

### Admin Features
- **User Management**: KYC approval, account controls
- **Financial Dashboard**: Volume, fees, pending transactions
- **System Monitoring**: Provider status, audit logs
- **Configuration**: Trading pairs, fees, limits

### Third-Party Integrations
- **Market Data**: Real-time prices, order books, historical data
- **Liquidity Providers**: Multiple exchange integrations
- **Payment Processing**: Stripe, Adyen, bank transfers
- **KYC Services**: Jumio, Onfido integration
- **Notifications**: Email, SMS, push notifications

## Tech Stack

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: React 18 with TypeScript
- **Database**: MySQL 8.0
- **Cache**: Redis (optional, with file fallback)
- **WebSocket**: Laravel WebSockets (Pusher alternative)
- **Queue**: Laravel Queue with database driver

## Quick Start

### Prerequisites
- XAMPP with PHP 8.1+, MySQL 8.0, Apache
- Composer
- Node.js 18+
- Redis (optional)

### Installation

1. **Clone and setup**:
```bash
git clone <repository>
cd crypto-exchange-pro
composer install
npm install
```

2. **Database setup**:
```bash
# Import database
mysql -u root -p < database/schema.sql

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

3. **Configuration**:
```bash
cp .env.example .env
# Edit .env with your database and API keys
php artisan key:generate
```

4. **Build assets**:
```bash
npm run build
```

5. **Start services**:
```bash
# Start XAMPP services
# Start Laravel queue worker
php artisan queue:work

# Start WebSocket server (optional)
php artisan websockets:serve
```

6. **Access the application**:
- Frontend: http://localhost/crypto-exchange-pro/public
- Admin: http://localhost/crypto-exchange-pro/public/admin
- API: http://localhost/crypto-exchange-pro/public/api

## Configuration

### Environment Variables
Copy `.env.example` to `.env` and configure:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crypto_exchange
DB_USERNAME=root
DB_PASSWORD=

# Redis (optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Third-party APIs
BINANCE_API_KEY=your_binance_key
BINANCE_SECRET_KEY=your_binance_secret

STRIPE_PUBLISHABLE_KEY=your_stripe_key
STRIPE_SECRET_KEY=your_stripe_secret

JUMIO_API_TOKEN=your_jumio_token
JUMIO_API_SECRET=your_jumio_secret

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

## API Documentation

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/verify-email` - Email verification
- `POST /api/auth/2fa/enable` - Enable 2FA
- `POST /api/auth/2fa/verify` - Verify 2FA

### Trading
- `GET /api/market/tickers` - Get market data
- `GET /api/market/orderbook/{pair}` - Get order book
- `POST /api/trading/orders` - Place order
- `GET /api/trading/orders` - Get user orders
- `DELETE /api/trading/orders/{id}` - Cancel order

### Wallets
- `GET /api/wallets` - Get user wallets
- `POST /api/wallets/deposit` - Generate deposit address
- `POST /api/wallets/withdraw` - Create withdrawal
- `GET /api/wallets/transactions` - Get transaction history

## Security Features

- **Encryption**: All sensitive data encrypted at rest
- **2FA**: Time-based OTP authentication
- **Rate Limiting**: API rate limiting and DDoS protection
- **Audit Logging**: Complete audit trail for all actions
- **KYC/AML**: Full compliance workflow
- **Cold Storage**: Secure key management for crypto operations

## Monitoring

- **Health Checks**: System status endpoints
- **Error Tracking**: Sentry integration
- **Performance**: Database query optimization
- **Logs**: Centralized logging system

## License

This project is for educational and demonstration purposes. Ensure compliance with local regulations before production use.

## Support

For technical support and questions, please refer to the documentation or create an issue in the repository.