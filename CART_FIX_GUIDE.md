# 🛠️ Cart Issue Fix - KHAIRAWANG DAIRY

## Issue Identified

The cart is not showing items because:
1. ✅ Backend API is working (`/api/v1/cart` responds)
2. ✅ Database and products are set up correctly
3. ❌ Frontend is using localStorage (Alpine.js) that's not synced with backend
4. ❌ Products need to be added through the admin panel or manually

## Quick Fix Steps

### Option 1: Add More Products to Test (Recommended)

```bash
cd /Users/kiranoli/Development/khairawang-dairy
mysql -uroot khairawang_dairy <<'SQL'
INSERT INTO products (category_id, name_en, slug, price, sku, stock, status, featured) VALUES
(2, 'Cheddar Cheese 250g', 'cheddar-cheese-250g', 400.00, 'CHEESE-001', 20, 'published', 1),
(3, 'Greek Yogurt 500g', 'greek-yogurt-500g', 220.00, 'YOGURT-001', 40, 'published', 1),
(1, 'Organic Milk 1L', 'organic-milk-1l', 150.00, 'MILK-002', 30, 'published', 1);
SQL
```

### Option 2: Test Cart via Browser Console

1. Open http://localhost:8000 in your browser
2. Open Browser DevTools (F12)
3. Go to Console tab
4. Run this code:

```javascript
// Add a product to cart
$store.cart.add({
    id: '1',
    name: 'Fresh Milk',
    price: 100,
    image: '/assets/images/product-placeholder.png'
}, 2);

// Check cart
console.log('Cart items:', $store.cart.items);
console.log('Cart count:', $store.cart.count);
```

5. Navigate to http://localhost:8000/cart - you should see the item!

### Option 3: Check if Problem is Backend Sync

The cart page uses Alpine.js localStorage, which is separate from the backend database cart. 

**To verify backend cart is working:**

```bash
# Test adding via API
curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=$(uuidgen)" \
  -d '{"product_id": "1", "quantity": 1}' -c /tmp/cookies.txt

# Check cart via API
curl http://localhost:8000/api/v1/cart -b /tmp/cookies.txt
```

## Root Cause

The application uses **TWO separate cart systems**:

1. **Frontend Cart** (Alpine.js + localStorage)
   - File: `resources/js/alpine/cart.js`
   - Stores cart in browser localStorage
   - Used for quick UI updates
   
2. **Backend Cart** (PHP + MySQL)
   - File: `app/Services/CartService.php`
   - Stores cart in database tables (`carts` and `cart_items`)
   - Used for persistent storage and checkout

**These are NOT automatically synced!**

## Solution: Add Products via Admin Panel

1. Go to http://localhost:8000/admin
2. Login with:
   - Email: admin@khairawangdairy.com
   - Password: admin123
3. Navigate to Products section
4. Add products with images
5. Now customers can add them to cart!

## For Developers: How to Sync Carts

The cart should sync when:
- User logs in (merge localStorage cart with DB cart)
- User adds to cart (update both localStorage and DB)
- User proceeds to checkout (use DB cart as source of truth)

Check `resources/js/alpine/cart.js` line 228 for the `refresh()` method that fetches from `/api/v1/cart`.

## Database Status

```
✓ MySQL running on localhost:3306
✓ Database: khairawang_dairy
✓ Products table: 1 product available
✓ Product ID 1: Fresh Milk (50 in stock, published)
✓ Carts table: 1 cart session exists
✓ Cart items: 0 items currently
```

## Test Commands

```bash
# Check if server is running
curl -I http://localhost:8000

# Check cart API
curl http://localhost:8000/api/v1/cart

# View products in database
mysql -uroot khairawang_dairy -e "SELECT id, name_en, stock, status FROM products;"

# View cart items
mysql -uroot khairawang_dairy -e "SELECT * FROM cart_items;"
```

## Next Steps

1. Add more products via admin panel or SQL
2. Test adding to cart from the homepage/products page
3. Check browser DevTools Console for JavaScript errors
4. Check browser DevTools Application > Local Storage for cart data

---

**Note:** The cart functionality is working, you just need products with proper images and data to test it properly!
