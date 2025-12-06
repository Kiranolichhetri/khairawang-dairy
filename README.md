# 🥛 KHAIRAWANG DAIRY

A modern, production-ready e-commerce platform for premium dairy products with integrated eSewa payment gateway.

## 🚀 Tech Stack

- **Frontend**: Tailwind CSS 3.x, Alpine.js 3.x, Vanilla JavaScript ES Modules
- **Backend**: PHP 8.2+ with custom MVC Framework
- **Database**: MySQL 8+ / MongoDB 6.0+
- **Payment**: eSewa Integration (Nepal) with sandbox support

## 🎨 Design System

| Element | Value |
|---------|-------|
| Primary Color | Dark Brown `#201916` |
| Accent Color | Orange `#FD7C44` |
| Background | Cream `#F7EFDF` |
| Typography | Poppins, DM Sans |

## 📦 Features

### Customer Features
- ✅ Modern, responsive UI
- ✅ Product catalog with variants
- ✅ Shopping cart & checkout
- ✅ Multiple payment methods (eSewa, COD)
- ✅ Order tracking & management
- ✅ User authentication & profiles
- ✅ Wishlist management
- ✅ Product reviews & ratings
- ✅ Multi-language support (EN/NE)
- ✅ SEO optimized

### Payment Features
- ✅ eSewa payment gateway integration
- ✅ Sandbox testing support
- ✅ Payment verification with retry mechanism
- ✅ Transaction logging and audit trail
- ✅ Secure payment processing
- ✅ Cash on Delivery (COD) option
- ✅ Payment status tracking
- ✅ Failed payment recovery

### Admin Features
- ✅ Comprehensive admin dashboard
- ✅ Product & category management
- ✅ Order management & processing
- ✅ User management
- ✅ Sales reports & analytics
- ✅ Inventory tracking
- ✅ Newsletter campaigns
- ✅ Coupon management

## 🛠️ Installation

### Prerequisites
- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MongoDB 6.0+ (for cart/sessions)
- MySQL 8+ (alternative)

### Quick Start

```bash
# Clone repository
git clone https://github.com/Kiranolichhetri/khairawang-dairy.git
cd khairawang-dairy

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Build assets
npm run build

# Configure environment
cp .env.example .env
nano .env

# Set up database (if needed)
php artisan migrate --seed
```

### Environment Configuration

```bash
# Application
APP_NAME="KHAIRAWANG DAIRY"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
MONGO_URI=mongodb://localhost:27017/
MONGO_DATABASE=khairawang_dairy

# eSewa Payment (Sandbox)
ESEWA_MERCHANT_CODE=EPAYTEST
ESEWA_SECRET_KEY=8gBm/:&EnhH.1/q
PAYMENT_TEST_MODE=true
ESEWA_LOG_TRANSACTIONS=true
```

## 💳 Payment Integration

### eSewa Sandbox Testing

For testing the payment integration:

1. Set `PAYMENT_TEST_MODE=true` in `.env`
2. Use merchant code: `EPAYTEST`
3. Test credentials:
   - **eSewa ID**: 9800000000
   - **Password**: Any password
   - **MPIN**: 1234

### Production Setup

See detailed documentation:
- **eSewa Integration Guide**: `docs/ESEWA_INTEGRATION.md`
- **Payment API Documentation**: `docs/PAYMENT_API.md`
- **Deployment Guide**: `docs/DEPLOYMENT.md`

## 📖 Documentation

Comprehensive documentation available in the `docs/` directory:

- **ESEWA_INTEGRATION.md** - Complete eSewa payment gateway guide
- **PAYMENT_API.md** - Payment API endpoints and usage
- **DEPLOYMENT.md** - Production deployment instructions
- **TROUBLESHOOTING.md** - Common issues and solutions

## 🧪 Testing

### Run Tests
```bash
# PHP tests
composer test

# Lint PHP code
composer lint
```

### Manual Testing
1. Add products to cart
2. Proceed to checkout
3. Complete payment using test credentials
4. Verify order confirmation and status

## 🔒 Security

- ✅ HTTPS required for production
- ✅ CSRF protection enabled
- ✅ XSS prevention
- ✅ SQL injection protection
- ✅ Secure password hashing
- ✅ Payment data encryption
- ✅ Transaction logging for audit

## 📊 Monitoring

Transaction logs are stored in:
```
storage/logs/esewa-YYYY-MM-DD.log
storage/logs/app-YYYY-MM-DD.log
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📄 License

MIT License - See LICENSE file for details.

## 🆘 Support

### eSewa Support
- **Website**: https://esewa.com.np
- **Merchant Portal**: https://esewa.com.np/merchant
- **Email**: merchant@esewa.com.np
- **Phone**: +977-1-5970047

### Application Support
- **Email**: support@khairawangdairy.com
- **GitHub Issues**: [Report Issue](https://github.com/Kiranolichhetri/khairawang-dairy/issues)

---

Built with ❤️ for KHAIRAWANG DAIRY
