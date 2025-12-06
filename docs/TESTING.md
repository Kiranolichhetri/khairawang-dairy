# End-to-End Testing Guide

## Overview

This guide provides step-by-step instructions for testing the complete eCommerce flow in KHAIRAWANG DAIRY platform, from adding products to cart through payment completion.

## Prerequisites

Before testing, ensure:

- Development server is running
- Database (MongoDB/MySQL) is connected and populated
- Products exist in database with status='published' and stock > 0
- Environment variables are configured correctly
- Assets are built (`npm run build`)

## Test Environment Setup

### 1. Start Development Server

```bash
cd /home/runner/work/khairawang-dairy/khairawang-dairy

# Start PHP built-in server
php -S localhost:8000 -t public public/index.php

# Or use alternative port
php -S localhost:8080 -t public public/index.php
```

### 2. Verify Environment Configuration

Check `.env` file has required settings:

```bash
# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database - choose one
DB_CONNECTION=mongodb
MONGO_URI=mongodb://localhost:27017/
MONGO_DATABASE=khairawang_dairy

# eSewa Sandbox
ESEWA_MERCHANT_CODE=EPAYTEST
ESEWA_SECRET_KEY=8gBm/:&EnhH.1/q
PAYMENT_TEST_MODE=true
ESEWA_LOG_TRANSACTIONS=true
```

### 3. Verify Database Has Products

**MongoDB**:
```bash
mongosh
use khairawang_dairy
db.products.countDocuments({status: 'published', stock: {$gt: 0}})
# Should return number > 0

# View sample products
db.products.find({status: 'published', stock: {$gt: 0}}).limit(3).pretty()
```

**MySQL**:
```sql
SELECT COUNT(*) FROM products WHERE status = 'published' AND stock > 0;
-- Should return number > 0

-- View sample products
SELECT id, name_en, slug, price, stock FROM products WHERE status = 'published' AND stock > 0 LIMIT 3;
```

### 4. Clear Previous Test Data

```bash
# Clear localStorage in browser console
localStorage.clear()

# Clear test carts from database
# MongoDB:
db.carts.deleteMany({})

# MySQL:
DELETE FROM carts;
DELETE FROM cart_items;
```

## Test Scenarios

### Scenario 1: Guest User - Add to Cart (Basic)

**Objective**: Verify guest user can add products to cart

**Steps**:

1. Open browser to http://localhost:8000
2. Navigate to Products page
3. Click "Add to Cart" on any product
4. Verify success notification appears
5. Check cart icon count increases
6. Click cart icon to open cart sidebar/page
7. Verify product appears in cart with correct details

**Expected Results**:
- ✅ Success toast notification shows "Added to cart!"
- ✅ Cart icon badge shows count (e.g., "1")
- ✅ Product appears in cart with name, price, quantity, image
- ✅ Cart total calculated correctly (item price × quantity + shipping)
- ✅ Free shipping indicator shows if total ≥ NPR 1,000

**Verification**:
```javascript
// Browser console
Alpine.store('cart').items.length // Should be > 0
Alpine.store('cart').count // Should match number of items
Alpine.store('cart').total // Should be subtotal + shipping
localStorage.getItem('khairawang_cart') // Should contain items JSON
```

### Scenario 2: Update Cart Quantities

**Objective**: Verify user can update product quantities in cart

**Steps**:

1. With items in cart, open cart page/sidebar
2. Increase quantity of an item
3. Verify cart total updates
4. Decrease quantity of an item
5. Verify cart total updates
6. Try to set quantity beyond stock limit
7. Verify validation prevents overselling

**Expected Results**:
- ✅ Quantity updates immediately in UI
- ✅ Cart total recalculates correctly
- ✅ Stock validation prevents quantity > available stock
- ✅ Error message shows for insufficient stock
- ✅ Cart persists after page refresh

**Verification**:
```bash
# API call to verify backend
curl http://localhost:8000/api/v1/cart | jq '.data.items[0].quantity'
```

### Scenario 3: Remove Items from Cart

