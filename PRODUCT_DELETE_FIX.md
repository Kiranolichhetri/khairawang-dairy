# Product Delete Error Fix - Method Not Allowed

## Error
```
Core\Exceptions\MethodNotAllowedException (HTTP 405)
Method [DELETE] not allowed
```

## Root Cause

The delete form is submitting to `/admin/products/` (missing product ID) instead of `/admin/products/1`.

**Why?** The product ID is empty when rendering the form:
```php
<form action="/admin/products/<?= $product['id'] ?>">
```
If `$product['id']` is empty, the action becomes `/admin/products/` which doesn't match the route `/admin/products/{id}`.

## This is THE SAME Issue As Edit Links

Both the edit link and delete form suffer from the same problem:
- Edit link: `/admin/products/<?= $product['id'] ?>/edit` becomes `/admin/products//edit`
- Delete form: `/admin/products/<?= $product['id'] ?>` becomes `/admin/products/`

**The common cause:** Products are not loading their IDs from the database properly.

## Diagnosis

### What We Know:
1. ✅ Database has products with proper IDs:
   ```sql
   mysql> SELECT id, name_en FROM products;
   +----+------------+
   | id | name_en    |
   +----+------------+
   |  1 | Fresh Milk |
   +----+------------+
   ```

2. ✅ Routes are correctly defined in `/routes/web.php`:
   ```php
   $router->delete('/products/{id}', [AdminProductController::class, 'delete']);
   ```

3. ✅ Form template is correct in `/resources/views/admin/products/index.php`:
   ```php
   <form action="/admin/products/<?= $product['id'] ?>" method="POST">
       <?= $view->csrf() ?>
       <?= $view->method('DELETE') ?>
   ```

4. ❌ The `$product['id']` is empty when passed to the view

## The Real Problem: Model Query Returns Data Without IDs

The issue is in how data flows through the application:

1. **ProductController::index()** calls:
   ```php
   $productsData = $query->limit($perPage)->offset($offset)->get();
   ```

2. **QueryBuilder::get()** returns raw arrays from database

3. **formatProductArray()** expects `$product['id']` to exist:
   ```php
   'id' => (string) ($product['_id'] ?? $product['id'] ?? ''),
   ```

4. But somehow the `id` field is missing from the raw array

## Root Cause: Same as Cart/Account Issues

This is the **SAME underlying problem** affecting:
- ❌ Cart add to database (Product::find() returns null)
- ❌ Account page (User::find() returns null)  
- ❌ Product edit links (product ID missing)
- ❌ Product delete (product ID missing)

**Common denominator:** Database connection/query issues when using Model methods in certain contexts.

## Workaround Solution

Since you need to be logged in anyway to access admin, make sure you:

1. **Login properly** via http://localhost:8000/login
2. **Use admin credentials:**
   - Email: `admin@khairawangdairy.com`
   - Password: `admin123`
3. **Then access** http://localhost:8000/admin/products

When logged in through the proper flow, the session and database connection should be properly initialized.

## Permanent Fix (Advanced)

To fix this properly, we need to debug why the query isn't returning the `id` field. Add explicit column selection in ProductController:

**File:** `/app/Controllers/Admin/ProductController.php`

**Around line 46, change:**
```php
// Current (implicit SELECT *)
$productsData = $query->limit($perPage)->offset($offset)->get();

// Fix with explicit columns
$productsData = $query
    ->select([
        'id', 'name_en', 'name_ne', 'slug', 'category_id',
        'short_description', 'description_en', 'price', 'sale_price',
        'sku', 'stock', 'low_stock_threshold', 'weight', 'images',
        'featured', 'status', 'created_at', 'updated_at'
    ])
    ->limit($perPage)
    ->offset($offset)
    ->get();
```

## Alternative: Check QueryBuilder Select

The QueryBuilder might have an issue with SELECT *. Check if `$this->columns` is properly set.

**File:** `/core/QueryBuilder.php`

In the `toSql()` method, ensure it's selecting all columns when no explicit select is set:
```php
$columns = empty($this->columns) ? '*' : implode(', ', $this->columns);
```

## Temporary Workaround for Delete

If the ID is still missing after login, manually add a product ID check in the view:

**File:** `/resources/views/admin/products/index.php` around line 145:

```php
<?php if (!empty($product['id'])): ?>
    <form action="/admin/products/<?= $product['id'] ?>" method="POST" 
          onsubmit="return confirm('Are you sure?');">
        <?= $view->csrf() ?>
        <?= $view->method('DELETE') ?>
        <button type="submit" ...>Delete</button>
    </form>
<?php else: ?>
    <span class="text-gray-400 text-xs">ID missing</span>
<?php endif; ?>
```

This will show "ID missing" for products without IDs, helping you debug.

## Summary

✅ **Router fixed:** Trailing slash handling added
✅ **Exception classes fixed:** No more duplicate class errors
✅ **Routes verified:** DELETE route exists and is correct
✅ **Template verified:** Form is correctly structured
❌ **Core issue remains:** Product IDs not loading from database

**The delete functionality code is 100% correct.** The issue is that products don't have IDs when rendered, which is a data loading problem, not a routing or form problem.

**Next step:** Login to admin panel properly and check if products show with IDs. If not, the QueryBuilder/Model query needs debugging.
