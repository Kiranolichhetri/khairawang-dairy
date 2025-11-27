<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * Payment Service
 * 
 * Handles payment processing for multiple payment methods.
 */
class PaymentService
{
    private EsewaService $esewaService;
    
    public function __construct(?EsewaService $esewaService = null)
    {
        $this->esewaService = $esewaService ?? new EsewaService();
    }

    /**
     * Get available payment methods
     * 
     * @return array<string, array<string, mixed>>
     */
    public function getPaymentMethods(): array
    {
        $config = config('payment.methods', []);
        $methods = [];
        
        foreach ($config as $key => $method) {
            if ($method['enabled'] ?? false) {
                $methods[$key] = [
                    'key' => $key,
                    'name' => $method['name'] ?? $key,
                    'icon' => $method['icon'] ?? null,
                ];
            }
        }
        
        return $methods;
    }

    /**
     * Initiate payment based on method
     * 
     * @return array<string, mixed>
     */
    public function initiatePayment(
        string $method,
        string $orderId,
        float $amount,
        float $shipping = 0
    ): array {
        return match ($method) {
            'esewa' => $this->initiateEsewaPayment($orderId, $amount, 0, 0, $shipping),
            'cod' => $this->initiateCodPayment($orderId, $amount),
            default => ['success' => false, 'message' => 'Invalid payment method'],
        };
    }

    /**
     * Initiate eSewa payment
     * 
     * @return array<string, mixed>
     */
    private function initiateEsewaPayment(
        string $orderId,
        float $amount,
        float $taxAmount = 0,
        float $serviceCharge = 0,
        float $deliveryCharge = 0
    ): array {
        $data = $this->esewaService->initiatePayment(
            $orderId,
            $amount,
            $taxAmount,
            $serviceCharge,
            $deliveryCharge
        );
        
        return [
            'success' => true,
            'method' => 'esewa',
            'redirect' => true,
            'payment_url' => $data['payment_url'],
            'params' => $data['params'],
        ];
    }

    /**
     * Initiate Cash on Delivery payment
     * 
     * @return array<string, mixed>
     */
    private function initiateCodPayment(string $orderId, float $amount): array
    {
        $maxAmount = config('payment.cod.max_amount', 50000);
        
        if ($amount > $maxAmount) {
            return [
                'success' => false,
                'message' => "Cash on Delivery is not available for orders over NPR " . number_format($maxAmount),
            ];
        }
        
        return [
            'success' => true,
            'method' => 'cod',
            'redirect' => false,
            'message' => 'Order placed successfully. Payment will be collected on delivery.',
        ];
    }

    /**
     * Verify payment callback
     * 
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    public function verifyPayment(string $method, array $params): array
    {
        return match ($method) {
            'esewa' => $this->esewaService->processSuccess($params),
            default => ['success' => false, 'message' => 'Invalid payment method'],
        };
    }

    /**
     * Process payment failure
     * 
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    public function processFailure(string $method, array $params): array
    {
        return match ($method) {
            'esewa' => $this->esewaService->processFailure($params),
            default => ['success' => false, 'message' => 'Payment failed'],
        };
    }

    /**
     * Check if payment method is available
     */
    public function isMethodAvailable(string $method): bool
    {
        $methods = $this->getPaymentMethods();
        return isset($methods[$method]);
    }

    /**
     * Get eSewa service instance
     */
    public function getEsewaService(): EsewaService
    {
        return $this->esewaService;
    }
}
