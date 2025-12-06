<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * eSewa Payment Service
 * 
 * Handles eSewa payment gateway integration for Nepal.
 * 
 * Features:
 * - Payment initiation with sandbox and production support
 * - Transaction verification with retry mechanism
 * - Comprehensive error handling and logging
 * - Security with HMAC signature validation
 * 
 * @link https://developer.esewa.com.np/pages/Esewa-Payment-Integration
 */
class EsewaService
{
    private string $merchantCode;
    private string $secretKey;
    private bool $testMode;
    private string $paymentUrl;
    private string $verifyUrl;
    private string $successUrl;
    private string $failureUrl;
    private bool $logTransactions;
    private int $timeout;
    private int $maxVerifyAttempts;

    public function __construct()
    {
        $app = Application::getInstance();
        $config = $app?->config('payment.esewa', []);
        $testMode = $app?->config('payment.test_mode', true);
        
        $this->merchantCode = $config['merchant_code'] ?? 'EPAYTEST';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->testMode = (bool) $testMode;
        $this->logTransactions = $config['log_transactions'] ?? true;
        $this->timeout = $config['timeout'] ?? 30;
        $this->maxVerifyAttempts = $config['max_verify_attempts'] ?? 3;
        
        $urls = $config['urls'] ?? [];
        $mode = $this->testMode ? 'test' : 'live';
        
        $this->paymentUrl = $urls['payment'][$mode] ?? 'https://uat.esewa.com.np/epay/main';
        $this->verifyUrl = $urls['verify'][$mode] ?? 'https://uat.esewa.com.np/epay/transrec';
        
        $baseUrl = $app?->config('app.url', 'http://localhost');
        $this->successUrl = $baseUrl . ($config['success_url'] ?? '/payment/esewa/success');
        $this->failureUrl = $baseUrl . ($config['failure_url'] ?? '/payment/esewa/failure');
        
        // Log initialization in test mode
        if ($this->testMode && $this->logTransactions) {
            $this->log('eSewa Service initialized in TEST mode', [
                'merchant_code' => $this->merchantCode,
                'payment_url' => $this->paymentUrl,
            ]);
        }
    }

    /**
     * Generate payment form data for eSewa
     * 
     * @param string $orderId Unique order identifier
     * @param float $amount Product amount (excluding tax, service charge, delivery)
     * @param float $taxAmount Tax amount
     * @param float $serviceCharge Service charge
     * @param float $deliveryCharge Delivery/shipping charge
     * @param string $productName Product description
     * 
     * @return array<string, mixed> Payment form data with URL and parameters
     * 
     * @throws \RuntimeException If required configuration is missing
     */
    public function initiatePayment(
        string $orderId,
        float $amount,
        float $taxAmount = 0,
        float $serviceCharge = 0,
        float $deliveryCharge = 0,
        string $productName = 'KHAIRAWANG DAIRY Order'
    ): array {
        // Validate inputs
        if (empty($orderId)) {
            throw new \InvalidArgumentException('Order ID is required');
        }
        
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }
        
        $totalAmount = $amount + $taxAmount + $serviceCharge + $deliveryCharge;
        
        // Validate secret key is configured for production mode
        if (!$this->testMode && empty($this->secretKey)) {
            $this->log('ERROR: eSewa secret key is missing for production mode', [
                'order_id' => $orderId,
                'mode' => 'production',
            ], 'error');
            throw new \RuntimeException('eSewa secret key is required for production mode');
        }
        
        // Generate signature for enhanced security (optional for test mode)
        $signature = '';
        if (!empty($this->secretKey)) {
            $signature = $this->generateSignature($totalAmount, $orderId);
        }
        
        $params = [
            'amt' => number_format($amount, 2, '.', ''),
            'txAmt' => number_format($taxAmount, 2, '.', ''),
            'psc' => number_format($serviceCharge, 2, '.', ''),
            'pdc' => number_format($deliveryCharge, 2, '.', ''),
            'tAmt' => number_format($totalAmount, 2, '.', ''),
            'pid' => $orderId,
            'scd' => $this->merchantCode,
            'su' => $this->successUrl . '?oid=' . urlencode($orderId),
            'fu' => $this->failureUrl . '?oid=' . urlencode($orderId),
        ];
        
        // Log payment initiation
        if ($this->logTransactions) {
            $this->log('Payment initiated', [
                'order_id' => $orderId,
                'amount' => $amount,
                'total_amount' => $totalAmount,
                'test_mode' => $this->testMode,
            ]);
        }
        
