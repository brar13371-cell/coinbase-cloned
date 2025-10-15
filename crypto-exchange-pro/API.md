# CryptoExchange Pro - API Documentation

## Base URL

```
http://localhost/crypto-exchange-pro/public/api
```

## Authentication

Most endpoints require authentication. Include the Bearer token in the Authorization header:

```
Authorization: Bearer your-token-here
```

## Response Format

All API responses follow this format:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

Error responses:

```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error information"
}
```

## Authentication Endpoints

### Register User

**POST** `/auth/register`

Register a new user account.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+1234567890",
  "date_of_birth": "1990-01-01",
  "country": "US",
  "terms_accepted": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful. Please check your email for verification.",
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "status": "pending",
      "kyc_status": "none"
    }
  }
}
```

### Login User

**POST** `/auth/login`

Authenticate user and get access token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "status": "active",
      "kyc_status": "approved",
      "two_factor_enabled": false
    },
    "token": "1|abc123...",
    "requires_2fa": false
  }
}
```

### Get Current User

**GET** `/auth/me`

Get current authenticated user information.

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "phone": "+1234567890",
      "status": "active",
      "kyc_status": "approved",
      "two_factor_enabled": true,
      "last_login_at": "2024-01-01T12:00:00Z",
      "created_at": "2024-01-01T00:00:00Z"
    },
    "profile": {
      "first_name": "John",
      "last_name": "Doe",
      "country": "US",
      "date_of_birth": "1990-01-01"
    }
  }
}
```

### Logout

**POST** `/auth/logout`

Logout current user and invalidate token.

**Response:**
```json
{
  "success": true,
  "message": "Logout successful."
}
```

## Wallet Endpoints

### Get User Wallets

**GET** `/wallets`

Get all wallets for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "wallets": [
      {
        "id": 1,
        "currency": "BTC",
        "balance_available": 1.5,
        "balance_reserved": 0.0,
        "balance_total": 1.5,
        "wallet_type": "spot",
        "formatted_balance": "1.50000000",
        "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
        "network": "mainnet"
      }
    ]
  }
}
```

### Get Specific Wallet

**GET** `/wallets/{id}`

Get details of a specific wallet.

**Response:**
```json
{
  "success": true,
  "data": {
    "wallet": {
      "id": 1,
      "currency": "BTC",
      "balance_available": 1.5,
      "balance_reserved": 0.0,
      "balance_total": 1.5,
      "wallet_type": "spot",
      "formatted_balance": "1.50000000",
      "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
      "network": "mainnet",
      "addresses": [
        {
          "id": 1,
          "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
          "network": "mainnet",
          "tag": null,
          "is_active": true
        }
      ]
    }
  }
}
```

### Generate Deposit Address

**POST** `/wallets/{id}/deposit-address`

Generate a new deposit address for a wallet.

**Request Body:**
```json
{
  "network": "mainnet"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Deposit address generated successfully.",
  "data": {
    "address": {
      "id": 1,
      "address": "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
      "network": "mainnet",
      "tag": null,
      "currency": "BTC"
    }
  }
}
```

### Create Withdrawal

**POST** `/wallets/withdraw`

Create a withdrawal transaction.

**Request Body:**
```json
{
  "currency": "BTC",
  "amount": 0.1,
  "fee": 0.001,
  "to_address": "1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2",
  "memo": "Withdrawal to external wallet"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal transaction created successfully.",
  "data": {
    "transaction": {
      "id": 1,
      "type": "withdrawal",
      "status": "pending",
      "amount": 0.1,
      "fee": 0.001,
      "total_amount": 0.101,
      "currency": "BTC",
      "to_address": "1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2",
      "created_at": "2024-01-01T12:00:00Z"
    }
  }
}
```

### Get Wallet Transactions

**GET** `/wallets/{id}/transactions`

Get transaction history for a specific wallet.

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page (default: 20)
- `type` (optional): Filter by transaction type
- `status` (optional): Filter by transaction status