**Objective**: Verify user can remove items from cart

**Steps**:

1. With multiple items in cart, click "Remove" on one item
2. Verify item is removed immediately
3. Verify cart count updates
4. Verify total recalculates
5. Remove all items to empty cart
6. Verify empty cart message displays

**Expected Results**:
- ✅ Item removed immediately from UI
- ✅ Cart count decreases
- ✅ Total recalculates
- ✅ Empty cart shows "Your cart is empty" message
- ✅ Checkout button disabled when cart empty

### Scenario 4: Guest User - Complete Checkout with COD

**Objective**: Verify guest user can complete order with Cash on Delivery

**Steps**:

1. Add products to cart (total < NPR 1,000 to test shipping)
2. Click "Checkout" button
3. Fill out shipping form:
   - Name: Test User
   - Email: test@example.com
   - Phone: 9800000000
   - Address: Kathmandu, Nepal
4. Select payment method: "Cash on Delivery"
5. Submit order
6. Verify order confirmation page shows
7. Note order number

**Expected Results**:
- ✅ Form validation works (required fields)
- ✅ Email format validation works
- ✅ Phone number validation works (Nepal format)
- ✅ Order created successfully
- ✅ Order confirmation page shows order number
- ✅ Order details displayed correctly
- ✅ Stock reduced in database
- ✅ Cart cleared after successful order

**Verification**:
```bash
# Check order in database
# MongoDB:
db.orders.findOne({order_number: "ORD-XXXXX"})

# MySQL:
SELECT * FROM orders WHERE order_number = 'ORD-XXXXX';

# Verify stock reduced
# MongoDB:
db.products.findOne({_id: ObjectId("PRODUCT_ID")}, {stock: 1})

# MySQL:
SELECT stock FROM products WHERE id = 'PRODUCT_ID';
```

### Scenario 5: eSewa Payment - Success Flow

**Objective**: Verify complete eSewa payment flow with successful payment

**Steps**:

1. Clear previous orders: `db.orders.deleteMany({})`
2. Add products to cart (total > NPR 100)
3. Proceed to checkout
4. Fill out shipping information
5. Select payment method: "eSewa"
6. Click "Pay with eSewa"
7. Verify redirect to eSewa sandbox
8. On eSewa page, use test credentials:
   - eSewa ID: 9800000000
   - Password: nepal@123
   - MPIN: 1234
9. Complete payment
10. Verify redirect back to success page
11. Note order number and transaction ID

**Expected Results**:
- ✅ Order created with status "Pending"
- ✅ Redirected to eSewa sandbox (uat.esewa.com.np)
- ✅ eSewa shows correct amount (subtotal + shipping)
- ✅ After payment, redirected to /checkout/success/ORDER_NUMBER
- ✅ Order status updated to "Processing"
- ✅ Payment status updated to "Paid"
- ✅ Transaction ID recorded
- ✅ Success page shows order details
- ✅ Confirmation email sent (if email configured)

**Verification**:
```bash
# Check order payment status
# MongoDB:
db.orders.findOne(
  {order_number: "ORD-XXXXX"},
  {order_number: 1, status: 1, payment_status: 1, transaction_id: 1}
)

# Expected:
# {
#   order_number: "ORD-XXXXX",
#   status: "processing",
#   payment_status: "paid",
#   transaction_id: "ESEWA-REF-XXXXX"
# }

# Check logs
tail -n 50 storage/logs/esewa-$(date +%Y-%m-%d).log | grep "ORD-XXXXX"

# Should see:
# - Payment initiated
# - Payment verified successfully
# - Transaction ID recorded
```

### Scenario 6: eSewa Payment - Failure Flow

**Objective**: Verify proper handling of failed/cancelled payment

**Steps**:

1. Add products to cart
2. Proceed to checkout and fill shipping info
3. Select payment method: "eSewa"
4. Click "Pay with eSewa"
5. On eSewa page, click "Cancel" or back button
6. Verify redirect to failure page

