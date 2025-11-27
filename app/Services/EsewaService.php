<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * eSewa Payment Service
 * 
 * Handles eSewa payment gateway integration for Nepal.
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

    public function __construct()
    {
        $app = Application::getInstance();
        $config = $app?->config('payment.esewa', []);
        $testMode = $app?->config('payment.test_mode', true);
        
        $this->merchantCode = $config['merchant_code'] ?? 'EPAYTEST';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->testMode = (bool) $testMode;
        
        $urls = $config['urls'] ?? [];
        $mode = $this->testMode ? 'test' : 'live';
        
        $this->paymentUrl = $urls['payment'][$mode] ?? 'https://uat.esewa.com.np/epay/main';
        $this->verifyUrl = $urls['verify'][$mode] ?? 'https://uat.esewa.com.np/epay/transrec';
        
        $baseUrl = $app?->config('app.url', 'http://localhost');
        $this->successUrl = $baseUrl . ($config['success_url'] ?? '/payment/success');
        $this->failureUrl = $baseUrl . ($config['failure_url'] ?? '/payment/failure');
    }

    /**
     * Generate payment form data for eSewa
     * 
     * @return array<string, mixed>
     */
    public function initiatePayment(
        string $orderId,
        float $amount,
        float $taxAmount = 0,
        float $serviceCharge = 0,
        float $deliveryCharge = 0,
        string $productName = 'KHAIRAWANG DAIRY Order'
    ): array {
        $totalAmount = $amount + $taxAmount + $serviceCharge + $deliveryCharge;
        
        // Validate secret key is configured for production mode
        if (!$this->testMode && empty($this->secretKey)) {
            throw new \RuntimeException('eSewa secret key is required for production mode');
        }
        
        // Generate signature for eSewa v2
        $signature = $this->generateSignature($totalAmount, $orderId);
        
        return [
            'payment_url' => $this->paymentUrl,
            'params' => [
                'amt' => $amount,
                'txAmt' => $taxAmount,
                'psc' => $serviceCharge,
                'pdc' => $deliveryCharge,
                'tAmt' => $totalAmount,
                'pid' => $orderId,
                'scd' => $this->merchantCode,
                'su' => $this->successUrl . '?oid=' . urlencode($orderId),
                'fu' => $this->failureUrl . '?oid=' . urlencode($orderId),
            ],
            'signature' => $signature,
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
     * @return array<string, mixed>
     */
    public function verifyPayment(string $referenceId, string $orderId, float $amount): array
    {
        $data = [
            'amt' => $amount,
            'rid' => $referenceId,
            'pid' => $orderId,
            'scd' => $this->merchantCode,
        ];
        
        $url = $this->verifyUrl . '?' . http_build_query($data);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => !$this->testMode,
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
        
        // Parse XML response from eSewa
        if ($response && str_contains($response, '<response_code>')) {
            $code = $this->extractXmlValue($response, 'response_code');
            
            if ($code === 'Success') {
                return [
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'transaction_id' => $referenceId,
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Payment verification failed',
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
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    public function processSuccess(array $params): array
    {
        $refId = $params['refId'] ?? '';
        $orderId = $params['oid'] ?? '';
        $amount = (float) ($params['amt'] ?? 0);
        
        if (empty($refId) || empty($orderId)) {
            return [
                'success' => false,
                'message' => 'Invalid callback parameters',
            ];
        }
        
        // Verify the payment
        return $this->verifyPayment($refId, $orderId, $amount);
    }

    /**
     * Process failure callback from eSewa
     * 
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    public function processFailure(array $params): array
    {
        return [
            'success' => false,
            'message' => 'Payment was cancelled or failed',
            'order_id' => $params['oid'] ?? '',
        ];
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
