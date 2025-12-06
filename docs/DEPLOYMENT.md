# Deployment Guide - eSewa Payment Integration

## Overview

This guide covers the deployment process for the KHAIRAWANG DAIRY eCommerce platform with eSewa payment gateway integration.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Requirements](#server-requirements)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [eSewa Setup](#esewa-setup)
6. [Security Configuration](#security-configuration)
7. [Testing](#testing)
8. [Going Live](#going-live)
9. [Monitoring](#monitoring)
10. [Troubleshooting](#troubleshooting)

## Prerequisites

### Required Software

- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **MongoDB**: 6.0 or higher (for cart and session storage)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for production

### Required PHP Extensions

```bash
# Check installed extensions
php -m

# Required extensions:
- json
- mbstring
- pdo
- curl
- openssl
- mongodb (version 1.15.0+)
- xml
- zip
```

## Server Requirements

### Minimum Server Specifications

- **CPU**: 2 cores
- **RAM**: 4 GB
- **Storage**: 20 GB SSD
- **Bandwidth**: 100 Mbps

### Recommended Specifications

- **CPU**: 4 cores
- **RAM**: 8 GB
- **Storage**: 50 GB SSD
- **Bandwidth**: 1 Gbps

## Installation Steps

### 1. Clone Repository

```bash
cd /var/www/
git clone https://github.com/Kiranolichhetri/khairawang-dairy.git
cd khairawang-dairy
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

### 3. Set File Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/khairawang-dairy

# Set directory permissions
find /var/www/khairawang-dairy -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/khairawang-dairy -type f -exec chmod 644 {} \;

# Make storage writable
chmod -R 775 /var/www/khairawang-dairy/storage
chmod -R 775 /var/www/khairawang-dairy/public/uploads
```

### 4. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key (if using)
php artisan key:generate

# Edit environment file
nano .env
```

## Configuration

### Environment Variables

#### Application Settings

```bash
APP_NAME="KHAIRAWANG DAIRY"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://khairawangdairy.com
APP_KEY=your-32-character-secret-key-here
```

#### Database Configuration

```bash
# MongoDB Connection
MONGO_URI=mongodb+srv://username:password@cluster.mongodb.net/
MONGO_DATABASE=khairawang_dairy

# Alternative: Local MongoDB
MONGO_URI=mongodb://localhost:27017/
MONGO_DATABASE=khairawang_dairy
```

#### eSewa Configuration

```bash
# Production Settings
ESEWA_MERCHANT_CODE=your_merchant_code
ESEWA_SECRET_KEY=your_secret_key
ESEWA_SUCCESS_URL=/payment/esewa/success
ESEWA_FAILURE_URL=/payment/esewa/failure
ESEWA_LOG_TRANSACTIONS=true
PAYMENT_TEST_MODE=false
```

#### Email Configuration

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@khairawangdairy.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Session & Cache

```bash
SESSION_DRIVER=file
SESSION_SECURE=true
CACHE_DRIVER=file
```

### Web Server Configuration

#### Apache Configuration

Create `/etc/apache2/sites-available/khairawang-dairy.conf`:

```apache
<VirtualHost *:80>
    ServerName khairawangdairy.com
    ServerAlias www.khairawangdairy.com
    
    # Redirect to HTTPS
    Redirect permanent / https://khairawangdairy.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName khairawangdairy.com
    ServerAlias www.khairawangdairy.com
    
    DocumentRoot /var/www/khairawang-dairy/public
    
    <Directory /var/www/khairawang-dairy/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/khairawang-error.log
    CustomLog ${APACHE_LOG_DIR}/khairawang-access.log combined
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/khairawangdairy.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/khairawangdairy.com/privkey.pem
    
    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</VirtualHost>
```

Enable site and modules:

```bash
a2ensite khairawang-dairy
a2enmod rewrite ssl headers
systemctl restart apache2
```

#### Nginx Configuration

Create `/etc/nginx/sites-available/khairawang-dairy`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name khairawangdairy.com www.khairawangdairy.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name khairawangdairy.com www.khairawangdairy.com;
    
    root /var/www/khairawang-dairy/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/khairawangdairy.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/khairawangdairy.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # File upload limit
    client_max_body_size 20M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:

```bash
ln -s /etc/nginx/sites-available/khairawang-dairy /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

## eSewa Setup

### 1. Register with eSewa

1. Visit [eSewa Merchant Portal](https://esewa.com.np/merchant)
2. Complete registration form
3. Submit required documents:
   - Business registration certificate
   - PAN certificate
   - Bank account details
   - Identity documents

### 2. Obtain Credentials

After approval, eSewa will provide:
- Merchant Code (e.g., `EPAYTEST` for sandbox)
- Secret Key for signature generation
- API documentation

### 3. Configure Callback URLs

Register these URLs with eSewa:
- Success URL: `https://khairawangdairy.com/payment/esewa/success`
- Failure URL: `https://khairawangdairy.com/payment/esewa/failure`

### 4. Test Integration

```bash
# Enable test mode
PAYMENT_TEST_MODE=true
ESEWA_MERCHANT_CODE=EPAYTEST
ESEWA_SECRET_KEY=8gBm/:&EnhH.1/q

# Test with sandbox
# Use eSewa test credentials to complete payment
```

## Security Configuration

### 1. SSL/TLS Certificate

Install Let's Encrypt certificate:

```bash
# Install Certbot
apt-get update
apt-get install certbot python3-certbot-apache

# Obtain certificate
certbot --apache -d khairawangdairy.com -d www.khairawangdairy.com

# Auto-renewal
certbot renew --dry-run
```

### 2. Firewall Configuration

```bash
# UFW Firewall
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Apache Full'
ufw enable
```

### 3. Secure File Permissions

```bash
# Protect .env file
chmod 600 .env
chown www-data:www-data .env

# Protect configuration files
chmod 644 config/*.php
```

### 4. Security Headers

Verify security headers are set:

```bash
curl -I https://khairawangdairy.com

# Should include:
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
```

## Testing

### Pre-Production Checklist

- [ ] All environment variables configured correctly
- [ ] Database connection working
- [ ] eSewa credentials configured
- [ ] SSL certificate installed
- [ ] File permissions set correctly
- [ ] Storage directories writable
- [ ] Email sending working
- [ ] Frontend assets built and accessible

### Test Payment Flow

1. **Create test order**
   ```bash
   # Add items to cart
   # Proceed to checkout
   # Fill shipping information
   # Select eSewa payment
   ```

2. **Complete payment**
   - Use eSewa test credentials
   - Verify redirection to success page
   - Check order status updated

3. **Verify logs**
   ```bash
   tail -f storage/logs/esewa-$(date +%Y-%m-%d).log
   ```

### Load Testing

```bash
# Install Apache Bench
apt-get install apache2-utils

# Test checkout endpoint
ab -n 1000 -c 10 https://khairawangdairy.com/checkout
```

## Going Live

### Pre-Launch Checklist

- [ ] **Configuration**
  - [ ] Set `APP_ENV=production`
  - [ ] Set `APP_DEBUG=false`
  - [ ] Set `PAYMENT_TEST_MODE=false`
  - [ ] Production eSewa credentials configured

- [ ] **Security**
  - [ ] SSL certificate active
  - [ ] Security headers configured
  - [ ] Firewall rules applied
  - [ ] File permissions secured

- [ ] **Performance**
  - [ ] Frontend assets optimized
  - [ ] Caching enabled
  - [ ] Database indexed

- [ ] **Monitoring**
  - [ ] Error logging enabled
  - [ ] Payment logging enabled
  - [ ] Server monitoring setup

### Launch Steps

1. **Final Configuration**
   ```bash
   nano .env
   # Set production values
   ```

2. **Clear Caches**
   ```bash
   php artisan cache:clear
   php artisan config:cache
   ```

3. **Restart Services**
   ```bash
   systemctl restart apache2  # or nginx
   systemctl restart php8.2-fpm
   ```

4. **Monitor Logs**
   ```bash
   tail -f storage/logs/*.log
   tail -f /var/log/apache2/khairawang-error.log
   ```

## Monitoring

### Log Files

Monitor these log files regularly:

```bash
# Application logs
tail -f storage/logs/app-$(date +%Y-%m-d).log

# eSewa payment logs
tail -f storage/logs/esewa-$(date +%Y-%m-%d).log

# Web server logs
tail -f /var/log/apache2/khairawang-error.log
tail -f /var/log/apache2/khairawang-access.log
```

### Health Checks

Set up monitoring for:

1. **Application Health**
   ```bash
   curl https://khairawangdairy.com/health
   ```

2. **Database Connection**
3. **Payment Gateway Connectivity**
4. **Disk Space**
5. **Memory Usage**

### Automated Monitoring

Use monitoring tools:
- **Uptime Monitoring**: Pingdom, UptimeRobot
- **Error Tracking**: Sentry, Bugsnag
- **Performance**: New Relic, Datadog

## Troubleshooting

### Common Issues

#### 1. Payment Callback Not Working

**Symptoms**: Payment successful but order not updated

**Solutions**:
```bash
# Check callback URLs are accessible
curl https://khairawangdairy.com/payment/esewa/success

# Check eSewa logs
tail -f storage/logs/esewa-*.log

# Verify firewall allows incoming connections
ufw status
```

#### 2. SSL Certificate Issues

**Symptoms**: Browser shows security warning

**Solutions**:
```bash
# Renew certificate
certbot renew

# Check certificate status
certbot certificates

# Test SSL configuration
openssl s_client -connect khairawangdairy.com:443
```

#### 3. Permission Denied Errors

**Symptoms**: Cannot write to logs or uploads

**Solutions**:
```bash
# Fix permissions
chmod -R 775 storage
chmod -R 775 public/uploads
chown -R www-data:www-data storage
chown -R www-data:www-data public/uploads
```

### Emergency Rollback

If issues occur after deployment:

```bash
# Switch to maintenance mode
touch storage/maintenance.flag

# Rollback to previous version
git checkout previous-tag

# Restore database backup
mongorestore --uri="mongodb://..." backup/

# Clear caches
php artisan cache:clear

# Remove maintenance mode
rm storage/maintenance.flag
```

## Support

### Emergency Contacts

- **eSewa Support**: +977-1-5970047
- **Server Admin**: admin@khairawangdairy.com
- **Developer**: dev@khairawangdairy.com

### Documentation

- eSewa Integration: `docs/ESEWA_INTEGRATION.md`
- API Documentation: `docs/PAYMENT_API.md`
- Troubleshooting: `docs/TROUBLESHOOTING.md`

---

**Last Updated**: December 2024  
**Maintained By**: KHAIRAWANG DAIRY Development Team
