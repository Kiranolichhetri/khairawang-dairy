# Cart Functionality Fixes - Verification Guide

This guide explains how to verify that the MongoDB cart functionality fixes are working correctly.

## Prerequisites

Before testing, ensure:
1. MongoDB is running and accessible
2. MongoDB connection is configured in `config/database.php` with `default => 'mongodb'`
3. At least one product exists in the `products` collection with `status: 'published'`
4. PHP MongoDB extension is installed and enabled

## Quick Verification

### Method 1: Run the Existing Test Script

```bash
cd /home/runner/work/khairawang-dairy/khairawang-dairy
php tests/manual/mongodb_cart_test.php
```

This script will automatically:
- Verify MongoDB connection
- Find a test product
- Create a test cart
- Add items to the cart
- Verify cart persistence
- Check MongoDB document structure
- Clean up test data

### Method 2: MongoDB Shell Verification

1. **Connect to MongoDB**:
```bash
mongosh "mongodb+srv://your-connection-string"
use khairawang_dairy
```

2. **Check carts collection**:
```javascript
// View all carts
db.carts.find().pretty()

// Find cart by session
db.carts.find({ session_id: "your-session-id" }).pretty()

// Verify cart structure
db.carts.findOne()
```

3. **Expected cart document structure**:
```javascript
{
  _id: ObjectId("..."),
  session_id: "test_abc123...",  // or null for user carts
  user_id: null,                  // or user ID for authenticated users
  items: [
    {
      item_id: "item_a1b2c3...",  // Prefixed with 'item_'
      product_id: "507f1f77...",   // MongoDB ObjectId as string
      variant_id: null,
      quantity: 2,
      added_at: ISODate("2024-01-15T10:30:00.000Z")
    }
  ],
  created_at: ISODate("2024-01-15T10:00:00.000Z"),
  updated_at: ISODate("2024-01-15T10:30:00.000Z")
}
```

### Method 3: API Testing with cURL

1. **Add item to cart**:
```bash
curl -X POST http://your-domain.com/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your-session-id" \
  -d '{
    "product_id": "507f1f77bcf86cd799439011",
    "quantity": 2
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Item added to cart",
  "cart": {
    "items": [
      {
        "id": "item_a1b2c3d4...",
        "product_id": "507f1f77...",
        "name": "Product Name",
        "price": 250.00,
        "quantity": 2,
        "total": 500.00,
        "image": "/uploads/products/..."
      }
    ],
    "count": 2,
    "subtotal": 500.00,
    "shipping": 100.00,
    "total": 600.00
  }
}
```

2. **Get cart contents**:
```bash
curl http://your-domain.com/api/v1/cart \
  -H "Cookie: PHPSESSID=your-session-id"
```

3. **Update item quantity**:
```bash
curl -X PUT http://your-domain.com/api/v1/cart/items/item_a1b2c3d4... \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your-session-id" \
  -d '{"quantity": 3}'
```

4. **Remove item from cart**:
```bash
curl -X DELETE http://your-domain.com/api/v1/cart/items/item_a1b2c3d4... \
  -H "Cookie: PHPSESSID=your-session-id"
```

### Method 4: Browser Testing

1. **Setup**:
   - Open your browser
   - Navigate to the website
   - Open Developer Tools (F12)
   - Go to Console tab

2. **Add product to cart**:
   - Navigate to a product page
   - Click "Add to Cart" button
   - Check Console for any errors
   - Verify cart icon in header updates with item count

3. **View cart**:
   - Navigate to `/cart` page
   - Verify items are displayed with correct:
     - Product name
     - Price
     - Quantity
     - Subtotal
     - Total (including shipping)

4. **Test quantity updates**:
   - Use quantity spinner or input field
   - Increase/decrease quantity
   - Verify subtotal and total update correctly
   - Refresh page and verify changes persist

5. **Test item removal**:
   - Click remove/delete button on an item
   - Verify item is removed from cart
   - Verify cart totals update
   - Refresh page and verify item stays removed

6. **Test checkout flow**:
   - Add items to cart
   - Proceed to checkout
   - Verify cart items are displayed on checkout page
   - Verify eSewa payment integration can access cart data

## Specific Issues Fixed

### Issue 1: MongoCart Initialization
**What was broken**: MongoCart failed to initialize due to missing collection parameter  
**How to verify**: Create a new cart and check it doesn't throw an error

```bash
# In PHP
$cart = new \App\Models\MongoCart('test-session-123');
$count = $cart->getItemCount(); // Should return 0, not throw error
```

### Issue 2: Cart Persistence
**What was broken**: Carts weren't persisting after creation due to incomplete data  
**How to verify**: Create cart, add item, reload, check items still exist

```bash
# In MongoDB shell
db.carts.find().count() // Should show created carts
db.carts.find({ items: { $ne: [] } }) // Should show carts with items
```

### Issue 3: Item ID Inconsistency
**What was broken**: Cart operations failed due to id/item_id mismatch  
**How to verify**: 
1. Add item to cart
2. Update quantity (should work, not fail)
3. Remove item (should work, not fail)
4. Check MongoDB - items should have 'item_id' field with 'item_' prefix

```javascript
// In MongoDB shell
db.carts.findOne().items[0]
// Should show:
// {
//   item_id: "item_abc123...",  // With 'item_' prefix
//   product_id: "...",
//   quantity: 2,
//   ...
// }
```

## Troubleshooting

### Cart appears empty after adding items

1. **Check MongoDB connection**:
   ```bash
   php -r "require 'vendor/autoload.php'; \$app = new Core\Application(__DIR__); \$app->mongo()->getDatabase();"
   ```

2. **Verify database default setting**:
   ```bash
   grep "default" config/database.php
   # Should show: "default" => "mongodb"
   ```

3. **Check session**:
   - Verify session cookies are being set
   - Check `cart_session_id` is in session data
   - Verify session persists across requests

### Items not persisting after page refresh

1. **Check timestamps**:
   ```javascript
   db.carts.findOne()
   // Both created_at and updated_at should exist
   ```

2. **Verify MongoDB updates**:
   ```javascript
   // Watch for updates
   db.carts.watch()
   // Then perform cart operation in browser
   ```

### Update/Remove operations fail

1. **Check item_id format**:
   ```javascript
   db.carts.findOne().items[0].item_id
   // Should start with "item_" prefix
   ```

2. **Verify API request is using correct ID**:
   - Get cart contents
   - Note the 'id' field in response
   - Use that exact ID in update/remove request

## Expected Behavior After Fixes

✅ Users can add products to cart  
✅ Cart items persist across page refreshes  
✅ Cart count displays correctly in header  
✅ Cart page shows all added items with details  
✅ Users can update item quantities  
✅ Users can remove items from cart  
✅ Cart data is accessible for checkout  
✅ eSewa payment integration can access cart totals  
✅ MongoDB documents have correct structure  
✅ All cart operations complete without errors  

## MongoDB Indexes

For optimal performance, create these indexes:

```javascript
db.carts.createIndex({ "session_id": 1 })
db.carts.createIndex({ "user_id": 1 })
db.carts.createIndex({ "updated_at": 1 })
```

## Integration Points

The cart functionality integrates with:

1. **Product Pages**: Add to cart buttons
2. **Cart Page**: Display cart contents, update quantities, remove items
3. **Checkout Page**: Display cart for order creation
4. **eSewa Payment**: Access cart totals for payment processing
5. **Header/Navigation**: Display cart item count
6. **User Login**: Merge guest cart with user cart

All integration points should work correctly after these fixes.