**Expected Results**:
- ✅ Redirected to /checkout/failed
- ✅ Error message displayed
- ✅ Order status remains "Pending"
- ✅ Payment status updated to "Failed"
- ✅ Stock restored (if reserved)
- ✅ User can retry payment or choose different method

**Verification**:
```bash
# Check order status
# MongoDB:
db.orders.findOne(
  {order_number: "ORD-XXXXX"},
  {order_number: 1, status: 1, payment_status: 1}
)

# Expected:
# {
#   order_number: "ORD-XXXXX",
#   status: "pending",
#   payment_status: "failed"
# }

# Check logs
tail -n 20 storage/logs/esewa-$(date +%Y-%m-%d).log | grep "failure"
```

### Scenario 7: Stock Validation

**Objective**: Verify stock validation prevents overselling

**Steps**:

1. Find product with low stock (e.g., stock = 2)
2. Add product to cart with quantity = 2
3. Try to add same product again with quantity = 1
4. Verify error message about insufficient stock
5. Try to update quantity in cart to 3
6. Verify error message

**Expected Results**:
- ✅ Cannot add quantity exceeding available stock
- ✅ Error message: "Only X units available"
- ✅ Cart quantity capped at available stock
- ✅ Stock check happens on both add and update

**Verification**:
```bash
# Test via API
curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "PRODUCT_ID",
    "quantity": 999
  }' | jq

# Expected: {"success": false, "message": "Only X units available"}
```

### Scenario 8: Cart Persistence

**Objective**: Verify cart persists across sessions and page refreshes

**Steps**:

1. Add products to cart as guest
2. Refresh page
3. Verify cart still contains items
4. Close browser completely
5. Reopen browser and navigate to site
6. Verify cart still contains items (localStorage)

**Expected Results**:
- ✅ Cart items persist after page refresh
- ✅ Cart items persist after browser close (localStorage)
- ✅ Cart items sync from server on load
- ✅ Guest cart session maintained via session cookie

**Verification**:
```javascript
// Before refresh
localStorage.getItem('khairawang_cart')
// Note the items

// After refresh
localStorage.getItem('khairawang_cart')
// Should match previous items
```

### Scenario 9: Logged-in User Cart

**Objective**: Verify cart works for authenticated users

**Steps**:

1. Register/login as user
2. Add products to cart
3. Logout
4. Add products to cart as guest
5. Login again
6. Verify guest cart merged with user cart

**Expected Results**:
- ✅ User cart persists in database
- ✅ User cart loads on login
- ✅ Guest cart merges into user cart on login
- ✅ No duplicate items (quantities combined)
- ✅ User cart persists across devices

**Verification**:
```bash
# Check user cart in database
# MongoDB:
db.carts.findOne({user_id: USER_ID})

# MySQL:
SELECT * FROM carts WHERE user_id = USER_ID;
SELECT * FROM cart_items WHERE cart_id = (SELECT id FROM carts WHERE user_id = USER_ID);
```

### Scenario 10: Concurrent Orders

**Objective**: Verify system handles concurrent purchases of limited stock

**Note**: This requires multiple browser windows/sessions

**Steps**:

1. Find product with stock = 1
2. In Browser A: Add product to cart
3. In Browser B: Add same product to cart
4. In Browser A: Complete checkout
5. In Browser B: Try to complete checkout
6. Verify Browser B gets stock validation error

**Expected Results**:
- ✅ First checkout succeeds
- ✅ Second checkout fails with "insufficient stock"
- ✅ Stock correctly updated after first order
- ✅ No overselling occurs

## API Testing

### Test Cart API Endpoints

```bash
# Add item to cart
curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -d '{"product_id": "PRODUCT_ID", "quantity": 2}' | jq

# Get cart
curl http://localhost:8000/api/v1/cart | jq

# Update item quantity
curl -X PUT http://localhost:8000/api/v1/cart/items/ITEM_ID \
  -H "Content-Type: application/json" \
  -d '{"quantity": 3}' | jq

# Remove item
curl -X DELETE http://localhost:8000/api/v1/cart/items/ITEM_ID | jq

# Clear cart
curl -X DELETE http://localhost:8000/api/v1/cart/clear | jq

# Get cart count
curl http://localhost:8000/api/v1/cart/count | jq
```

