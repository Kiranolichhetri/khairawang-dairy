# Payment API Documentation

## Overview

This document provides detailed information about the payment API endpoints in the KHAIRAWANG DAIRY eCommerce platform.

## Base URL

```
Production: https://khairawangdairy.com
Development: http://localhost:8000
```

## Authentication

Most payment endpoints require authentication via session cookies. Admin endpoints require admin role.

## API Endpoints

### Checkout API

#### Get Checkout Data

Retrieves cart contents and available payment methods for checkout.

```http
GET /api/v1/checkout
```

**Response:**
```json
{
  "success": true,
  "data": {
    "cart": {
      "items": [
        {
          "id": "item_123",
          "name": "Fresh Milk 1L",
          "slug": "fresh-milk-1l",
          "quantity": 2,
          "price": 150.00,
          "total": 300.00,
          "image": "/uploads/products/milk.jpg"
        }
      ],
      "subtotal": 1000.00,
      "shipping": 50.00,
      "total": 1050.00,
      "free_shipping": false,
      "free_shipping_threshold": 2000.00
    },
    "user": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "9841234567"
    },
    "payment_methods": [
      {
        "key": "esewa",
        "name": "eSewa",
        "icon": "esewa.png"
      },
      {
        "key": "cod",
        "name": "Cash on Delivery",
        "icon": "cod.png"
      }
    ],
    "shipping": {
      "cost": 50.00,
      "free_threshold": 2000.00,
      "is_free": false
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Cart is empty"
}
```

---

#### Process Checkout

Creates an order and initiates payment.

```http
POST /api/v1/checkout
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "9841234567",
  "address": "Thamel, Kathmandu",
  "city": "Kathmandu",
  "notes": "Please deliver in the morning",
  "payment_method": "esewa"
}
```

**Validation Rules:**
- `name`: Required, string, max 255 characters
- `email`: Required, valid email format
- `phone`: Required, 10-15 digits (Nepal format: 98XXXXXXXX)
- `address`: Required, string
- `city`: Optional, string
- `notes`: Optional, string
- `payment_method`: Required, one of: esewa, cod

**Response (eSewa):**
```json
{
  "success": true,
  "redirect": true,
  "method": "esewa",
  "payment_url": "https://uat.esewa.com.np/epay/main",
  "params": {
    "amt": "1000.00",
    "txAmt": "0.00",
    "psc": "0.00",
    "pdc": "50.00",
    "tAmt": "1050.00",
    "pid": "ORD-20240115-12345",
    "scd": "EPAYTEST",
    "su": "http://localhost/payment/esewa/success?oid=ORD-20240115-12345",
    "fu": "http://localhost/payment/esewa/failure?oid=ORD-20240115-12345"
  },
  "order": {
    "id": "507f1f77bcf86cd799439011",
    "order_number": "ORD-20240115-12345",
    "total": 1050.00
  }
}
```

**Response (COD):**
```json
{
  "success": true,
  "redirect": false,
  "method": "cod",
  "redirect_url": "/checkout/success/ORD-20240115-12345",
  "order": {
    "id": "507f1f77bcf86cd799439011",
    "order_number": "ORD-20240115-12345",
    "total": 1050.00
  },
  "message": "Order placed successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Invalid email format"],
    "phone": ["Phone number must be 10-15 digits"]
  }
}
```

---

#### Validate Stock

Validates that cart items are in stock before checkout.

```http
GET /api/v1/checkout/validate
```

**Response:**
```json
{
  "success": true,
  "valid": true,
  "message": "All items are in stock"
}
```

**Error Response:**
```json
{
  "success": true,
  "valid": false,
  "message": "Some items are out of stock",
  "errors": {
    "item_123": "Only 5 items available, you have 10 in cart"
  }
}
```

---

### eSewa Payment Endpoints

#### Initiate Payment

Generates payment form data for eSewa.

```http
POST /payment/esewa/initiate
Content-Type: application/json
```

**Request Body:**
```json
{
  "order_id": "ORD-20240115-12345",
  "amount": 1000.00,
  "shipping": 50.00
}
```

**Response:**
```json
{
  "success": true,
  "payment_url": "https://uat.esewa.com.np/epay/main",
  "params": {
    "amt": "1000.00",
    "txAmt": "0.00",
    "psc": "0.00",
    "pdc": "50.00",
    "tAmt": "1050.00",
    "pid": "ORD-20240115-12345",
    "scd": "EPAYTEST",
    "su": "http://localhost/payment/esewa/success?oid=ORD-20240115-12345",
    "fu": "http://localhost/payment/esewa/failure?oid=ORD-20240115-12345"
  },
  "signature": "base64_encoded_signature"
}
```

---

#### Success Callback

Handles successful payment callback from eSewa.

```http
GET /payment/esewa/success?refId=ESEWA-123&oid=ORD-20240115-12345&amt=1050.00
```

**Query Parameters:**
- `refId`: eSewa transaction reference ID
- `oid`: Order number
- `amt`: Total amount

**Response:**
- Redirects to: `/checkout/success/{orderNumber}` on success
- Redirects to: `/checkout/failed?error={message}` on failure

---

#### Failure Callback

Handles failed payment callback from eSewa.

