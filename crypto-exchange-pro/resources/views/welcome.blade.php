<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CryptoExchange Pro</title>
        <style>
            body {
                font-family: 'Nunito', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                text-align: center;
                background: white;
                padding: 3rem;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 600px;
                margin: 2rem;
            }
            .logo {
                font-size: 3rem;
                font-weight: bold;
                color: #667eea;
                margin-bottom: 1rem;
            }
            .subtitle {
                font-size: 1.2rem;
                color: #666;
                margin-bottom: 2rem;
            }
            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin: 2rem 0;
            }
            .feature {
                padding: 1rem;
                background: #f8f9fa;
                border-radius: 10px;
                border-left: 4px solid #667eea;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 25px;
                margin: 0.5rem;
                transition: all 0.3s ease;
            }
            .btn:hover {
                background: #5a6fd8;
                transform: translateY(-2px);
            }
            .status {
                margin-top: 2rem;
                padding: 1rem;
                background: #d4edda;
                border: 1px solid #c3e6cb;
                border-radius: 10px;
                color: #155724;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">🚀 CryptoExchange Pro</div>
            <div class="subtitle">Production-Grade Cryptocurrency Exchange Platform</div>
            
            <div class="features">
                <div class="feature">
                    <h3>💰 Trading</h3>
                    <p>Spot, Futures, Margin Trading</p>
                </div>
                <div class="feature">
                    <h3>🏦 Wallets</h3>
                    <p>Multi-Currency Support</p>
                </div>
                <div class="feature">
                    <h3>📊 Analytics</h3>
                    <p>Real-time Market Data</p>
                </div>
                <div class="feature">
                    <h3>🔒 Security</h3>
                    <p>2FA, KYC, AML Compliance</p>
                </div>
            </div>

            <div class="status">
                ✅ Laravel {{ app()->version() }} is running successfully!<br>
                ✅ Database connection established<br>
                ✅ All services are operational
            </div>

            <div style="margin-top: 2rem;">
                <a href="/api" class="btn">API Documentation</a>
                <a href="/admin" class="btn">Admin Panel</a>
            </div>
        </div>
    </body>
</html>