**Response:**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 1,
        "type": "deposit",
        "status": "completed",
        "amount": 1.0,
        "fee": 0.0,
        "total_amount": 1.0,
        "currency": "BTC",
        "txid": "abc123...",
        "created_at": "2024-01-01T12:00:00Z",
        "processed_at": "2024-01-01T12:05:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 100
    }
  }
}
```

## Trading Endpoints

### Get Trading Pairs

**GET** `/trading/pairs`

Get all available trading pairs.

**Response:**
```json
{
  "success": true,
  "data": {
    "pairs": [
      {
        "id": 1,
        "symbol": "BTCUSD",
        "base_currency": "BTC",
        "quote_currency": "USD",
        "price": 50000.0,
        "change_24h": 2.5,
        "volume_24h": 1000000.0,
        "high_24h": 51000.0,
        "low_24h": 49000.0,
        "min_trade_amount": 0.0001,
        "max_trade_amount": 1000.0,
        "tick_size": 0.01,
        "step_size": 0.0001,
        "maker_fee": 0.001,
        "taker_fee": 0.001
      }
    ]
  }
}
```

### Get Market Data

**GET** `/trading/pairs/{id}/data`

Get market data for a specific trading pair.

**Response:**
```json
{
  "success": true,
  "data": {
    "pair": {
      "id": 1,
      "symbol": "BTCUSD",
      "base_currency": "BTC",
      "quote_currency": "USD"
    },
    "market_data": {
      "price": 50000.0,
      "change_24h": 2.5,
      "volume_24h": 1000000.0,
      "high_24h": 51000.0,
      "low_24h": 49000.0,
      "timestamp": "2024-01-01T12:00:00Z"
    }
  }
}
```

### Get Order Book

**GET** `/trading/pairs/{id}/orderbook`

Get order book for a specific trading pair.

**Query Parameters:**
- `limit` (optional): Number of orders to return (default: 50)

**Response:**
```json
{
  "success": true,
  "data": {
    "pair": {
      "id": 1,
      "symbol": "BTCUSD",
      "base_currency": "BTC",
      "quote_currency": "USD"
    },
    "order_book": {
      "bids": [
        {
          "price": 49950.0,
          "amount": 0.5,
          "total": 24975.0
        }
      ],
      "asks": [
        {
          "price": 50050.0,
          "amount": 0.3,
          "total": 15015.0
        }
      ]
    }
  }
}
```

### Place Order

**POST** `/trading/orders`

Place a new trading order.

**Request Body:**
```json
{
  "pair_id": 1,
  "side": "buy",
  "type": "limit",
  "amount": 0.1,
  "price": 49000.0,
  "time_in_force": "GTC"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order placed successfully.",
  "data": {
    "order": {
      "id": 1,
      "pair_id": 1,
      "side": "buy",
      "type": "limit",
      "price": 49000.0,
      "amount": 0.1,
      "filled": 0.0,
      "remaining": 0.1,
      "status": "open",
      "created_at": "2024-01-01T12:00:00Z"
    }
  }
}
```

### Get User Orders

**GET** `/trading/orders`

Get orders for the authenticated user.

**Query Parameters:**
- `pair_id` (optional): Filter by trading pair
- `status` (optional): Filter by order status
- `side` (optional): Filter by order side
- `type` (optional): Filter by order type
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page

**Response:**
```json
{
  "success": true,
  "data": {
    "orders": [
      {
        "id": 1,
        "pair_id": 1,
        "side": "buy",
        "type": "limit",
        "price": 49000.0,
        "amount": 0.1,
        "filled": 0.05,
        "remaining": 0.05,
        "status": "partially_filled",
        "created_at": "2024-01-01T12:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 20,
      "total": 50
    }
  }
}
```

### Cancel Order

**DELETE** `/trading/orders/{id}`

Cancel a specific order.

**Response:**
```json
{
  "success": true,
  "message": "Order cancelled successfully."
}
```

## KYC Endpoints

### Get KYC Status

**GET** `/kyc/status`

Get KYC status for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "kyc_status": "approved",
    "kyc_request": {
      "id": 1,
      "status": "approved",
      "document_type": "passport",
      "submitted_at": "2024-01-01T12:00:00Z",
      "reviewed_at": "2024-01-01T14:00:00Z",
      "review_notes": "Approved after verification"
    }
  }
}
```

### Submit KYC

**POST** `/kyc/submit`

Submit KYC documents for verification.

**Request Body:**
```json
{
  "document_type": "passport",
  "document_front": "path/to/front/document.jpg",
  "document_back": "path/to/back/document.jpg",
  "selfie": "path/to/selfie.jpg"
}
```

**Response:**
```json
{
  "success": true,
  "message": "KYC request submitted successfully.",
  "data": {
    "kyc_request": {
      "id": 1,
      "status": "pending",
      "document_type": "passport",
      "submitted_at": "2024-01-01T12:00:00Z"
    }
  }
}
```

## Notification Endpoints

### Get Notifications

**GET** `/notifications`

Get notifications for the authenticated user.

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page
- `type` (optional): Filter by notification type

**Response:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": 1,
        "type": "trade",
        "title": "Order Filled",
        "message": "Your buy order for 0.1 BTC has been filled at $50,000",
        "is_read": false,
        "created_at": "2024-01-01T12:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 20,
      "total": 30
    }
  }
}
```

### Mark Notification as Read

**POST** `/notifications/{id}/read`

Mark a specific notification as read.

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read."
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

## Rate Limiting

API requests are rate limited to 60 requests per minute per user. Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## WebSocket Support

Real-time updates are available via WebSocket connection:

```
ws://localhost/crypto-exchange-pro/public/ws
```

**Message Types:**
- `price_update` - Price changes
- `trade_update` - New trades
- `order_update` - Order status changes
- `balance_update` - Wallet balance changes

## SDKs and Libraries

Official SDKs are available for:
- JavaScript/TypeScript
- Python
- PHP
- Java

## Support

For API support:
- Check the logs for detailed error messages
- Review the API documentation
- Contact the development team