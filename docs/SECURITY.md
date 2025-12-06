# Security Best Practices

## Overview

This document outlines security best practices implemented in the KHAIRAWANG DAIRY eCommerce platform, with focus on payment security.

## Table of Contents

1. [Payment Security](#payment-security)
2. [Data Protection](#data-protection)
3. [Authentication](#authentication)
4. [Input Validation](#input-validation)
5. [HTTPS/TLS](#httpstls)
6. [Security Headers](#security-headers)
7. [Logging & Monitoring](#logging--monitoring)
8. [Incident Response](#incident-response)

## Payment Security

### eSewa Integration Security

#### 1. Never Store Sensitive Payment Data

```php
// ❌ NEVER DO THIS
$payment_data = [
    'card_number' => $request->input('card'),
    'cvv' => $request->input('cvv')
];

// ✅ CORRECT: Let eSewa handle all payment data
$payment_data = $esewaService->initiatePayment($orderId, $amount);
```

#### 2. Always Verify Payment Callbacks

```php
// ❌ WRONG: Trust callback without verification
if ($request->query('refId')) {
    $order->markAsPaid();
}

// ✅ CORRECT: Always verify with eSewa API
$result = $esewaService->verifyPayment($refId, $orderId, $amount);
if ($result['success']) {
    $order->markAsPaid();
}
```

#### 3. Validate Amount Consistency

```php
// Always verify the amount matches
if (abs($order->total - $callbackAmount) > 0.01) {
    // Log potential tampering attempt
    $this->log('Amount mismatch detected', [
        'order_total' => $order->total,
        'callback_amount' => $callbackAmount,
    ], 'security');
    
    return false;
}
```

#### 4. Use HMAC Signatures

```php
// Generate signature for enhanced security
private function generateSignature(float $totalAmount, string $orderId): string
{
    $message = "total_amount={$totalAmount},transaction_uuid={$orderId},product_code={$this->merchantCode}";
    
    if (!empty($this->secretKey)) {
        return base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));
    }
    
    throw new \RuntimeException('Secret key required for signature');
}
```

#### 5. Implement Rate Limiting

```php
// Limit payment attempts to prevent brute force
class PaymentRateLimiter
{
    public function attempt(string $ip, string $orderId): bool
    {
        $key = "payment_attempt:{$ip}:{$orderId}";
        $attempts = cache()->get($key, 0);
        
        if ($attempts >= 5) {
            return false; // Too many attempts
        }
        
        cache()->put($key, $attempts + 1, 300); // 5 minutes
        return true;
    }
}
```

#### 6. Secure Callback URLs

```php
// Whitelist eSewa IP addresses (if provided)
$allowedIPs = [
    '103.10.28.0/24', // eSewa IPs
    '103.10.29.0/24',
];

if (!$this->isIpWhitelisted($request->ip(), $allowedIPs)) {
    $this->log('Unauthorized callback attempt', [
        'ip' => $request->ip(),
    ], 'security');
    
    return Response::error('Forbidden', 403);
}
```

## Data Protection

### 1. Encrypt Sensitive Data

```php
// Encrypt sensitive order notes
use Core\Encryption;

$order->notes = Encryption::encrypt($request->input('notes'));

// Decrypt when retrieving
$decryptedNotes = Encryption::decrypt($order->notes);
```

### 2. Sanitize User Input

```php
// Always sanitize user input
$name = htmlspecialchars(trim($request->input('name')), ENT_QUOTES, 'UTF-8');
$email = filter_var($request->input('email'), FILTER_SANITIZE_EMAIL);
$phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
```

### 3. Protect Personal Information

```php
// Mask sensitive data in logs
private function maskEmail(string $email): string
{
    $parts = explode('@', $email);
    return substr($parts[0], 0, 2) . '***@' . $parts[1];
}

private function maskPhone(string $phone): string
{
    return substr($phone, 0, 3) . '****' . substr($phone, -2);
}
```

## Authentication

### 1. Strong Password Policy

```php
// Minimum requirements
$requirements = [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_special' => true,
];

function validatePassword(string $password): bool
{
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}
```

### 2. Session Security

```php
// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
```

### 3. CSRF Protection

```php
// Generate CSRF token
function generateCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}
```

## Input Validation

### 1. Email Validation

```php
function validateEmail(string $email): bool
{
    // Basic format check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check for valid domain format
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        return false;
    }
    
    // Additional checks
    $domain = explode('@', $email)[1];
    
    // Check DNS records (optional but recommended)
    if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
        return false;
    }
    
    return true;
}
```

### 2. Phone Validation (Nepal)

```php
function validateNepalPhone(string $phone): bool
{
    // Remove non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check length
    if (strlen($phone) !== 10) {
        return false;
    }
    
    // Nepal mobile numbers start with 98, 97, or 96
    if (!preg_match('/^(98|97|96)[0-9]{8}$/', $phone)) {
        return false;
    }
    
    return true;
}
```

### 3. Amount Validation

```php
function validateAmount(float $amount): bool
{
    // Check for positive amount
    if ($amount <= 0) {
        return false;
    }
    
    // Check for reasonable maximum
    if ($amount > 1000000) { // 10 lakhs
        return false;
    }
    
    // Check decimal places (max 2)
    if (round($amount, 2) !== $amount) {
        return false;
    }
    
    return true;
}
```

## HTTPS/TLS

### 1. Force HTTPS

```php
// Redirect HTTP to HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
        exit;
    }
}
```

### 2. SSL Certificate Verification

```bash
# Check SSL certificate
openssl s_client -connect khairawangdairy.com:443 -showcerts

# Verify certificate expiry
echo | openssl s_client -servername khairawangdairy.com -connect khairawangdairy.com:443 2>/dev/null | openssl x509 -noout -dates
```

## Security Headers

### Required Headers

```php
// Set security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
```

### Content Security Policy

```apache
# Apache configuration
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https://uat.esewa.com.np https://esewa.com.np;"
```

## Logging & Monitoring

### 1. Transaction Logging

```php
// Log all payment transactions
private function log(string $message, array $context = [], string $level = 'info'): void
{
    $logDir = __DIR__ . '/../../storage/logs';
    
    // Create separate log files by type
    $logFile = match($level) {
        'security' => $logDir . '/security-' . date('Y-m-d') . '.log',
        'error' => $logDir . '/error-' . date('Y-m-d') . '.log',
        default => $logDir . '/esewa-' . date('Y-m-d') . '.log',
    };
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
```

### 2. Failed Login Attempts

```php
class LoginAttemptMonitor
{
    public function recordFailedAttempt(string $email, string $ip): void
    {
        $key = "failed_login:{$ip}";
        $attempts = cache()->get($key, 0) + 1;
        
        cache()->put($key, $attempts, 3600); // 1 hour
        
        if ($attempts >= 5) {
            $this->log('Multiple failed login attempts', [
                'email' => $this->maskEmail($email),
                'ip' => $ip,
                'attempts' => $attempts,
            ], 'security');
            
            // Optional: Send alert email to admin
            $this->sendSecurityAlert($email, $ip);
        }
    }
}
```

### 3. Monitor Suspicious Activity

```php
class SecurityMonitor
{
    public function detectSuspiciousOrder(Order $order): bool
    {
        $suspicious = false;
        
        // Check for unusually large orders
        if ($order->total > 50000) {
            $this->log('Large order detected', [
                'order_id' => $order->order_number,
                'amount' => $order->total,
            ], 'security');
            $suspicious = true;
        }
        
        // Check for rapid successive orders
        $recentOrders = Order::where('email', $order->email)
            ->where('created_at', '>', now()->subHours(1))
            ->count();
            
        if ($recentOrders > 5) {
            $this->log('Multiple orders in short time', [
                'email' => $this->maskEmail($order->email),
                'count' => $recentOrders,
            ], 'security');
            $suspicious = true;
        }
        
        return $suspicious;
    }
}
```

## Incident Response

### 1. Security Incident Playbook

```markdown
# Security Incident Response Plan

## Phase 1: Detection & Analysis (0-1 hour)
1. Identify the security incident
2. Determine scope and severity
3. Preserve evidence (logs, database snapshots)
4. Alert security team

## Phase 2: Containment (1-4 hours)
1. Isolate affected systems
2. Block malicious IPs/users
3. Disable compromised accounts
4. Enable maintenance mode if needed

## Phase 3: Eradication (4-24 hours)
1. Remove malware/unauthorized access
2. Patch vulnerabilities
3. Update security rules
4. Reset compromised credentials

## Phase 4: Recovery (24-48 hours)
1. Restore services gradually
2. Monitor for recurring issues
3. Verify system integrity
4. Communicate with stakeholders

## Phase 5: Post-Incident (After)
1. Document incident details
2. Conduct root cause analysis
3. Update security procedures
4. Train team on lessons learned
```

### 2. Contact Information

```
Security Team: security@khairawangdairy.com
On-Call Phone: +977-9800000000
eSewa Support: merchant@esewa.com.np
```

## Security Checklist

### Development
- [ ] Code reviewed for security vulnerabilities
- [ ] Input validation implemented
- [ ] Output encoding applied
- [ ] CSRF protection enabled
- [ ] SQL injection prevention
- [ ] XSS prevention implemented

### Deployment
- [ ] HTTPS/SSL enabled
- [ ] Security headers configured
- [ ] Firewall rules applied
- [ ] File permissions set correctly
- [ ] Sensitive files protected
- [ ] Error messages sanitized

### Payment Security
- [ ] eSewa credentials secured
- [ ] Payment verification implemented
- [ ] Transaction logging enabled
- [ ] Amount validation in place
- [ ] Callback authentication working
- [ ] Rate limiting configured

### Monitoring
- [ ] Log monitoring active
- [ ] Intrusion detection enabled
- [ ] Failed login alerts configured
- [ ] Suspicious activity detection
- [ ] Regular security audits scheduled

---

**Last Updated**: December 2024  
**Review Frequency**: Quarterly  
**Next Review**: March 2025
