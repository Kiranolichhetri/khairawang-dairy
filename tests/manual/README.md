# Manual Tests

This directory contains manual test scripts for verifying functionality that requires external services or manual inspection.

## MongoDB Cart Test

**File**: `mongodb_cart_test.php`

**Purpose**: Tests MongoDB cart operations end-to-end without requiring HTTP requests.

**Usage**:
```bash
php tests/manual/mongodb_cart_test.php
```

**Prerequisites**:
1. MongoDB connection configured in `config/database.php`
2. Database default set to 'mongodb'
3. At least one published product in MongoDB

**What it tests**:
- MongoDB connection
- Cart creation for session
- Adding items to cart
- Retrieving items with product details
- Updating item quantities
- CartService integration
- Removing items from cart
- MongoDB document structure validation

**Expected Output**:
```
╔═══════════════════════════════════════════╗
║   MongoDB Cart Functionality Test         ║
╚═══════════════════════════════════════════╝

=== Configuration Check ===
✓ MongoDB is set as default database

=== MongoDB Connection ===
✓ MongoDB connection successful

... (more tests)

╔═══════════════════════════════════════════╗
║  ✓ ALL TESTS PASSED                       ║
╚═══════════════════════════════════════════╝
```

## Adding More Tests

To add new manual tests:

1. Create a new PHP script in this directory
2. Follow the same structure as existing tests
3. Document the test in this README
4. Make the script executable: `chmod +x your_test.php`
