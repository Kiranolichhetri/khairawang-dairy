<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * Email Service
 * 
 * Handles sending email notifications using PHP's mail function
 * or SMTP configuration.
 */
class EmailService
{
    private array $config;
    private string $viewsPath;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->config = $app?->config('mail', []) ?? [];
        $basePath = $app?->basePath() ?? dirname(__DIR__, 2);
        $this->viewsPath = $basePath . '/resources/views';
    }

    /**
     * Send an email
     * 
     * @param string|array<string> $to
     * @param array<string, mixed> $data
     */
    public function send(string|array $to, string $subject, string $template, array $data = []): bool
    {
        $to = is_array($to) ? $to : [$to];
        
        // Validate email addresses
        foreach ($to as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }
        
        // Render email template
        $content = $this->renderTemplate($template, $data);
        
        // Get sender info
        $fromAddress = $this->config['from']['address'] ?? 'noreply@khairawangdairy.com';
        $fromName = $this->config['from']['name'] ?? 'KHAIRAWANG DAIRY';
        
        // Send using configured mailer
        $mailer = $this->config['default'] ?? 'smtp';
        
        if ($mailer === 'log') {
            return $this->logEmail($to, $subject, $content);
        }
        
        return $this->sendViaSmtp($to, $subject, $content, $fromAddress, $fromName);
    }

    /**
     * Send order confirmation email
     * 
     * @param array<string, mixed>|\App\Models\Order $order
     */
    public function sendOrderConfirmation(array|object $order): bool
    {
        $orderData = is_object($order) ? $order->toArray() : $order;
        $email = $orderData['shipping_email'] ?? $orderData['email'] ?? '';
        
        if (empty($email)) {
            return false;
        }
        
        return $this->send(
            $email,
            'Order Confirmation - #' . ($orderData['order_number'] ?? ''),
            'emails/order-confirmation',
            ['order' => $orderData]
        );
    }

    /**
     * Send order shipped email
     * 
     * @param array<string, mixed>|\App\Models\Order $order
     */
    public function sendOrderShipped(array|object $order): bool
    {
        $orderData = is_object($order) ? $order->toArray() : $order;
        $email = $orderData['shipping_email'] ?? $orderData['email'] ?? '';
        
        if (empty($email)) {
            return false;
        }
        
        return $this->send(
            $email,
            'Your Order Has Been Shipped - #' . ($orderData['order_number'] ?? ''),
            'emails/order-shipped',
            ['order' => $orderData]
        );
    }

    /**
     * Send order delivered email
     * 
     * @param array<string, mixed>|\App\Models\Order $order
     */
    public function sendOrderDelivered(array|object $order): bool
    {
        $orderData = is_object($order) ? $order->toArray() : $order;
        $email = $orderData['shipping_email'] ?? $orderData['email'] ?? '';
        
        if (empty($email)) {
            return false;
        }
        
        return $this->send(
            $email,
            'Your Order Has Been Delivered - #' . ($orderData['order_number'] ?? ''),
            'emails/order-delivered',
            ['order' => $orderData]
        );
    }

    /**
     * Send password reset email
     * 
     * @param array<string, mixed>|\App\Models\User $user
     */
    public function sendPasswordReset(array|object $user, string $token): bool
    {
        $userData = is_object($user) ? $user->toArray() : $user;
        $email = $userData['email'] ?? '';
        
        if (empty($email)) {
            return false;
        }
        
        $resetUrl = url('/reset-password/' . $token);
        
        return $this->send(
            $email,
            'Reset Your Password - KHAIRAWANG DAIRY',
            'emails/password-reset',
            [
                'user' => $userData,
                'resetUrl' => $resetUrl,
                'token' => $token,
            ]
        );
    }

    /**
     * Send welcome email
     * 
     * @param array<string, mixed>|\App\Models\User $user
     */
    public function sendWelcome(array|object $user): bool
    {
        $userData = is_object($user) ? $user->toArray() : $user;
        $email = $userData['email'] ?? '';
        
        if (empty($email)) {
            return false;
        }
        
        return $this->send(
            $email,
            'Welcome to KHAIRAWANG DAIRY!',
            'emails/welcome',
            ['user' => $userData]
        );
    }

    /**
     * Send email verification
     * 
     * @param array<string, mixed>|\App\Models\User $user
     */
    public function sendEmailVerification(array|object $user, string $token): bool
    {
        $userData = is_object($user) ? $user->toArray() : $user;
        $email = $userData['email'] ?? '';
        
        if (empty($email)) {
            return false;
        }
        
        $verificationUrl = url('/verify-email/' . $token);
        
        return $this->send(
            $email,
            'Verify Your Email Address - KHAIRAWANG DAIRY',
            'emails/email-verification',
            [
                'user' => $userData,
                'verificationUrl' => $verificationUrl,
                'token' => $token,
            ]
        );
    }

    /**
     * Send newsletter to subscribers
     * 
     * @param array<array<string, mixed>> $subscribers
     * @param array<string, mixed> $campaign
     */
    public function sendNewsletter(array $subscribers, array $campaign): int
    {
        $sentCount = 0;
        
        foreach ($subscribers as $subscriber) {
            $email = $subscriber['email'] ?? '';
            if (empty($email)) {
                continue;
            }
            
            $unsubscribeUrl = url('/newsletter/unsubscribe/' . ($subscriber['unsubscribe_token'] ?? ''));
            
            $sent = $this->send(
                $email,
                $campaign['subject'] ?? 'Newsletter from KHAIRAWANG DAIRY',
                'emails/newsletter',
                [
                    'subscriber' => $subscriber,
                    'campaign' => $campaign,
                    'unsubscribeUrl' => $unsubscribeUrl,
                ]
            );
            
            if ($sent) {
                $sentCount++;
            }
        }
        
        return $sentCount;
    }

    /**
     * Render email template
     * 
     * @param array<string, mixed> $data
     */
    private function renderTemplate(string $template, array $data): string
    {
        $filePath = $this->viewsPath . '/' . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($filePath)) {
            // Return plain text fallback
            return $data['message'] ?? $template;
        }
        
        // Extract data for template
        extract($data);
        
        ob_start();
        include $filePath;
        return ob_get_clean() ?: '';
    }

    /**
     * Send email via SMTP
     * 
     * @param array<string> $to
     */
    private function sendViaSmtp(array $to, string $subject, string $content, string $fromAddress, string $fromName): bool
    {
        $smtpConfig = $this->config['mailers']['smtp'] ?? [];
        $host = $smtpConfig['host'] ?? 'localhost';
        $port = (int) ($smtpConfig['port'] ?? 587);
        $username = $smtpConfig['username'] ?? '';
        $password = $smtpConfig['password'] ?? '';
        $encryption = $smtpConfig['encryption'] ?? 'tls';
        
        // If no SMTP configured, use PHP mail
        if (empty($username) || empty($password)) {
            return $this->sendViaMail($to, $subject, $content, $fromAddress, $fromName);
        }
        
        try {
            // Connect to SMTP server
            $prefix = $encryption === 'ssl' ? 'ssl://' : '';
            $socket = @fsockopen($prefix . $host, $port, $errno, $errstr, 30);
            
            if (!$socket) {
                return $this->sendViaMail($to, $subject, $content, $fromAddress, $fromName);
            }
            
            $this->smtpRead($socket);
            
            // EHLO
            $this->smtpCommand($socket, "EHLO " . gethostname());
            
            // STARTTLS for TLS encryption
            if ($encryption === 'tls') {
                $this->smtpCommand($socket, "STARTTLS");
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpCommand($socket, "EHLO " . gethostname());
            }
            
            // AUTH LOGIN
            $this->smtpCommand($socket, "AUTH LOGIN");
            $this->smtpCommand($socket, base64_encode($username));
            $this->smtpCommand($socket, base64_encode($password));
            
            // MAIL FROM
            $this->smtpCommand($socket, "MAIL FROM:<{$fromAddress}>");
            
            // RCPT TO
            foreach ($to as $recipient) {
                $this->smtpCommand($socket, "RCPT TO:<{$recipient}>");
            }
            
            // DATA
            $this->smtpCommand($socket, "DATA");
            
            // Email headers and body
            $headers = [
                "From: {$fromName} <{$fromAddress}>",
                "To: " . implode(', ', $to),
                "Subject: {$subject}",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "Date: " . date('r'),
            ];
            
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $content . "\r\n.";
            $this->smtpCommand($socket, $message);
            
            // QUIT
            $this->smtpCommand($socket, "QUIT");
            
            fclose($socket);
            
            return true;
        } catch (\Exception $e) {
            // Fallback to PHP mail
            return $this->sendViaMail($to, $subject, $content, $fromAddress, $fromName);
        }
    }

    /**
     * Send SMTP command
     * 
     * @param resource $socket
     */
    private function smtpCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->smtpRead($socket);
    }

    /**
     * Read SMTP response
     * 
     * @param resource $socket
     */
    private function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * Send email using PHP mail function
     * 
     * @param array<string> $to
     */
    private function sendViaMail(array $to, string $subject, string $content, string $fromAddress, string $fromName): bool
    {
        $headers = [
            'From' => "{$fromName} <{$fromAddress}>",
            'Reply-To' => $fromAddress,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'KHAIRAWANG DAIRY Mailer',
        ];
        
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "{$key}: {$value}\r\n";
        }
        
        return @mail(implode(',', $to), $subject, $content, $headerString);
    }

    /**
     * Log email instead of sending (for development)
     * 
     * @param array<string> $to
     */
    private function logEmail(array $to, string $subject, string $content): bool
    {
        $app = Application::getInstance();
        $logPath = $app?->basePath() . '/storage/logs/mail.log';
        
        $logEntry = sprintf(
            "[%s] To: %s | Subject: %s | Content Length: %d\n",
            date('Y-m-d H:i:s'),
            implode(', ', $to),
            $subject,
            strlen($content)
        );
        
        @file_put_contents($logPath, $logEntry, FILE_APPEND);
        
        return true;
    }
}