### Test Payment API Endpoints

```bash
# Initiate eSewa payment
curl -X POST http://localhost:8000/payment/esewa/initiate \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "ORD-12345",
    "amount": 1000,
    "shipping": 100
  }' | jq

# Verify payment
curl -X POST http://localhost:8000/payment/esewa/verify \
  -H "Content-Type: application/json" \
  -d '{
    "reference_id": "ESEWA-REF-123",
    "order_id": "ORD-12345",
    "amount": 1100
  }' | jq
```

## Logging Verification

### Check Application Logs

```bash
# View real-time logs
tail -f storage/logs/app-$(date +%Y-%m-%d).log

# Filter for cart operations
grep "cart" storage/logs/app-*.log

# Filter for orders
grep "order" storage/logs/app-*.log
```

### Check eSewa Transaction Logs

```bash
# View eSewa logs
tail -f storage/logs/esewa-$(date +%Y-%m-%d).log

# Filter by order
grep "ORD-12345" storage/logs/esewa-*.log

# Check payment initiations
grep "Payment initiated" storage/logs/esewa-*.log

# Check verifications
grep "verified" storage/logs/esewa-*.log
```

## Browser Testing Checklist

Test in multiple browsers:

- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari (if on macOS)
- [ ] Edge

Test responsive design:

- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

Check browser console:

- [ ] No JavaScript errors
- [ ] No network errors (failed requests)
- [ ] Alpine.js loaded correctly
- [ ] Cart store accessible

Check network tab:

- [ ] API calls successful (200/201 status)
- [ ] No CORS errors
- [ ] Request/response data correct

## Performance Testing

### Measure Page Load Times

```javascript
// Browser console
performance.timing.loadEventEnd - performance.timing.navigationStart
// Should be < 3000ms for good UX
```

### Measure API Response Times

```bash
# Time API requests
time curl http://localhost:8000/api/v1/cart

# Should be < 500ms
```

## Automated Testing (Future)

Template for automated tests using PHPUnit:

```php
class CartTest extends TestCase
{
    public function testAddToCart()
    {
        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => 'test_product',
            'quantity' => 2
        ]);
        
        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }
    
    public function testInsufficientStock()
    {
        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => 'test_product',
            'quantity' => 9999
        ]);
        
        $response->assertStatus(400)
                 ->assertJson(['success' => false]);
    }
}
```

## Test Results Template

Document test results:

```markdown
## Test Execution Report

**Date**: YYYY-MM-DD
**Tester**: Name
**Environment**: Development/Staging

### Test Results

| Scenario | Status | Notes |
|----------|--------|-------|
| Add to Cart (Guest) | ✅ Pass | - |
| Update Quantities | ✅ Pass | - |
| Remove Items | ✅ Pass | - |
| COD Checkout | ✅ Pass | - |
| eSewa Success | ✅ Pass | Order: ORD-12345 |
| eSewa Failure | ✅ Pass | - |
| Stock Validation | ✅ Pass | - |
| Cart Persistence | ✅ Pass | - |
| Logged-in User | ✅ Pass | - |
| Concurrent Orders | ✅ Pass | - |

### Issues Found

1. **Issue**: Description
   - **Severity**: High/Medium/Low
   - **Steps to Reproduce**: ...
   - **Expected**: ...
   - **Actual**: ...

### Browser Compatibility

- ✅ Chrome 120
- ✅ Firefox 121
- ✅ Safari 17
- ✅ Edge 120

### Performance Metrics

- Average Page Load: 2.1s
- Average API Response: 245ms
- Cart Operations: < 100ms

### Recommendations

1. ...
2. ...
```

## Support

For testing issues:
- Review [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- Check logs in `storage/logs/`
- Enable debug mode: `APP_DEBUG=true`

---

**Last Updated**: December 2024  
**Version**: 1.0
