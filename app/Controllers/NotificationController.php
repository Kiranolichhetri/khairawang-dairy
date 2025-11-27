<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Notification Controller
 * 
 * Handles in-app notifications for authenticated users.
 */
class NotificationController
{
    private NotificationService $notificationService;

    public function __construct(?NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService ?? new NotificationService();
    }

    /**
     * Get current user ID from session
     */
    private function getCurrentUserId(): ?int
    {
        $app = Application::getInstance();
        $session = $app?->session();
        $userId = $session?->get('user_id');
        
        return $userId !== null ? (int) $userId : null;
    }

    /**
     * List notifications for current user
     */
    public function index(Request $request): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $unreadOnly = (bool) $request->query('unread_only', false);
        
        $offset = ($page - 1) * $perPage;
        $notifications = $this->notificationService->getUserNotifications($userId, $perPage, $offset, $unreadOnly);
        $unreadCount = $this->notificationService->getUnreadCount($userId);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'notifications' => array_map(fn($n) => $this->formatNotification($n), $notifications),
                    'unread_count' => $unreadCount,
                ],
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);
        }
        
        return Response::view('notifications.index', [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'page' => $page,
            'perPage' => $perPage,
            'unreadOnly' => $unreadOnly,
        ]);
    }

    /**
     * Show a single notification
     */
    public function show(Request $request, string $id): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $notification = Notification::find((int) $id);
        
        if ($notification === null || $notification->user_id != $userId) {
            if ($request->expectsJson()) {
                return Response::error('Notification not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Notification not found.');
            
            return Response::redirect('/account/notifications');
        }
        
        // Mark as read when viewing
        $notification->markAsRead();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $this->formatNotification($notification),
            ]);
        }
        
        return Response::view('notifications.show', [
            'title' => $notification->title ?? 'Notification',
            'notification' => $notification,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            return Response::error('Unauthorized', 401);
        }
        
        $result = $this->notificationService->markAsRead((int) $id, $userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/notifications');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            return Response::error('Unauthorized', 401);
        }
        
        $result = $this->notificationService->markAllAsRead($userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/notifications');
    }

    /**
     * Delete a notification
     */
    public function delete(Request $request, string $id): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            return Response::error('Unauthorized', 401);
        }
        
        $result = $this->notificationService->delete((int) $id, $userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/notifications');
    }

    /**
     * Show notification preferences
     */
    public function preferences(Request $request): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $preferences = $this->notificationService->getPreferences($userId);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $preferences->toArray(),
            ]);
        }
        
        return Response::view('notifications.preferences', [
            'title' => 'Notification Preferences',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            return Response::error('Unauthorized', 401);
        }
        
        $preferences = [
            'email_orders' => (bool) $request->input('email_orders', false),
            'email_promotions' => (bool) $request->input('email_promotions', false),
            'email_newsletter' => (bool) $request->input('email_newsletter', false),
            'sms_orders' => (bool) $request->input('sms_orders', false),
            'sms_promotions' => (bool) $request->input('sms_promotions', false),
            'push_enabled' => (bool) $request->input('push_enabled', false),
        ];
        
        $result = $this->notificationService->updatePreferences($userId, $preferences);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/notifications/preferences');
    }

    /**
     * Get unread count (API endpoint)
     */
    public function unreadCount(Request $request): Response
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            return Response::json(['success' => false, 'count' => 0], 401);
        }
        
        $count = $this->notificationService->getUnreadCount($userId);
        
        return Response::json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Format notification for response
     * 
     * @return array<string, mixed>
     */
    private function formatNotification(Notification $notification): array
    {
        return [
            'id' => $notification->getKey(),
            'type' => $notification->type ?? '',
            'type_label' => $notification->getTypeLabel(),
            'icon' => $notification->getIcon(),
            'title' => $notification->title ?? '',
            'message' => $notification->message ?? '',
            'data' => $notification->getData(),
            'is_read' => $notification->isRead(),
            'read_at' => $notification->read_at ?? null,
            'created_at' => $notification->created_at ?? null,
            'time_ago' => $notification->getTimeAgo(),
        ];
    }
}