```http
GET /payment/esewa/failure?oid=ORD-20240115-12345
```

**Query Parameters:**
- `oid`: Order number

**Response:**
- Redirects to: `/checkout/failed?order={orderNumber}&error=Payment failed`

---

#### Verify Payment

Verifies a payment transaction manually.

```http
POST /payment/esewa/verify
Content-Type: application/json
```

**Request Body:**
```json
{
  "reference_id": "ESEWA-123",
  "order_id": "ORD-20240115-12345",
  "amount": 1050.00
}
```

**Response:**
```json
{
  "success": true,
  "verified": true,
  "transaction_id": "ESEWA-123"
}
```

**Error Response:**
```json
{
  "success": false,
  "verified": false,
  "message": "Payment verification failed"
}
```

---

### Order Management

#### Get Order Details

Retrieves order details by order number.

```http
GET /checkout/success/{orderNumber}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order": {
      "id": "507f1f77bcf86cd799439011",
      "order_number": "ORD-20240115-12345",
      "status": "pending",
      "status_label": "Pending",
      "payment_status": "paid",
      "payment_status_label": "Paid",
      "payment_method": "esewa",
      "subtotal": 1000.00,
      "shipping_cost": 50.00,
      "discount": 0.00,
      "total": 1050.00,
      "shipping_name": "John Doe",
      "shipping_email": "john@example.com",
      "shipping_phone": "9841234567",
      "shipping_address": "Thamel, Kathmandu",
      "shipping_city": "Kathmandu",
      "notes": "Please deliver in the morning",
      "created_at": "2024-01-15T10:30:00Z"
    },
    "items": [
      {
        "product_name": "Fresh Milk 1L",
        "variant_name": "1 Liter",
        "quantity": 2,
        "price": 150.00,
        "total": 300.00,
        "slug": "fresh-milk-1l",
        "image": "/uploads/products/milk.jpg"
      }
    ]
  }
}
```

---

## Payment Flow

### eSewa Payment Flow

1. **Customer submits checkout**
   ```
   POST /api/v1/checkout
   ```

2. **System creates order and returns payment data**
   ```json
   {
     "payment_url": "...",
     "params": {...}
   }
   ```

3. **Frontend auto-submits form to eSewa**
   ```html
   <form action="{payment_url}" method="POST">
     <input name="amt" value="1000.00">
     <!-- ... other params -->
   </form>
   ```

4. **Customer completes payment on eSewa**

5. **eSewa redirects to success/failure URL**
   ```
   GET /payment/esewa/success?refId=xxx&oid=xxx&amt=xxx
   ```

6. **System verifies payment with eSewa API**

7. **System updates order status and redirects customer**
   ```
   Redirect to /checkout/success/{orderNumber}
   ```

### COD Payment Flow

1. **Customer submits checkout with COD**
   ```
   POST /api/v1/checkout
   ```

2. **System creates order and confirms immediately**
   ```json
   {
     "redirect_url": "/checkout/success/ORD-123"
   }
   ```

3. **Customer sees confirmation page**

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 400 | Cart is empty | No items in cart for checkout |
| 400 | Validation failed | Invalid input data |
| 400 | Out of stock | One or more items out of stock |
| 400 | Invalid payment method | Unsupported payment method |
| 404 | Order not found | Order doesn't exist |
| 403 | Unauthorized | User doesn't own this order |
| 500 | Internal server error | Server error occurred |

---

## Rate Limiting

Payment endpoints are rate limited to prevent abuse:

- **Checkout**: 10 requests per minute
- **Payment verification**: 5 requests per minute
- **Order retrieval**: 30 requests per minute

When rate limit is exceeded:
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

---

## Webhooks (Future Implementation)

Webhook endpoints for asynchronous payment notifications.

### eSewa Webhook

```http
POST /api/webhooks/esewa
Content-Type: application/json
```

**Payload:**
```json
{
  "event": "payment.success",
  "reference_id": "ESEWA-123",
  "order_id": "ORD-20240115-12345",
  "amount": 1050.00,
  "timestamp": "2024-01-15T10:35:00Z"
}
```

---

## Testing

### Sandbox Environment

Use the following test credentials for eSewa sandbox:

```
Merchant Code: EPAYTEST
Payment URL: https://uat.esewa.com.np/epay/main
eSewa ID: 9800000000
Password: Any password
MPIN: 1234
```

### Example cURL Requests

#### Process Checkout
```bash
curl -X POST http://localhost:8000/api/v1/checkout \
  -H "Content-Type: application/json" \
  -b "session_cookie=..." \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "9841234567",
    "address": "Thamel, Kathmandu",
    "city": "Kathmandu",
    "payment_method": "esewa"
  }'
```

#### Verify Payment
```bash
curl -X POST http://localhost:8000/payment/esewa/verify \
  -H "Content-Type: application/json" \
  -d '{
    "reference_id": "ESEWA-123",
    "order_id": "ORD-20240115-12345",
    "amount": 1050.00
  }'
```

---

## Support

For API support and questions:
- **Email**: dev@khairawangdairy.com
- **Documentation**: See `docs/ESEWA_INTEGRATION.md`
- **Issues**: GitHub Issues

---

**Last Updated**: December 2024  
**API Version**: 1.0.0
