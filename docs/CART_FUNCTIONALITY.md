# Shopping Cart Functionality Guide

## Overview

This document provides comprehensive information about the shopping cart functionality in KHAIRAWANG DAIRY eCommerce platform. The cart system supports both client-side (localStorage) and server-side (API) synchronization for a seamless user experience.

## Table of Contents

1. [Architecture](#architecture)
2. [Features](#features)
3. [API Endpoints](#api-endpoints)
4. [Frontend Integration](#frontend-integration)
5. [Backend Implementation](#backend-implementation)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)

## Architecture

The cart system uses a hybrid approach:

- **Client-side**: Alpine.js store with localStorage for immediate UI updates
- **Server-side**: PHP backend with MongoDB/MySQL for persistence and validation
- **Synchronization**: API calls keep client and server in sync

### Data Flow

```
User Action → Alpine.js Store → API Request → Backend Validation → Database Update
              ↓
        localStorage ← API Response ← Success/Error
```

## Features

### Implemented Features

- ✅ Add products to cart from listing and detail pages
- ✅ Update product quantities
- ✅ Remove items from cart
- ✅ Clear entire cart
- ✅ Real-time cart count in header
- ✅ Cart persistence across sessions
- ✅ Stock validation before adding items
- ✅ Product availability checks
- ✅ Free shipping threshold (NPR 1,000+)
- ✅ Guest cart merge on login
- ✅ Support for product variants

### Cart Store Features

The Alpine.js cart store (`resources/js/alpine/cart.js`) provides:

- `items`: Array of cart items
- `count`: Total item count
- `subtotal`: Sum of item prices
- `shippingCost`: Calculated shipping cost
- `total`: Grand total (subtotal + shipping)
- `add()`: Add product to cart (localStorage)
- `addViaApi()`: Add product via API (server-side)
- `remove()`: Remove item from cart
- `updateQuantity()`: Update item quantity
- `clear()`: Clear all items
- `refresh()`: Sync cart from server

## API Endpoints

### Cart Management

#### Get Cart Contents
```http
GET /api/v1/cart
```

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": "product_id",
        "product_id": "507f...",
        "name": "Fresh Farm Milk",
        "price": 120.00,
        "quantity": 2,
        "stock": 50,
        "image": "/uploads/products/milk.jpg",
        "total": 240.00
      }
    ],
    "count": 2,
    "subtotal": 240.00,
    "shipping": 100.00,
    "total": 340.00,
    "free_shipping": false
  }
}
```

#### Add Item to Cart
```http
POST /api/v1/cart/items
Content-Type: application/json

{
  "product_id": "507f1f77bcf86cd799439011",
  "quantity": 2,
  "variant_id": "variant_123" // optional
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Item added to cart",
  "cart": {
    "items": [...],
    "count": 2,
    "total": 340.00
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Insufficient stock",
  "available_stock": 1
}
```

#### Update Item Quantity
```http
PUT /api/v1/cart/items/{item_id}
Content-Type: application/json

{
  "quantity": 3
}
```

**Response:**
```json
{
  "success": true,
  "message": "Cart updated",
  "cart": {...}
}
```

#### Remove Item
```http
DELETE /api/v1/cart/items/{item_id}
```

**Response:**
```json
{
  "success": true,
  "message": "Item removed from cart",
  "cart": {...}
}
```

#### Clear Cart
```http
DELETE /api/v1/cart/clear
```

**Response:**
```json
{
  "success": true,
  "message": "Cart cleared",
  "cart": {
    "items": [],
    "count": 0,
    "total": 0.00
  }
}
```

#### Get Cart Count
```http
GET /api/v1/cart/count
```

**Response:**
```json
{
  "success": true,
  "count": 2,
  "total": 340.00
}
```

#### Sync Cart (Login)
```http
POST /api/v1/cart/sync
Content-Type: application/json

{
  "items": [
    {
      "product_id": "507f...",
      "quantity": 2,
      "variant_id": null
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Cart synced successfully",
  "cart": {...}
}
```

## Frontend Integration

### Using Alpine.js Cart Store

#### Add to Cart (Client-side)
```javascript
// In Alpine.js component
Alpine.data('productCard', (product) => ({
  async addToCart() {
    // Client-side only (localStorage)
    this.$store.cart.add(product, 1);
  }
}));
```

#### Add to Cart (Server-side)
```javascript
// In Alpine.js component with API call
Alpine.data('productDetail', () => ({
  product: {},
  quantity: 1,
  
  async addToCart() {
    // Server-side with validation
    const result = await this.$store.cart.addViaApi(
      this.product.id,
      this.quantity,
      this.selectedVariant?.id
    );
    
    if (result.success) {
      // Cart automatically refreshed
      console.log('Added to cart');
    } else {
      console.error(result.message);
    }
  }
}));
```

#### Manual API Call
```javascript
async function addToCart(productId, quantity = 1) {
  try {
    const response = await fetch('/api/v1/cart/items', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        product_id: productId,
        quantity: quantity
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Refresh cart from server
      Alpine.store('cart').refresh();
      console.log('Item added:', data.message);
    } else {
      console.error('Failed:', data.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}
```

### Cart Component Example

```html
<!-- Cart dropdown in header -->
<div x-data @click="$store.cart.toggle()">
  <button class="cart-button">
    <svg><!-- Cart icon --></svg>
    <span x-text="$store.cart.count"></span>
  </button>
</div>

<!-- Cart sidebar -->
<div x-show="$store.cart.isOpen" x-cloak>
  <template x-for="(item, index) in $store.cart.items" :key="item.id">
    <div class="cart-item">
      <img :src="item.image" :alt="item.name">
      <div>
        <h4 x-text="item.name"></h4>
        <p x-text="$store.cart.formatPrice(item.price)"></p>
      </div>
      <input type="number" 
             :value="item.quantity"
             @change="$store.cart.updateQuantity(index, $event.target.value)">
      <button @click="$store.cart.remove(index)">Remove</button>
    </div>
  </template>
  
  <div class="cart-summary">
    <div>Subtotal: <span x-text="$store.cart.formattedSubtotal"></span></div>
    <div>Shipping: <span x-text="$store.cart.formattedShipping"></span></div>
    <div>Total: <span x-text="$store.cart.formattedTotal"></span></div>
  </div>
</div>
```

## Backend Implementation

### Cart Model

The `Cart` model (`app/Models/Cart.php`) supports both MySQL and MongoDB:

```php
// Get cart for user
$cart = Cart::forUser($userId);

// Get cart for session (guest)
$cart = Cart::forSession($sessionId);

// Add item to cart
$cart->addItem($productId, $quantity, $variantId);

// Update item quantity
$cart->updateItemQuantity($itemId, $quantity);

// Remove item
$cart->removeItem($itemId);

// Clear cart
$cart->clear();

// Get cart items with product details
$items = $cart->itemsWithProducts();
```

### Cart Service

The `CartService` (`app/Services/CartService.php`) provides business logic:

```php
$cartService = new CartService();

// Add item with validation
$result = $cartService->addItem($productId, $quantity);

// Get cart contents
$contents = $cartService->getCartContents();

// Validate for checkout
$validation = $cartService->validateForCheckout();

// Merge guest cart after login
$cartService->mergeGuestCart($userId);
```

### Stock Validation

The cart automatically validates:

1. **Product exists**: Checks if product is in database
2. **Product published**: Checks if product status is 'published'
3. **Stock available**: Checks if requested quantity is in stock
4. **Variant availability**: Validates variant if specified

### Error Handling

The API returns detailed error messages:

- `"Invalid product or quantity"`: Invalid input parameters
- `"Product not found"`: Product doesn't exist or not published
- `"Insufficient stock"`: Requested quantity exceeds available stock
- `"Only X units available"`: Partial stock available

## Testing

### Manual Testing Steps

#### 1. Add to Cart (Guest User)

```bash
# Start server
php -S localhost:8000 -t public

# Add product to cart
curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -d '{"product_id": "PRODUCT_ID", "quantity": 2}'

# Expected: {"success": true, "message": "Item added to cart"}
```

#### 2. View Cart

```bash
# Get cart contents
curl http://localhost:8000/api/v1/cart

# Expected: Cart with items array, count, and total
```

#### 3. Update Quantity

```bash
# Update item quantity
curl -X PUT http://localhost:8000/api/v1/cart/items/ITEM_ID \
  -H "Content-Type: application/json" \
  -d '{"quantity": 3}'

# Expected: {"success": true, "message": "Cart updated"}
```

#### 4. Remove Item

```bash
# Remove item from cart
curl -X DELETE http://localhost:8000/api/v1/cart/items/ITEM_ID

# Expected: {"success": true, "message": "Item removed"}
```

#### 5. Browser Testing

1. Open http://localhost:8000/products
2. Click "Add to Cart" on any product
3. Verify cart icon count increases
4. Open cart dropdown/page
5. Verify product details are correct
6. Update quantity - verify updates
7. Remove item - verify removal
8. Refresh page - verify cart persists

### Frontend Testing

Open browser console and test Alpine.js store:

```javascript
// Check cart store exists
Alpine.store('cart')

// Add item
Alpine.store('cart').add({
  id: '1',
  name: 'Test Product',
  price: 100,
  image: '/path/to/image.jpg'
}, 2)

// Check count
Alpine.store('cart').count // Should be 2

// Check total
Alpine.store('cart').total // Should be 200 + shipping

// Refresh from server
await Alpine.store('cart').refresh()
```

## Troubleshooting

### Issue: Add to Cart Not Working

**Symptoms**: Clicking "Add to Cart" does nothing

**Solutions**:
1. Check browser console for JavaScript errors
2. Verify Alpine.js is loaded: `window.Alpine`
3. Check cart store exists: `Alpine.store('cart')`
4. Verify API endpoint is accessible: `curl http://localhost:8000/api/v1/cart`
5. Check network tab for failed requests

### Issue: Cart Count Not Updating

**Symptoms**: Cart icon shows wrong count

**Solutions**:
1. Verify `refresh()` is called after adding items
2. Check localStorage: `localStorage.getItem('khairawang_cart')`
3. Ensure cart initialization in `init()` method
4. Clear localStorage and retry: `localStorage.clear()`

### Issue: Cart Empty After Refresh

**Symptoms**: Cart items disappear on page reload

**Solutions**:
1. Check localStorage is working: `KhairawangDairy.storage.isAvailable()`
2. Verify session is persisting (check cookies)
3. Check if user is logged in vs guest
4. Verify API `/api/v1/cart` returns correct data
5. Check MongoDB/MySQL connection

### Issue: Stock Validation Failing

**Symptoms**: "Insufficient stock" error when stock is available

**Solutions**:
1. Verify product stock in database:
   ```javascript
   db.products.findOne({_id: ObjectId("PRODUCT_ID")})
   ```
2. Check product status is 'published'
3. Verify StockService logic
4. Check for concurrent updates reducing stock

### Issue: Payment Not Initiating

**Symptoms**: Payment button doesn't work or redirects fail

**Solutions**:
1. Verify cart has items before checkout
2. Check order was created successfully
3. Verify eSewa configuration in `.env`
4. Check payment initiation logs
5. Ensure callbacks URLs are accessible

## Configuration

### Environment Variables

```bash
# Database (choose one)
DB_CONNECTION=mongodb
MONGO_URI=mongodb://localhost:27017/
MONGO_DATABASE=khairawang_dairy

# OR
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=khairawang_dairy
DB_USERNAME=root
DB_PASSWORD=

# Cart Settings
CART_SESSION_LIFETIME=120  # minutes
FREE_SHIPPING_THRESHOLD=1000  # NPR
DEFAULT_SHIPPING_COST=100  # NPR
```

### JavaScript Configuration

Edit `resources/js/alpine/cart.js`:

```javascript
const CART_STORAGE_KEY = 'khairawang_cart';
const FREE_SHIPPING_THRESHOLD = 1000;
const SHIPPING_COST = 100;
```

## Best Practices

### 1. Always Use API for Add to Cart

Instead of just using localStorage, use `addViaApi()` for:
- Stock validation
- Product availability checking
- Server-side persistence
- User authentication handling

### 2. Handle Errors Gracefully

```javascript
const result = await Alpine.store('cart').addViaApi(productId, quantity);
if (!result.success) {
  // Show user-friendly error message
  Alpine.store('toast').show(result.message, 'error');
}
```

### 3. Sync Cart on Login

```javascript
// After successful login
if (Alpine.store('cart').items.length > 0) {
  await fetch('/api/v1/cart/sync', {
    method: 'POST',
    body: JSON.stringify({
      items: Alpine.store('cart').items
    })
  });
}
```

### 4. Clear Cart After Order

```php
// After successful order
$cartService->clearCart();
```

### 5. Validate Before Checkout

```php
$validation = $cartService->validateForCheckout();
if (!$validation['valid']) {
  return Response::error($validation['message']);
}
```

## Security Considerations

1. **Input Validation**: All API inputs are validated
2. **Stock Checks**: Server-side validation prevents overselling
3. **Authentication**: Guest and user carts are isolated
4. **CSRF Protection**: API endpoints use CSRF tokens
5. **Rate Limiting**: Consider adding rate limits for cart operations

## Performance Optimization

1. **Indexes**: Create database indexes on cart queries
   ```javascript
   db.carts.createIndex({ "session_id": 1 })
   db.carts.createIndex({ "user_id": 1 })
   ```

2. **Cleanup**: Regularly clean abandoned carts
   ```php
   Cart::cleanupAbandoned(30); // Remove carts older than 30 days
   ```

3. **Caching**: Cache product details to reduce database queries

4. **Lazy Loading**: Load cart data only when needed

## Support

For issues or questions:
- Check the troubleshooting section above
- Review logs in `storage/logs/`
- File an issue on GitHub
- Contact: support@khairawangdairy.com

---

**Last Updated**: December 2024  
**Version**: 1.0
