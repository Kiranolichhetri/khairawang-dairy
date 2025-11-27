<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Core\Application;

/**
 * Notification Service
 * 
 * Handles in-app notifications for users.
 */
class NotificationService
{
    private ?EmailService $emailService;
    private ?SmsService $smsService;

    public function __construct(?EmailService $emailService = null, ?SmsService $smsService = null)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    /**
     * Create a notification for a user
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(int $userId, string $type, string $title, string $message, array $data = []): array
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => !empty($data) ? json_encode($data) : null,
            'is_read' => false,
        ]);
        
        return [
            'success' => true,
            'message' => 'Notification created',
            'notification_id' => $notification->getKey(),
        ];
    }

    /**
     * Notify a user with optional email/SMS based on preferences
     * 
     * @param array<string, mixed>|User $user
     * @param array<string, mixed> $notification
     * @return array<string, mixed>
     */
    public function notify(array|User $user, array $notification): array
    {
        $userData = is_object($user) ? $user->toArray() : $user;
        $userId = $userData['id'] ?? 0;
        
        if (empty($userId)) {
            return ['success' => false, 'message' => 'Invalid user'];
        }
        
        // Create in-app notification
        $result = $this->create(
            (int) $userId,
            $notification['type'] ?? 'general',
            $notification['title'] ?? 'Notification',
            $notification['message'] ?? '',
            $notification['data'] ?? []
        );
        
        // Check user preferences for email/SMS
        $preferences = NotificationPreference::getForUser((int) $userId);
        
        $emailSent = false;
        $smsSent = false;
        
        // Send email if enabled for this notification type
        if ($this->shouldSendEmail($preferences, $notification['type'] ?? '')) {
            $emailSent = $this->sendEmailNotification($userData, $notification);
        }
        
        // Send SMS if enabled for this notification type
        if ($this->shouldSendSms($preferences, $notification['type'] ?? '')) {
            $smsSent = $this->sendSmsNotification($userData, $notification);
        }
        
        return array_merge($result, [
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
        ]);
    }

    /**
     * Mark notification as read
     * 
     * @return array<string, mixed>
     */
    public function markAsRead(int $notificationId, ?int $userId = null): array
    {
        $notification = Notification::find($notificationId);
        
        if ($notification === null) {
            return ['success' => false, 'message' => 'Notification not found'];
        }
        
        // Verify ownership if user ID provided
        if ($userId !== null && $notification->user_id != $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $notification->markAsRead();
        
        return ['success' => true, 'message' => 'Notification marked as read'];
    }

    /**
     * Mark all notifications as read for a user
     * 
     * @return array<string, mixed>
     */
    public function markAllAsRead(int $userId): array
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $db->update('notifications', [
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s'),
        ], ['user_id' => $userId, 'is_read' => false]);
        
        return ['success' => true, 'message' => 'All notifications marked as read'];
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::getUnreadCount($userId);
    }

    /**
     * Get notifications for a user
     * 
     * @return array<Notification>
     */
    public function getUserNotifications(int $userId, int $limit = 50, int $offset = 0, bool $unreadOnly = false): array
    {
        return Notification::getForUser($userId, $limit, $offset, $unreadOnly);
    }

    /**
     * Delete a notification
     * 
     * @return array<string, mixed>
     */
    public function delete(int $notificationId, ?int $userId = null): array
    {
        $notification = Notification::find($notificationId);
        
        if ($notification === null) {
            return ['success' => false, 'message' => 'Notification not found'];
        }
        
        // Verify ownership if user ID provided
        if ($userId !== null && $notification->user_id != $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $notification->delete();
        
        return ['success' => true, 'message' => 'Notification deleted'];
    }

    /**
     * Delete all read notifications for a user
     * 
     * @return array<string, mixed>
     */
    public function deleteReadNotifications(int $userId): array
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $db->delete('notifications', ['user_id' => $userId, 'is_read' => true]);
        
        return ['success' => true, 'message' => 'Read notifications deleted'];
    }

    /**
     * Get or create notification preferences for user
     */
    public function getPreferences(int $userId): NotificationPreference
    {
        return NotificationPreference::getOrCreateForUser($userId);
    }

    /**
     * Update notification preferences
     * 
     * @param array<string, bool> $preferences
     * @return array<string, mixed>
     */
    public function updatePreferences(int $userId, array $preferences): array
    {
        $pref = NotificationPreference::getOrCreateForUser($userId);
        
        $allowedFields = [
            'email_orders',
            'email_promotions',
            'email_newsletter',
            'sms_orders',
            'sms_promotions',
            'push_enabled',
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($preferences[$field])) {
                $pref->$field = (bool) $preferences[$field];
            }
        }
        
