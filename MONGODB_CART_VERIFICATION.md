# MongoDB Cart Support - Verification Guide

## Overview

The Cart model now fully supports MongoDB as the default database connection. This document outlines the changes made and provides steps to verify the implementation.

## Changes Made

### 1. Cart Model (`app/Models/Cart.php`)

#### Timestamp Consistency
- **Fixed**: `addItemMongo()` now uses `\MongoDB\BSON\UTCDateTime` instead of string dates for the `added_at` field
- **Reason**: Ensures consistency with MongoDB's native date handling

#### Item Matching Logic  
- **Fixed**: `updateItemQuantityMongo()` and `removeItemMongo()` now only match by item `id`, not by `product_id`
- **Reason**: Prevents ambiguity when cart contains multiple items with the same product but different variants

### 2. Existing MongoDB Support

The following methods already have full MongoDB support:
- `items()` - Returns cart items from embedded array
- `itemsWithProducts()` - Enriches items with product details from MongoDB
- `addItem()` - Adds items to MongoDB cart with validation
- `updateItemQuantity()` - Updates item quantities in MongoDB
- `removeItem()` - Removes items from MongoDB cart
- `clear()` - Clears all items from cart
- `getItemCount()` - Counts total items/quantities
- `forSession()` - Gets or creates cart for guest session
- `forUser()` - Gets or creates cart for authenticated user
- `mergeGuestCart()` - Merges guest cart into user cart after login
- `cleanupAbandoned()` - Removes old guest carts

## MongoDB Cart Document Structure

```json
{
  "_id": ObjectId("..."),
  "session_id": "abc123..." | null,
  "user_id": 123 | null,
  "items": [
    {
      "id": "a1b2c3d4e5f6...",
      "product_id": "507f1f77bcf86cd799439011",
      "variant_id": "variant_123" | null,
      "quantity": 2,
      "added_at": ISODate("2024-01-15T10:30:00Z")
    }
  ],
  "created_at": ISODate("2024-01-15T10:00:00Z"),
  "updated_at": ISODate("2024-01-15T10:30:00Z")
}
```

## Manual Verification Steps

### Prerequisites
1. MongoDB connection configured in `config/database.php`:
   ```php
   "default" => "mongodb"
   ```
2. MongoDB contains products in the `products` collection
3. PHP MongoDB extension installed (`ext-mongodb`)

### Step 1: Start Development Server
```bash
cd /home/runner/work/khairawang-dairy/khairawang-dairy
php -S localhost:8000 -t public public/index.php
```

### Step 2: Test Add to Cart (API)

Using curl:
```bash
# Get a product ID from MongoDB first
# Replace PRODUCT_ID with actual MongoDB ObjectId

curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -d '{"product_id": "PRODUCT_ID", "quantity": 2}'
```

Expected response:
```json
{
  "success": true,
  "message": "Item added to cart",
  "cart": {
    "items": [...],
    "count": 2,
    "subtotal": 500.00,
    "shipping": 100.00,
    "total": 600.00
  }
}
```

### Step 3: Verify Cart Contents

```bash
curl http://localhost:8000/api/v1/cart
```

Expected response:
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": "a1b2c3d4e5f6...",
        "product_id": "PRODUCT_ID",
        "name": "Product Name",
        "price": 250.00,
        "quantity": 2,
        "total": 500.00,
        ...
      }
    ],
    "count": 2,
    "subtotal": 500.00,
    "total": 600.00
  }
}
```

### Step 4: Test Update Quantity

```bash
# Replace ITEM_ID with the id from cart contents response

curl -X PUT http://localhost:8000/api/v1/cart/items/ITEM_ID \
  -H "Content-Type: application/json" \
  -d '{"quantity": 3}'
```

Expected: Success response with updated cart

### Step 5: Test Remove Item

```bash
curl -X DELETE http://localhost:8000/api/v1/cart/items/ITEM_ID
```

Expected: Success response with item removed

### Step 6: Verify in MongoDB

Using MongoDB shell or Compass:
```javascript
// View carts collection
db.carts.find().pretty()

// Verify cart structure
db.carts.findOne({session_id: "..."})
```

Verify:
- Cart document exists
- `items` array is properly populated
- Items have correct structure (id, product_id, quantity, added_at)
- Timestamps are UTCDateTime objects

### Step 7: Test Browser Integration

1. Open http://localhost:8000/products in browser
2. Click "Add to Cart" on any product
3. Check cart icon in header - count should increment
4. Open browser console - should show no errors
5. Navigate to /cart - items should be displayed
6. Try updating quantities - should work smoothly
7. Try removing items - should work smoothly

## Testing Checklist

- [ ] Add item to cart returns success
- [ ] Cart contents endpoint shows added items
- [ ] Cart count is accurate
- [ ] Product details (name, price, image) are correctly displayed
- [ ] Update quantity works and validates stock
- [ ] Remove item works
- [ ] Clear cart works
- [ ] Cart persists across page refreshes (same session)
- [ ] Items have unique IDs for tracking
- [ ] Multiple items with same product work correctly
- [ ] MongoDB documents have correct structure
- [ ] Timestamps use UTCDateTime format
- [ ] Out of stock validation works
- [ ] Guest cart merges into user cart on login
- [ ] No JavaScript console errors

## Troubleshooting

### Cart is empty after adding items

Check:
1. MongoDB connection is working: `php test_mongo.php`
2. Database default is set to 'mongodb' in config
3. Session is persisting (check cookies in browser dev tools)
4. MongoDB contains products with `status: 'published'`
5. Check application logs for errors

### Products not found

Check:
1. Products collection exists in MongoDB
2. Product IDs are valid MongoDB ObjectIds
3. Product `status` field is set to 'published'
4. Products have required fields: name_en, price, stock, images

### Session issues

Check:
1. PHP session is started
2. Session cookie is being set
3. `cart_session_id` exists in session data
4. Session storage is working (file or database)

## Performance Considerations

1. **Indexes**: Create indexes on MongoDB collections:
   ```javascript
   db.carts.createIndex({ "session_id": 1 })
   db.carts.createIndex({ "user_id": 1 })
   db.carts.createIndex({ "updated_at": 1 })
   db.products.createIndex({ "slug": 1 })
   ```

2. **Cleanup**: Run cleanup periodically to remove abandoned carts:
   ```php
   Cart::cleanupAbandoned(30); // Remove carts older than 30 days
   ```

## API Endpoints

- `GET /api/v1/cart` - Get cart contents
- `POST /api/v1/cart/items` - Add item to cart
- `PUT /api/v1/cart/items/{id}` - Update item quantity
- `DELETE /api/v1/cart/items/{id}` - Remove item
- `DELETE /api/v1/cart/clear` - Clear entire cart
- `POST /api/v1/cart/sync` - Sync localStorage cart (for login)
- `GET /api/v1/cart/count` - Get cart count

## Notes

- Cart items are embedded documents in the cart document (not separate collection)
- Each cart item has a unique random ID for tracking operations
- Product IDs are stored as strings (MongoDB ObjectId string representation)
- Timestamps use MongoDB UTCDateTime for consistency
- Cart automatically validates stock before adding/updating items
- Old/abandoned guest carts can be cleaned up automatically
