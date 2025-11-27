<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * SMS Service
 * 
 * Base SMS service that delegates to configured SMS driver.
 */
class SmsService
{
    private array $config;
    private ?object $driver = null;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->config = $app?->config('sms', []) ?? [];
    }

    /**
     * Get the configured SMS driver
     */
    private function getDriver(): object
    {
        if ($this->driver === null) {
            $driverName = $this->config['default'] ?? 'sparrow';
            
            $this->driver = match ($driverName) {
                'sparrow' => new SparrowSmsService(),
                'aakash' => new AakashSmsService(),
                default => new SparrowSmsService(),
            };
        }
        
        return $this->driver;
    }

    /**
     * Validate Nepal phone number format
     */
    public static function validateNepalPhone(string $phone): bool
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Nepal mobile numbers: 10 digits starting with 98, 97, or 96
        if (strlen($phone) === 10) {
            return preg_match('/^(98|97|96)[0-9]{8}$/', $phone) === 1;
        }
        
        // With country code +977
        if (strlen($phone) === 13 && str_starts_with($phone, '977')) {
            return preg_match('/^977(98|97|96)[0-9]{8}$/', $phone) === 1;
        }
        
        return false;
    }

    /**
     * Format phone number for Nepal
     */
    public static function formatNepalPhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading 977 country code if present
        if (strlen($phone) === 13 && str_starts_with($phone, '977')) {
            $phone = substr($phone, 3);
        }
        
        return $phone;
    }

    /**
     * Send SMS message
     * 
     * @return array<string, mixed>
     */
    public function send(string $phone, string $message): array
    {
        if (!($this->config['enabled'] ?? true)) {
            return ['success' => false, 'message' => 'SMS is disabled'];
        }
        
        // Format and validate phone number
        $phone = self::formatNepalPhone($phone);
        
        if (!self::validateNepalPhone($phone)) {
            return ['success' => false, 'message' => 'Invalid Nepal phone number'];
        }
        
        return $this->getDriver()->send($phone, $message);
    }

    /**
     * Send OTP
     * 
     * @return array<string, mixed>
     */
    public function sendOtp(string $phone, string $otp): array
    {
        $message = "Your KHAIRAWANG DAIRY verification code is: {$otp}. Valid for " . 
                   ($this->config['otp']['expiry'] ?? 5) . " minutes. Do not share this code.";
        
        return $this->send($phone, $message);
    }

    /**
     * Generate OTP
     */
    public function generateOtp(): string
    {
        $length = $this->config['otp']['length'] ?? 6;
        return str_pad((string) random_int(0, (int) str_repeat('9', $length)), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Send order confirmation SMS
     * 
     * @param array<string, mixed>|\App\Models\Order $order
     * @return array<string, mixed>
     */
    public function sendOrderConfirmation(array|object $order): array
    {
        $orderData = is_object($order) ? $order->toArray() : $order;
        $phone = $orderData['shipping_phone'] ?? $orderData['phone'] ?? '';
        $orderNumber = $orderData['order_number'] ?? '';
        $total = $orderData['total'] ?? 0;
        
        if (empty($phone)) {
            return ['success' => false, 'message' => 'No phone number provided'];
        }
        
        $message = "KHAIRAWANG DAIRY: Order #{$orderNumber} confirmed! Total: Rs. " . number_format((float) $total, 2) . 
                   ". Thank you for your purchase!";
        
        return $this->send($phone, $message);
    }

    /**
     * Send order shipped SMS
     * 
     * @param array<string, mixed>|\App\Models\Order $order
     * @return array<string, mixed>
     */
    public function sendOrderShipped(array|object $order): array
    {
        $orderData = is_object($order) ? $order->toArray() : $order;
        $phone = $orderData['shipping_phone'] ?? $orderData['phone'] ?? '';
        $orderNumber = $orderData['order_number'] ?? '';
        
        if (empty($phone)) {
            return ['success' => false, 'message' => 'No phone number provided'];
        }
        
        $message = "KHAIRAWANG DAIRY: Order #{$orderNumber} has been shipped! " .
                   "Track your delivery at " . url('/account/orders/' . $orderNumber . '/track');
        
        return $this->send($phone, $message);
    }

    /**
     * Send order delivered SMS
     * 
     * @param array<string, mixed>|\App\Models\Order $order
     * @return array<string, mixed>
     */
    public function sendOrderDelivered(array|object $order): array
    {
        $orderData = is_object($order) ? $order->toArray() : $order;
        $phone = $orderData['shipping_phone'] ?? $orderData['phone'] ?? '';
        $orderNumber = $orderData['order_number'] ?? '';
        
        if (empty($phone)) {
            return ['success' => false, 'message' => 'No phone number provided'];
        }
        
        $message = "KHAIRAWANG DAIRY: Order #{$orderNumber} has been delivered! " .
                   "Thank you for choosing us. Rate your experience at " . url('/products');
        
        return $this->send($phone, $message);
    }

    /**
     * Store OTP for verification
     * 
     * @return array<string, mixed>
     */
    public function storeOtp(string $phone, string $otp): array
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($session === null) {
            return ['success' => false, 'message' => 'Session not available'];
        }
        
        $otpData = [
            'otp' => $otp,
            'phone' => self::formatNepalPhone($phone),
            'expires_at' => time() + (($this->config['otp']['expiry'] ?? 5) * 60),
        ];
        
        $session->set('otp_verification', $otpData);
        
        return ['success' => true, 'message' => 'OTP stored'];
    }

    /**
     * Verify OTP
     * 
     * @return array<string, mixed>
     */
    public function verifyOtp(string $phone, string $otp): array
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($session === null) {
            return ['success' => false, 'message' => 'Session not available'];
        }
        
        $storedData = $session->get('otp_verification');
        
        if ($storedData === null) {
            return ['success' => false, 'message' => 'No OTP found'];
        }
        
        $formattedPhone = self::formatNepalPhone($phone);
        
        if ($storedData['phone'] !== $formattedPhone) {
            return ['success' => false, 'message' => 'Phone number mismatch'];
        }
        
        if (time() > $storedData['expires_at']) {
            $session->remove('otp_verification');
            return ['success' => false, 'message' => 'OTP expired'];
        }
        
        if ($storedData['otp'] !== $otp) {
            return ['success' => false, 'message' => 'Invalid OTP'];
        }
        
        // Clear OTP after successful verification
        $session->remove('otp_verification');
        
        return ['success' => true, 'message' => 'OTP verified'];
    }
}