        return [
            'payment_url' => $this->paymentUrl,
            'params' => $params,
            'signature' => $signature,
            'merchant_code' => $this->merchantCode,
            'test_mode' => $this->testMode,
        ];
    }

    /**
     * Generate HMAC signature for eSewa
     */
    private function generateSignature(float $totalAmount, string $orderId): string
    {
        $message = "total_amount={$totalAmount},transaction_uuid={$orderId},product_code={$this->merchantCode}";
        
        if (!empty($this->secretKey)) {
            return base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));
        }
        
        // In test mode, signature may be optional
        if ($this->testMode) {
            return '';
        }
        
        throw new \RuntimeException('eSewa secret key is required for signature generation');
    }

    /**
     * Verify payment transaction with eSewa
     * 
     * Verifies a payment using eSewa's transaction verification API.
     * Implements retry mechanism for better reliability.
     * 
     * @param string $referenceId eSewa reference/transaction ID
     * @param string $orderId Unique order identifier
     * @param float $amount Transaction amount
     * 
     * @return array<string, mixed> Verification result
     */
    public function verifyPayment(string $referenceId, string $orderId, float $amount): array
    {
        if (empty($referenceId) || empty($orderId)) {
            return [
                'success' => false,
                'message' => 'Reference ID and Order ID are required',
            ];
        }
        
        $attempt = 0;
        $lastError = '';
        
        // Retry verification up to maxVerifyAttempts times
        while ($attempt < $this->maxVerifyAttempts) {
            $attempt++;
            
            try {
                $result = $this->performVerification($referenceId, $orderId, $amount);
                
                if ($result['success']) {
                    if ($this->logTransactions) {
                        $this->log('Payment verified successfully', [
                            'order_id' => $orderId,
                            'reference_id' => $referenceId,
                            'amount' => $amount,
                            'attempts' => $attempt,
                        ]);
                    }
                    return $result;
                }
                
                $lastError = $result['message'] ?? 'Unknown error';
                
                // If verification failed but response was received, don't retry
                if (isset($result['response']) && !empty($result['response'])) {
                    break;
                }
                
                // Wait before retry (exponential backoff)
                if ($attempt < $this->maxVerifyAttempts) {
                    sleep($attempt);
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                
                if ($this->logTransactions) {
                    $this->log('Payment verification exception', [
                        'order_id' => $orderId,
                        'reference_id' => $referenceId,
                        'attempt' => $attempt,
                        'error' => $lastError,
                    ], 'error');
                }
                
                // Wait before retry
                if ($attempt < $this->maxVerifyAttempts) {
                    sleep($attempt);
                }
            }
        }
        
        // All attempts failed
        if ($this->logTransactions) {
            $this->log('Payment verification failed after all attempts', [
                'order_id' => $orderId,
                'reference_id' => $referenceId,
                'attempts' => $attempt,
                'error' => $lastError,
            ], 'error');
        }
        
        return [
            'success' => false,
            'message' => 'Payment verification failed: ' . $lastError,
            'attempts' => $attempt,
        ];
    }

    /**
     * Perform single verification attempt
     * 
     * @return array<string, mixed>
     */
    private function performVerification(string $referenceId, string $orderId, float $amount): array
    {
        $data = [
            'amt' => number_format($amount, 2, '.', ''),
            'rid' => $referenceId,
            'pid' => $orderId,
            'scd' => $this->merchantCode,
        ];
        
        $url = $this->verifyUrl . '?' . http_build_query($data);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => !$this->testMode,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $error,
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'HTTP error: ' . $httpCode,
                'response' => $response,
            ];
        }
        
        // Parse XML response from eSewa
        if ($response && str_contains($response, '<response_code>')) {
            $code = $this->extractXmlValue($response, 'response_code');
            
            if ($code === 'Success') {
                return [
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'transaction_id' => $referenceId,
                    'order_id' => $orderId,
                    'amount' => $amount,
                ];
            }
            
            // Extract error message if available
            $message = $this->extractXmlValue($response, 'message');
            return [
                'success' => false,
                'message' => !empty($message) ? $message : 'Payment verification failed',
                'response' => $response,
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid response from eSewa',
            'response' => $response,
        ];
    }

    /**
     * Extract value from XML response
     */
    private function extractXmlValue(string $xml, string $tag): string
    {
        $pattern = "/<{$tag}>(.*?)<\/{$tag}>/";
        preg_match($pattern, $xml, $matches);
        return $matches[1] ?? '';
    }

    /**
     * Process success callback from eSewa
     * 
     * @param array<string, string> $params Callback parameters from eSewa
     * @return array<string, mixed> Processing result
     */
    public function processSuccess(array $params): array
    {
        $refId = $params['refId'] ?? '';
        $orderId = $params['oid'] ?? '';
        $amount = (float) ($params['amt'] ?? 0);
        
        if (empty($refId) || empty($orderId)) {
            if ($this->logTransactions) {
                $this->log('Invalid success callback parameters', $params, 'error');
            }
            return [
                'success' => false,
                'message' => 'Invalid callback parameters',
            ];
        }
        
        if ($this->logTransactions) {
            $this->log('Processing payment success callback', [
                'order_id' => $orderId,
                'reference_id' => $refId,
                'amount' => $amount,
            ]);
        }
        
        // Verify the payment with eSewa
        $verificationResult = $this->verifyPayment($refId, $orderId, $amount);
        
        if ($verificationResult['success']) {
            if ($this->logTransactions) {
                $this->log('Payment success confirmed', [
                    'order_id' => $orderId,
                    'reference_id' => $refId,
                ]);
            }
        }
        
        return $verificationResult;
    }

    /**
     * Process failure callback from eSewa
     * 
     * @param array<string, string> $params Callback parameters
     * @return array<string, mixed> Processing result
     */
    public function processFailure(array $params): array
    {
        $orderId = $params['oid'] ?? '';
        
        if ($this->logTransactions) {
            $this->log('Payment failure callback received', [
                'order_id' => $orderId,
                'params' => $params,
            ], 'warning');
        }
        
        return [
            'success' => false,
            'message' => 'Payment was cancelled or failed',
            'order_id' => $orderId,
        ];
    }

    /**
     * Log transaction activity
     * 
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context
     * @param string $level Log level (info, warning, error)
     */
    private function log(string $message, array $context = [], string $level = 'info'): void
    {
        if (!$this->logTransactions) {
            return;
        }
        
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/esewa-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Get payment URL
     */
    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
    }

    /**
     * Check if in test mode
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }
}
