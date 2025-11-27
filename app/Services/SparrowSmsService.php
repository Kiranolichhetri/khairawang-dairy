<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * Sparrow SMS Service
 * 
 * Integration with Sparrow SMS Gateway (Nepal's popular SMS provider).
 * API Documentation: https://docs.sparrowsms.com/
 */
class SparrowSmsService
{
    private string $token;
    private string $from;
    private string $url;

    public function __construct()
    {
        $app = Application::getInstance();
        $config = $app?->config('sms.drivers.sparrow', []) ?? [];
        
        $this->token = $config['token'] ?? '';
        $this->from = $config['from'] ?? 'KhairawangDairy';
        $this->url = $config['url'] ?? 'https://api.sparrowsms.com/v2/sms/';
    }

    /**
     * Send SMS via Sparrow SMS API
     * 
     * @return array<string, mixed>
     */
    public function send(string $phone, string $message): array
    {
        if (empty($this->token)) {
            return $this->logSms($phone, $message);
        }
        
        // Ensure phone has country code for Sparrow
        if (!str_starts_with($phone, '977')) {
            $phone = '977' . $phone;
        }
        
        $data = [
            'token' => $this->token,
            'from' => $this->from,
            'to' => $phone,
            'text' => $message,
        ];
        
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
            
            if ($httpCode === 200 && isset($result['response_code']) && $result['response_code'] === 200) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'message_id' => $result['message_id'] ?? null,
                    'credits_used' => $result['credits_used'] ?? null,
                    'credits_remaining' => $result['credits_remaining'] ?? null,
                ];
            }
            
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to send SMS',
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
            $url = 'https://api.sparrowsms.com/v2/credit/?token=' . urlencode($this->token);
            
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($httpCode === 200 && isset($result['credits_available'])) {
                return [
                    'success' => true,
                    'credits' => $result['credits_available'],
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
            "[%s] SPARROW SMS | To: %s | Message: %s\n",
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
