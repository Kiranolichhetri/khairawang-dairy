# eSewa Payment Gateway Integration Guide

## Overview

This document provides comprehensive information about the eSewa payment gateway integration in KHAIRAWANG DAIRY eCommerce platform. eSewa is Nepal's leading digital wallet and payment gateway.

## Table of Contents

1. [Features](#features)
2. [Configuration](#configuration)
3. [Sandbox Testing](#sandbox-testing)
4. [Production Setup](#production-setup)
5. [Payment Flow](#payment-flow)
6. [API Reference](#api-reference)
7. [Error Handling](#error-handling)
8. [Security](#security)
9. [Troubleshooting](#troubleshooting)

## Features

### Implemented Features

- ✅ Secure payment initiation with form auto-submission
- ✅ Payment verification with retry mechanism (up to 3 attempts)
- ✅ Transaction logging for audit trail
- ✅ Sandbox and production mode support
- ✅ Error handling with detailed error messages
- ✅ Success and failure callback handling
- ✅ Order status synchronization
- ✅ HMAC signature validation (for enhanced security)

### Payment Methods Supported

- **eSewa Wallet**: Digital wallet payments
- **Cash on Delivery (COD)**: Alternative payment method

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```bash
# eSewa Configuration
ESEWA_MERCHANT_CODE=EPAYTEST                    # Merchant code (EPAYTEST for sandbox)
ESEWA_SECRET_KEY=8gBm/:&EnhH.1/q               # Secret key for signature generation
ESEWA_SUCCESS_URL=/payment/esewa/success       # Success callback URL
ESEWA_FAILURE_URL=/payment/esewa/failure       # Failure callback URL
ESEWA_LOG_TRANSACTIONS=true                    # Enable transaction logging
PAYMENT_TEST_MODE=true                         # Enable sandbox mode
```

### Configuration File

The payment configuration is located at `config/payment.php`. Key settings include:

- `merchant_code`: Your eSewa merchant identifier
- `secret_key`: Secret key for HMAC signature (required for production)
- `urls`: Payment and verification API endpoints
- `timeout`: Transaction timeout in seconds (default: 30)
- `max_verify_attempts`: Maximum verification retry attempts (default: 3)
- `log_transactions`: Enable/disable transaction logging

## Sandbox Testing

### Sandbox Credentials

For testing, use the following sandbox credentials:

```
Merchant Code: EPAYTEST
Secret Key: 8gBm/:&EnhH.1/q
Payment URL: https://uat.esewa.com.np/epay/main
Verify URL: https://uat.esewa.com.np/epay/transrec
```

### Test Payment

1. Set `PAYMENT_TEST_MODE=true` in `.env`
2. Use `EPAYTEST` as merchant code
3. Navigate to checkout and select eSewa as payment method
4. Complete the order to be redirected to eSewa sandbox
5. Use test credentials to complete payment:
   - **eSewa ID**: Any valid format (e.g., 9800000000)
   - **Password**: Any password
   - **MPIN**: 1234

### Testing Scenarios

#### Successful Payment
1. Proceed to checkout
2. Select eSewa payment method
3. Complete payment on eSewa sandbox
4. Verify order status changes to "Processing"
5. Check payment status is "Paid"

#### Failed Payment
1. Proceed to checkout
2. Select eSewa payment method
3. Cancel payment on eSewa page
4. Verify order status remains "Pending"
5. Check payment status is "Failed"

## Production Setup

### Steps to Go Live

1. **Register with eSewa**
   - Visit [https://esewa.com.np/merchant](https://esewa.com.np/merchant)
   - Complete merchant registration
   - Submit required documents

2. **Obtain Credentials**
   - Receive merchant code from eSewa
   - Receive secret key for signature generation
   - Note production API endpoints

3. **Update Configuration**
   ```bash
   PAYMENT_TEST_MODE=false
   ESEWA_MERCHANT_CODE=your_merchant_code
   ESEWA_SECRET_KEY=your_secret_key
   ```

4. **Test in Production**
   - Perform test transactions with small amounts
   - Verify callbacks are working correctly
   - Monitor logs for any issues

5. **Go Live**
   - Enable eSewa payment method for customers
   - Monitor transactions regularly
   - Set up alerts for failed payments

### Production Checklist

- [ ] Merchant account approved by eSewa
- [ ] Production merchant code configured
- [ ] Secret key configured and secured
- [ ] Test mode disabled (`PAYMENT_TEST_MODE=false`)
- [ ] SSL certificate installed (HTTPS)
- [ ] Callback URLs accessible from internet
- [ ] Transaction logging enabled
- [ ] Error monitoring in place
- [ ] Test transactions completed successfully

## Payment Flow

### Customer Journey

```
1. Customer adds products to cart
2. Customer proceeds to checkout
3. Customer fills shipping information
4. Customer selects eSewa payment method
5. Customer submits order
   ↓
6. System creates order (status: Pending)
7. System initiates eSewa payment
8. Customer redirected to eSewa
   ↓
9. Customer completes payment on eSewa
10. eSewa redirects to success/failure URL
    ↓
11. System verifies payment with eSewa API
12. System updates order status
13. Customer sees confirmation page
```

### Technical Flow

```php
// 1. Payment Initiation
$esewaService = new EsewaService();
$paymentData = $esewaService->initiatePayment(
    orderId: $orderNumber,
    amount: $subtotal,
    deliveryCharge: $shippingCost
);

// 2. Form Submission (Auto-submit to eSewa)
// Frontend submits form with payment parameters

// 3. Success Callback
// eSewa redirects to: /payment/esewa/success?refId=xxx&oid=xxx&amt=xxx

// 4. Verification
$result = $esewaService->verifyPayment(
    referenceId: $refId,
    orderId: $orderId,
    amount: $amount
);

// 5. Order Update
if ($result['success']) {
    $order->updatePaymentStatus(PaymentStatus::PAID);
    $order->updateStatus(OrderStatus::PROCESSING);
}
```

## API Reference

### EsewaService Methods

#### `initiatePayment()`

Initiates a payment request with eSewa.

```php
public function initiatePayment(
    string $orderId,
    float $amount,
    float $taxAmount = 0,
    float $serviceCharge = 0,
    float $deliveryCharge = 0,
    string $productName = 'KHAIRAWANG DAIRY Order'
): array
```

**Parameters:**
- `$orderId`: Unique order identifier
- `$amount`: Product amount (excluding other charges)
- `$taxAmount`: Tax amount (optional)
- `$serviceCharge`: Service charge (optional)
- `$deliveryCharge`: Delivery/shipping charge
- `$productName`: Product description

**Returns:**
```php
[
    'payment_url' => 'https://uat.esewa.com.np/epay/main',
    'params' => [
        'amt' => '1000.00',
        'txAmt' => '0.00',
        'psc' => '0.00',
        'pdc' => '50.00',
        'tAmt' => '1050.00',
        'pid' => 'ORD-12345',
        'scd' => 'EPAYTEST',
        'su' => 'http://localhost/payment/esewa/success?oid=ORD-12345',
        'fu' => 'http://localhost/payment/esewa/failure?oid=ORD-12345'
    ],
    'signature' => 'base64_encoded_signature',
    'merchant_code' => 'EPAYTEST',
    'test_mode' => true
]
```

#### `verifyPayment()`

Verifies a payment transaction with eSewa.

```php
public function verifyPayment(
    string $referenceId,
    string $orderId,
    float $amount
): array
```

**Parameters:**
- `$referenceId`: eSewa transaction reference ID
- `$orderId`: Order identifier
- `$amount`: Transaction amount

**Returns:**
```php
[
    'success' => true,
    'message' => 'Payment verified successfully',
    'transaction_id' => 'ESEWA-REF-123',
    'order_id' => 'ORD-12345',
    'amount' => 1050.00
]
```

#### `processSuccess()`

Processes success callback from eSewa.

```php
public function processSuccess(array $params): array
```

**Parameters:**
```php
$params = [
    'refId' => 'ESEWA-REF-123',  // eSewa reference ID
    'oid' => 'ORD-12345',         // Order ID
    'amt' => '1050.00'            // Amount
];
```

#### `processFailure()`

Processes failure callback from eSewa.

```php
public function processFailure(array $params): array
```

### REST API Endpoints

#### Checkout Endpoints

```
GET  /api/v1/checkout              - Get checkout data
POST /api/v1/checkout              - Process checkout
GET  /api/v1/checkout/validate     - Validate stock
```

#### Payment Endpoints

```
POST /payment/esewa/initiate       - Initiate payment
GET  /payment/esewa/success        - Success callback
GET  /payment/esewa/failure        - Failure callback
POST /payment/esewa/verify         - Verify payment
```

## Error Handling

### Common Errors

#### 1. Invalid Secret Key
```
Error: eSewa secret key is required for production mode
Solution: Set ESEWA_SECRET_KEY in .env file
```

#### 2. Connection Timeout
```
Error: Connection error: Operation timed out
Solution: Check internet connectivity and eSewa service status
```

#### 3. Verification Failed
```
Error: Payment verification failed
Solution: Check order ID, reference ID, and amount are correct
```

#### 4. Invalid Callback Parameters
```
Error: Invalid callback parameters
Solution: Ensure eSewa callback URLs are correctly configured
```

### Error Response Format

```php
[
    'success' => false,
    'message' => 'Error description',
    'error_code' => 'ERROR_CODE',  // Optional
    'details' => []                 // Optional
]
```

## Security

### Best Practices

1. **Use HTTPS**: Always use SSL/TLS in production
2. **Validate Callbacks**: Always verify payments with eSewa API
3. **Secret Key**: Keep secret key secure, never expose in frontend
4. **Amount Validation**: Verify amounts match on callback
5. **Order Status**: Check order status before processing payment
6. **Rate Limiting**: Implement rate limiting on payment endpoints
7. **CSRF Protection**: Enable CSRF tokens for payment forms
8. **Logging**: Enable transaction logging for audit trail

### HMAC Signature Validation

For enhanced security, implement HMAC signature validation:

```php
private function generateSignature(float $totalAmount, string $orderId): string
{
    $message = "total_amount={$totalAmount},transaction_uuid={$orderId},product_code={$this->merchantCode}";
    
    if (!empty($this->secretKey)) {
        return base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));
    }
    
    return '';
}
```

## Troubleshooting

### Payment Not Redirecting to eSewa

**Symptoms**: Form submits but nothing happens

**Solutions**:
1. Check JavaScript console for errors
2. Verify payment URL is correct
3. Ensure form has all required parameters
4. Check browser popup blocker

### Payment Success but Order Not Updated

**Symptoms**: Payment completed on eSewa but order status unchanged

**Solutions**:
1. Check callback URLs are accessible
2. Verify verification API is responding
3. Check logs for errors: `storage/logs/esewa-YYYY-MM-DD.log`
4. Verify order ID matches between systems

### Verification Always Failing

**Symptoms**: All verification attempts fail

**Solutions**:
1. Check eSewa service status
2. Verify merchant code is correct
3. Check amount format (must be decimal with 2 places)
4. Ensure reference ID is valid
5. Check network connectivity to eSewa servers

### Transaction Logs

Logs are stored in: `storage/logs/esewa-YYYY-MM-DD.log`

Example log entry:
```
[2024-01-15 10:30:45] [info] Payment initiated {"order_id":"ORD-12345","amount":1000,"total_amount":1050,"test_mode":true}
[2024-01-15 10:31:20] [info] Payment verified successfully {"order_id":"ORD-12345","reference_id":"ESEWA-123","amount":1050,"attempts":1}
```

## Support

### eSewa Support

- **Website**: [https://esewa.com.np](https://esewa.com.np)
- **Merchant Support**: [https://esewa.com.np/merchant](https://esewa.com.np/merchant)
- **Email**: merchant@esewa.com.np
- **Phone**: +977-1-5970047

### Developer Resources

- **API Documentation**: [https://developer.esewa.com.np](https://developer.esewa.com.np)
- **Integration Guide**: [https://developer.esewa.com.np/pages/Esewa-Payment-Integration](https://developer.esewa.com.np/pages/Esewa-Payment-Integration)

## Changelog

### Version 1.0.0 (Current)

- Initial eSewa integration
- Sandbox and production support
- Payment verification with retry
- Transaction logging
- Error handling and recovery
- Comprehensive documentation

---

**Last Updated**: December 2024  
**Version**: 1.0.0  
**Maintained By**: KHAIRAWANG DAIRY Development Team
