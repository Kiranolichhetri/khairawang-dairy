# Cart Functionality Fixes - Summary

## Overview
This document summarizes the critical fixes made to resolve MongoDB cart functionality issues where users could not add products to cart and the cart remained empty.

## Issues Identified and Fixed

### 1. MongoCart.php - Missing Collection Parameter (Line 56)
**Issue**: The `findOne()` call in `ensureDocument()` was missing the collection name parameter.

**Location**: `app/Models/MongoCart.php:56`

**Before**:
```php
$result = $this->mongo()->insertOne('carts', $doc);
$this->document = $this->mongo()->findOne(['_id' => $result->getInsertedId()]);
```

**After**:
```php
$insertedId = $this->mongo()->insertOne('carts', $doc);
$this->document = $this->mongo()->findOne('carts', ['_id' => new ObjectId($insertedId)]);
```

**Impact**: This caused the MongoCart initialization to fail, preventing any cart operations from working.

---

### 2. Cart.php - Incomplete Cart Creation in forSession() and forUser()
**Issue**: After inserting a new cart document into MongoDB, the methods returned a hydrated cart with incomplete data (missing timestamps and other MongoDB-generated fields).

**Location**: `app/Models/Cart.php:514-526` and `app/Models/Cart.php:554-566`

**Before**:
```php
// Create new cart
$id = $mongo->insertOne(static::$table, [
    'user_id' => $userId,
    'session_id' => null,
    'items' => [],
]);

return static::hydrate([
    '_id' => $id,
    'id' => $id,
    'user_id' => $userId,
    'session_id' => null,
    'items' => [],
]);
```

**After**:
```php
// Create new cart
$id = $mongo->insertOne(static::$table, [
    'user_id' => $userId,
    'session_id' => null,
    'items' => [],
]);

// Fetch the created cart to get complete data with timestamps
$cart = $mongo->findOne(static::$table, ['_id' => MongoDB::objectId($id)]);

return static::hydrate($cart);
```

**Impact**: Cart operations that depended on complete document structure (especially timestamps) could fail or behave incorrectly.

---

### 3. Cart.php - Item ID Field Inconsistency
**Issue**: Inconsistent use of item ID field names between Cart model and MongoCart model:
- Cart model used 'id' field for cart items
- MongoCart model used 'item_id' field for cart items
- This mismatch caused operations like update and remove to fail

**Locations**:
- `app/Models/Cart.php:237` (addItemMongo)
- `app/Models/Cart.php:40-48` (items)
- `app/Models/Cart.php:120` (itemsWithProductsMongo)
- `app/Models/Cart.php:315` (updateItemQuantityMongo)
- `app/Models/Cart.php:382` (removeItemMongo)

**Changes**:

1. **addItemMongo**: Changed to use 'item_id' field when creating new items
```php
// Before
'id' => bin2hex(random_bytes(12)),

// After
'item_id' => bin2hex(random_bytes(12)),
```

2. **items()**: Added normalization to expose 'item_id' as 'id' for API compatibility
```php
// For MongoDB, items are embedded in the cart document
// Normalize item_id to id for consistency with SQL
$items = $this->attributes['items'] ?? [];
return array_map(function($item) {
    if (isset($item['item_id']) && !isset($item['id'])) {
        $item['id'] = $item['item_id'];
    }
    return $item;
}, $items);
```

3. **itemsWithProductsMongo**: Updated to check 'item_id' first
```php
// Before
'id' => $item['id'] ?? $productId,

// After
'id' => $item['item_id'] ?? $item['id'] ?? $productId,
```

4. **updateItemQuantityMongo**: Updated matching logic
```php
// Before
if (($item['id'] ?? '') === $itemId) {

// After
if (($item['item_id'] ?? $item['id'] ?? '') === $itemId) {
```

5. **removeItemMongo**: Updated matching logic
```php
// Before
return ($item['id'] ?? '') !== $itemId;

// After
return ($item['item_id'] ?? $item['id'] ?? '') !== $itemId;
```

**Impact**: This was the most critical fix - without consistent field naming, cart items could not be properly updated or removed, and the cart would appear empty even after adding items.

---

## MongoDB Cart Item Structure

After the fixes, cart items in MongoDB have the following structure:

```json
{
  "_id": ObjectId("..."),
  "session_id": "test_abc123..." | null,
  "user_id": 123 | null,
  "items": [
    {
      "item_id": "a1b2c3d4e5f6...",
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

**Key Points**:
- Items use 'item_id' field internally in MongoDB
- The 'item_id' is exposed as 'id' in API responses for consistency with SQL implementation
- Both Cart model and MongoCart model now consistently use 'item_id'

---

## Testing

To verify the fixes work correctly:

1. **Manual Testing**: Use the test script at `tests/manual/mongodb_cart_test.php`
   ```bash
   php tests/manual/mongodb_cart_test.php
   ```

2. **API Testing**: Test cart operations through API endpoints
   ```bash
   # Add item to cart
   curl -X POST http://localhost/api/v1/cart/items \
     -H "Content-Type: application/json" \
     -d '{"product_id": "PRODUCT_ID", "quantity": 2}'
   
   # Get cart contents
   curl http://localhost/api/v1/cart
   ```

3. **Browser Testing**: 
   - Navigate to product pages
   - Click "Add to Cart"
   - Verify cart icon updates
   - Navigate to cart page
   - Verify items are displayed
   - Test quantity updates and item removal

---

## Impact Assessment

These fixes resolve the following user-facing issues:

1. ✅ Users can now add products to cart
2. ✅ Cart persists items correctly across page refreshes
3. ✅ Cart item count displays correctly
4. ✅ Users can update item quantities
5. ✅ Users can remove items from cart
6. ✅ Cart data is properly stored in MongoDB
7. ✅ eSewa payment integration can now access cart data correctly

---

## Files Modified

1. `app/Models/Cart.php` - Core cart model with MongoDB support
2. `app/Models/MongoCart.php` - MongoDB-specific cart implementation

---

## Backward Compatibility

All changes maintain backward compatibility:
- SQL-based cart operations remain unchanged
- API responses maintain the same structure (items have 'id' field)
- MongoCart and Cart model work interchangeably through CartService

---

## Related Documentation

- See `MONGODB_CART_VERIFICATION.md` for detailed testing procedures
- See `IMPLEMENTATION_SUMMARY.md` for overall implementation details
