<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * Aakash SMS Service
 * 
 * Integration with Aakash SMS Gateway (Alternative Nepal SMS provider).
 * API Documentation: https://aakashsms.com/
 */
class AakashSmsService
{
    private string $token;
    private string $from;
    private string $url;

    public function __construct()
    {
        $app = Application::getInstance();
        $config = $app?->config('sms.drivers.aakash', []) ?? [];
        
        $this->token = $config['token'] ?? '';
        $this->from = $config['from'] ?? '';
        $this->url = $config['url'] ?? 'https://aakashsms.com/api/v3/send_sms';
    }

    /**
     * Send SMS via Aakash SMS API
     * 
     * @return array<string, mixed>
     */
    public function send(string $phone, string $message): array
    {
        if (empty($this->token)) {
            return $this->logSms($phone, $message);
        }
        
        // Ensure phone has country code for Aakash
        if (!str_starts_with($phone, '977')) {
            $phone = '977' . $phone;
        }
        
        $data = [
            'auth_token' => $this->token,
            'to' => $phone,
            'text' => $message,
        ];
        
        // Add sender ID if configured
        if (!empty($this->from)) {
            $data['from'] = $this->from;
        }
        
        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
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
            
            $result = json_decode($response, true);
            
            // Aakash SMS returns different response structure
            if ($httpCode === 200 && isset($result['response_code']) && $result['response_code'] == 200) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'message_id' => $result['message_id'] ?? null,
                    'credit' => $result['credit'] ?? null,
                ];
            }
            
            // Alternative success check
            if ($httpCode === 200 && isset($result['success']) && $result['success'] === true) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $result,
                ];
            }
            
            return [
                'success' => false,
                'message' => $result['message'] ?? $result['error'] ?? 'Failed to send SMS',
                'response_code' => $result['response_code'] ?? $httpCode,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check SMS credits balance
     * 
     * @return array<string, mixed>
     */
    public function checkCredits(): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'Token not configured'];
        }
        
        try {
            $url = 'https://aakashsms.com/api/v3/credit/';
            
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(['auth_token' => $this->token]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($httpCode === 200 && isset($result['available_credit'])) {
                return [
                    'success' => true,
                    'credits' => $result['available_credit'],
                ];
            }
            
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to check credits',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Log SMS for development/testing
     * 
     * @return array<string, mixed>
     */
    private function logSms(string $phone, string $message): array
    {
        $app = Application::getInstance();
        $logPath = $app?->basePath() . '/storage/logs/sms.log';
        
        $logEntry = sprintf(
            "[%s] AAKASH SMS | To: %s | Message: %s\n",
            date('Y-m-d H:i:s'),
            $phone,
            substr($message, 0, 100) . (strlen($message) > 100 ? '...' : '')
        );
        
        @file_put_contents($logPath, $logEntry, FILE_APPEND);
        
        return [
            'success' => true,
            'message' => 'SMS logged (token not configured)',
            'logged' => true,
        ];
    }
}