        $pref->save();
        
        return [
            'success' => true,
            'message' => 'Preferences updated',
            'preferences' => $pref->toArray(),
        ];
    }

    /**
     * Send notification for order events
     * 
     * @param array<string, mixed>|\App\Models\Order $order
     * @return array<string, mixed>
     */
    public function notifyOrderEvent(array|object $order, string $event): array
    {
        $orderData = is_object($order) ? $order->toArray() : $order;
        $userId = $orderData['user_id'] ?? null;
        
        if (empty($userId)) {
            return ['success' => false, 'message' => 'No user associated with order'];
        }
        
        $orderNumber = $orderData['order_number'] ?? '';
        
        $notifications = [
            'order_placed' => [
                'type' => 'order_placed',
                'title' => 'Order Confirmed',
                'message' => "Your order #{$orderNumber} has been confirmed. Thank you for your purchase!",
            ],
            'order_shipped' => [
                'type' => 'order_shipped',
                'title' => 'Order Shipped',
                'message' => "Your order #{$orderNumber} has been shipped. Track your delivery from your account.",
            ],
            'order_delivered' => [
                'type' => 'order_delivered',
                'title' => 'Order Delivered',
                'message' => "Your order #{$orderNumber} has been delivered. Enjoy your dairy products!",
            ],
            'order_cancelled' => [
                'type' => 'order_cancelled',
                'title' => 'Order Cancelled',
                'message' => "Your order #{$orderNumber} has been cancelled. If you have questions, please contact us.",
            ],
            'payment_received' => [
                'type' => 'payment_received',
                'title' => 'Payment Confirmed',
                'message' => "Payment for order #{$orderNumber} has been confirmed. Your order is being processed.",
            ],
        ];
        
        if (!isset($notifications[$event])) {
            return ['success' => false, 'message' => 'Invalid event type'];
        }
        
        $notification = $notifications[$event];
        $notification['data'] = ['order_number' => $orderNumber, 'order_id' => $orderData['id'] ?? null];
        
        $user = User::find((int) $userId);
        
        if ($user === null) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        return $this->notify($user, $notification);
    }

    /**
     * Check if email should be sent based on preferences and notification type
     */
    private function shouldSendEmail(?NotificationPreference $pref, string $type): bool
    {
        if ($pref === null) {
            return true; // Default to sending
        }
        
        return match (true) {
            in_array($type, ['order_placed', 'order_shipped', 'order_delivered', 'order_cancelled', 'payment_received']) 
                => $pref->email_orders ?? true,
            in_array($type, ['promotion', 'price_drop', 'back_in_stock']) 
                => $pref->email_promotions ?? true,
            default => true,
        };
    }

    /**
     * Check if SMS should be sent based on preferences and notification type
     */
    private function shouldSendSms(?NotificationPreference $pref, string $type): bool
    {
        if ($pref === null) {
            return false; // Default to not sending SMS (cost consideration)
        }
        
        return match (true) {
            in_array($type, ['order_placed', 'order_shipped', 'order_delivered', 'order_cancelled', 'payment_received']) 
                => $pref->sms_orders ?? true,
            in_array($type, ['promotion', 'price_drop', 'back_in_stock']) 
                => $pref->sms_promotions ?? false,
            default => false,
        };
    }

    /**
     * Send email notification
     * 
     * @param array<string, mixed> $user
     * @param array<string, mixed> $notification
     */
    private function sendEmailNotification(array $user, array $notification): bool
    {
        if ($this->emailService === null) {
            $this->emailService = new EmailService();
        }
        
        $email = $user['email'] ?? '';
        if (empty($email)) {
            return false;
        }
        
        return $this->emailService->send(
            $email,
            $notification['title'] ?? 'Notification',
            'emails/notification',
            [
                'user' => $user,
                'notification' => $notification,
            ]
        );
    }

    /**
     * Send SMS notification
     * 
     * @param array<string, mixed> $user
     * @param array<string, mixed> $notification
     */
    private function sendSmsNotification(array $user, array $notification): bool
    {
        if ($this->smsService === null) {
            $this->smsService = new SmsService();
        }
        
        $phone = $user['phone'] ?? '';
        if (empty($phone)) {
            return false;
        }
        
        $result = $this->smsService->send(
            $phone,
            "KHAIRAWANG DAIRY: {$notification['title']} - {$notification['message']}"
        );
        
        return $result['success'] ?? false;
    }
}
