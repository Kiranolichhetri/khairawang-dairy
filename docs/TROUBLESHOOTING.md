# eCommerce Troubleshooting Guide

## Overview

This guide helps diagnose and resolve common issues with the KHAIRAWANG DAIRY eCommerce platform, covering cart functionality, checkout process, and payment integration.

## Table of Contents

1. [Cart Issues](#cart-issues)
2. [Checkout Issues](#checkout-issues)
3. [Payment Issues](#payment-issues)
4. [Database Issues](#database-issues)
5. [Frontend Issues](#frontend-issues)
6. [API Issues](#api-issues)
7. [Logging and Debugging](#logging-and-debugging)

## Cart Issues

### Issue: Add to Cart Button Not Working

**Symptoms**: Clicking "Add to Cart" does nothing, no feedback

**Diagnostic Steps**:
```bash
# 1. Check if Alpine.js is loaded
# Open browser console and type:
Alpine.store('cart')

# Expected: Object with items, count, etc.
# If undefined: Alpine.js not loaded or initialized

# 2. Check for JavaScript errors
# Open browser console (F12)
# Look for red error messages

# 3. Verify API endpoint is accessible
curl http://localhost:8000/api/v1/cart/items -X POST \
  -H "Content-Type: application/json" \
  -d '{"product_id": "TEST_ID", "quantity": 1}'

# Expected: JSON response with success/error
```

**Solutions**:

1. **Alpine.js not loaded**:
   - Verify `resources/js/app.js` imports cart module
   - Check browser console for module loading errors
   - Run `npm run build` to rebuild assets

2. **Toast store not initialized**:
   - Ensure toast module is imported before cart in `app.js`
   - Check `initToast()` is called before `initCartStore()`

3. **API endpoint not accessible**:
   - Verify routes are registered in `routes/api.php`
   - Check web server is running
   - Verify CORS settings if calling from different domain

### Issue: Cart Count Not Updating

**Symptoms**: Cart icon shows incorrect count or doesn't update

**Diagnostic Steps**:
```javascript
// Browser console
Alpine.store('cart').count
Alpine.store('cart').items.length

// Check localStorage
localStorage.getItem('khairawang_cart')

// Expected: JSON array of items
```

**Solutions**:

1. **Cart not refreshing after add**:
   - Ensure `refresh()` is called after successful add
   - Check for errors in console during refresh

2. **localStorage not persisting**:
   ```javascript
   // Test localStorage
   KhairawangDairy.storage.isAvailable()
   // Expected: true
   
   // Clear and retry
   localStorage.clear()
   // Add item again
   ```

3. **Session/cookie issues**:
   - Check cookies in browser dev tools
   - Verify session is persisting
   - Check `cart_session_id` in session storage

### Issue: Cart Empty After Page Refresh

**Symptoms**: Items disappear when refreshing page

**Diagnostic Steps**:
```bash
# 1. Check localStorage
# Browser console:
localStorage.getItem('khairawang_cart')

# 2. Check API returns cart
curl http://localhost:8000/api/v1/cart

# 3. Check if user is logged in vs guest
# Different cart handling for each
```

**Solutions**:

1. **localStorage cleared**:
   - Check browser privacy settings
   - Verify not in incognito/private mode
   - Check for browser extensions clearing storage

2. **API not returning cart**:
   - Check database connection
   - Verify cart exists in database
   - Check session is valid

3. **Cart initialization failing**:
   - Check `init()` method in cart.js
   - Verify `getItem()` is working
   - Check for errors in console

### Issue: Stock Validation Errors

**Symptoms**: "Insufficient stock" when stock exists

**Diagnostic Steps**:
```bash
# Check product stock in database
# MongoDB:
db.products.findOne({_id: ObjectId("PRODUCT_ID")})

# MySQL:
mysql> SELECT id, name_en, stock, status FROM products WHERE id = 'PRODUCT_ID';

# Check via API
curl http://localhost:8000/api/v1/products/PRODUCT_SLUG
```

**Solutions**:

1. **Product not published**:
   - Verify product status is 'published'
   - Update: `UPDATE products SET status = 'published' WHERE id = 'PRODUCT_ID'`

2. **Stock not updated**:
   - Check StockService is updating correctly
   - Verify no concurrent transactions reducing stock
   - Check stock movement logs

3. **Variant issues**:
   - If using variants, verify variant stock
   - Check variant_id is being passed correctly

## Checkout Issues

### Issue: Checkout Page Won't Load

**Symptoms**: Error or blank page when accessing checkout

**Diagnostic Steps**:
```bash
# Check if cart has items
curl http://localhost:8000/api/v1/cart

# Check checkout validation
curl http://localhost:8000/api/v1/checkout/validate

# Check error logs
tail -f storage/logs/app-$(date +%Y-%m-%d).log
```

**Solutions**:

1. **Empty cart**:
   - Add items to cart first
   - Check cart API returns items

2. **Validation failing**:
   - Check stock validation errors
   - Verify all products still available
   - Check for unpublished products

3. **Missing address for logged-in user**:
   - User may need to add shipping address
   - Check address requirements in form

### Issue: Order Creation Fails

**Symptoms**: Error when submitting checkout form

**Diagnostic Steps**:
```bash
# Test order creation
curl -X POST http://localhost:8000/api/v1/checkout \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "9800000000",
    "address": "Test Address",
    "payment_method": "cod"
  }'

# Check validation errors in response
```

**Solutions**:

1. **Missing required fields**:
   - Verify all required fields provided:
     - name, email, phone, address
   - Check email format validation
   - Verify phone number format (Nepal: 10 digits, starts with 98/97)

2. **Email validation failing**:
   ```php
   // Email must match:
   /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
   ```

3. **Stock issues**:
   - Check cart validation before order
   - Verify all items have sufficient stock

4. **Database errors**:
   - Check database connection
   - Verify orders table/collection exists
   - Check for constraint violations

## Payment Issues

### Issue: eSewa Payment Not Initiating

**Symptoms**: Payment button doesn't redirect to eSewa

**Diagnostic Steps**:
```bash
# Check eSewa configuration
grep ESEWA .env

# Test payment initiation
curl -X POST http://localhost:8000/payment/esewa/initiate \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "ORD-12345",
    "amount": 1000,
    "shipping": 100
  }'

# Expected: Payment URL and params
```

**Solutions**:

1. **Missing configuration**:
   ```bash
   # Check .env has:
   ESEWA_MERCHANT_CODE=EPAYTEST
   ESEWA_SECRET_KEY=8gBm/:&EnhH.1/q
   PAYMENT_TEST_MODE=true
   ```

2. **Order not found**:
   - Verify order exists in database
   - Check order_id is correct format
   - Ensure order not already paid

3. **Amount validation failing**:
   - Amount must be numeric and > 0
   - Check amount format (decimal)

4. **Popup blocked**:
   - Check browser popup blocker
   - Allow popups for site

### Issue: eSewa Callback Not Working

**Symptoms**: Payment completes but order not updated

**Diagnostic Steps**:
```bash
# Check callback URLs are accessible
curl http://localhost:8000/payment/esewa/success?refId=TEST&oid=ORD-12345&amt=1100

# Check eSewa logs
tail -f storage/logs/esewa-$(date +%Y-%m-%d).log

# Check order status
# MongoDB:
db.orders.findOne({order_number: "ORD-12345"})

# MySQL:
mysql> SELECT order_number, payment_status, status FROM orders WHERE order_number = 'ORD-12345';
```

**Solutions**:

1. **Callback URL not accessible**:
   - Verify URLs in config/payment.php
   - Check server firewall allows incoming requests
   - Ensure domain is publicly accessible (not localhost)
   - For sandbox testing, use ngrok or similar

2. **Verification failing**:
   - Check eSewa service is up
   - Verify merchant code is correct
   - Check reference ID format
   - Review verification logs

3. **Order update failing**:
   - Check OrderService handlePaymentSuccess
   - Verify PaymentStatus enum values
   - Check database permissions

### Issue: Payment Verification Always Fails

**Symptoms**: All payment verifications return failure

**Diagnostic Steps**:
```bash
# Test verification directly
curl -X POST http://localhost:8000/payment/esewa/verify \
  -H "Content-Type: application/json" \
  -d '{
    "reference_id": "ESEWA-REF-123",
    "order_id": "ORD-12345",
    "amount": 1100
  }'

# Check eSewa sandbox status
curl https://uat.esewa.com.np/epay/transrec?amt=1100&rid=ESEWA-REF-123&pid=ORD-12345&scd=EPAYTEST

# Check logs
grep "verification" storage/logs/esewa-*.log
```

**Solutions**:

1. **Wrong merchant code**:
   - For sandbox: use `EPAYTEST`
   - For production: use your assigned code
   - Check ESEWA_MERCHANT_CODE in .env

2. **Amount mismatch**:
   - Amount must match exactly (including decimals)
   - Format: `1100.00` not `1100`
   - Check tAmt calculation includes shipping

3. **Invalid reference ID**:
   - Verify reference ID from eSewa callback
   - Check it's passed correctly to verification
   - May need to wait before verifying (race condition)

4. **Network issues**:
   - Check internet connectivity
   - Verify no firewall blocking eSewa
   - Check SSL certificate validation

## Database Issues

### Issue: MongoDB Connection Fails

**Symptoms**: "Failed to connect to MongoDB" errors

**Diagnostic Steps**:
```bash
# Test MongoDB connection
php test_mongo.php

# Check MongoDB is running
sudo systemctl status mongod

# Test connection manually
mongosh "mongodb://localhost:27017/"

# Check .env configuration
grep MONGO .env
```

**Solutions**:

1. **MongoDB not running**:
   ```bash
   sudo systemctl start mongod
   sudo systemctl enable mongod
   ```

2. **Wrong connection string**:
   ```bash
   # Local: mongodb://localhost:27017/
   # Atlas: mongodb+srv://user:pass@cluster.mongodb.net/
   ```

3. **Authentication issues**:
   - Verify username/password in connection string
   - Check database user has correct permissions

### Issue: Cart Not Persisting in Database

**Symptoms**: Cart empty on page refresh

**Diagnostic Steps**:
```bash
# Check carts collection/table
# MongoDB:
db.carts.find().pretty()

# MySQL:
mysql> SELECT * FROM carts;

# Check cart service is saving
# Add item and check immediately
```

**Solutions**:

1. **Session not set**:
   - Check session is starting
   - Verify `cart_session_id` in session
   - Check session driver is working

2. **Database write failing**:
   - Check database permissions
   - Verify collection/table exists
   - Check for unique constraint violations

3. **Model not saving**:
   - Check Cart model save() method
   - Verify MongoDB/MySQL detection
   - Check for model errors

## Frontend Issues

### Issue: JavaScript Not Loading

**Symptoms**: No Alpine.js functionality, plain HTML

**Diagnostic Steps**:
```bash
# Check if Vite built assets
ls -la public/build/

# Check for build errors
npm run build

# Check if assets are referenced in HTML
curl http://localhost:8000/ | grep "build/assets"
```

**Solutions**:

1. **Assets not built**:
   ```bash
   npm install
   npm run build
   ```

2. **Vite manifest missing**:
   - Check `public/build/.vite/manifest.json` exists
   - Rebuild if missing

3. **Wrong asset paths**:
   - Check BASE_URL in vite.config.js
   - Verify asset URLs in view templates

### Issue: Styles Not Applied

**Symptoms**: Broken layout, no CSS

**Diagnostic Steps**:
```bash
# Check Tailwind generated CSS
ls -la public/build/assets/*.css

# Rebuild
npm run build

# Check CSS is loaded
# Browser Network tab: Look for CSS files
```

**Solutions**:

1. **CSS not built**:
   ```bash
   npm run build
   ```

2. **Purge removed classes**:
   - Check tailwind.config.js content paths
   - Add missing paths to content array

3. **PostCSS issues**:
   - Check postcss.config.js
   - Verify autoprefixer installed

## API Issues

### Issue: CORS Errors

**Symptoms**: "Blocked by CORS policy" in console

**Solutions**:

1. **Add CORS headers**:
   ```php
   // In middleware or controller
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
   header('Access-Control-Allow-Headers: Content-Type');
   ```

2. **Use same origin**:
   - API and frontend on same domain
   - No cross-origin requests needed

### Issue: 404 Not Found on API Calls

**Symptoms**: API endpoints return 404

**Diagnostic Steps**:
```bash
# Check routes are registered
php -r "require 'routes/api.php'; var_dump(\$router);"

# Test endpoint directly
curl -v http://localhost:8000/api/v1/cart

# Check .htaccess/nginx config for rewrites
```

**Solutions**:

1. **Routes not loaded**:
   - Verify routes/api.php is included
   - Check router initialization

2. **Wrong prefix**:
   - API routes should be `/api/v1/...`
   - Check prefix in routes/api.php

3. **Rewrite rules**:
   - Apache: Check .htaccess
   - Nginx: Check nginx.conf

### Issue: 500 Internal Server Error

**Symptoms**: API returns 500 error

**Diagnostic Steps**:
```bash
# Check PHP error log
tail -f /var/log/php/error.log

# Check application log
tail -f storage/logs/app-$(date +%Y-%m-%d).log

# Enable debug mode temporarily
# In .env: APP_DEBUG=true
```

**Solutions**:

1. **PHP errors**:
   - Check error logs for details
   - Fix syntax/runtime errors
   - Check for missing dependencies

2. **Database errors**:
   - Verify database connection
   - Check for SQL syntax errors
   - Verify table/collection exists

3. **Permission errors**:
   - Check file permissions
   - Verify storage/ is writable
   - Check log directory exists

## Logging and Debugging

### Enable Debug Mode

```bash
# .env
APP_DEBUG=true
APP_ENV=development
```

### Check Application Logs

```bash
# View recent logs
tail -f storage/logs/app-$(date +%Y-%m-%d).log

# Search for errors
grep -i error storage/logs/app-*.log

# Search for specific order
grep "ORD-12345" storage/logs/app-*.log
```

### Check eSewa Logs

```bash
# View eSewa transaction logs
tail -f storage/logs/esewa-$(date +%Y-%m-%d).log

# Search for payment initiation
grep "Payment initiated" storage/logs/esewa-*.log

# Search for verification
grep "verified" storage/logs/esewa-*.log
```

### Browser Console Debugging

```javascript
// Check Alpine.js
window.Alpine

// Check cart store
Alpine.store('cart')

// Check utilities
window.KhairawangDairy

// Enable Alpine dev tools
Alpine.devtools = true

// Watch cart changes
Alpine.effect(() => {
  console.log('Cart items:', Alpine.store('cart').items)
})
```

### Network Debugging

```bash
# Monitor API calls
# Browser Network tab > Filter by XHR

# Copy as cURL for testing
# Right-click request > Copy > Copy as cURL

# Test with curl
curl -v http://localhost:8000/api/v1/cart
```

### Database Debugging

```bash
# MongoDB
mongosh
use khairawang_dairy
db.carts.find().pretty()
db.orders.find({order_number: "ORD-12345"}).pretty()

# MySQL
mysql -u root -p khairawang_dairy
SELECT * FROM carts;
SELECT * FROM orders WHERE order_number = 'ORD-12345';
```

## Quick Diagnostic Checklist

When troubleshooting, run through this checklist:

- [ ] Check browser console for JavaScript errors
- [ ] Verify API endpoints are accessible
- [ ] Check database connection is working
- [ ] Review application logs for errors
- [ ] Verify environment variables are set correctly
- [ ] Check localStorage/session is persisting
- [ ] Ensure products exist and are published
- [ ] Verify stock levels are sufficient
- [ ] Check payment configuration is correct
- [ ] Verify all required services are running

## Getting Help

If issues persist after trying these solutions:

1. **Enable full logging**:
   ```bash
   APP_DEBUG=true
   ESEWA_LOG_TRANSACTIONS=true
   ```

2. **Collect information**:
   - Error messages from logs
   - Steps to reproduce
   - Browser console output
   - Network tab screenshot

3. **Check documentation**:
   - CART_FUNCTIONALITY.md
   - ESEWA_INTEGRATION.md
   - MONGODB_CART_VERIFICATION.md

4. **Contact support**:
   - Email: support@khairawangdairy.com
   - GitHub: Create an issue with debug info

---

**Last Updated**: December 2024  
**Version**: 1.0
