<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Invoice Controller
 * 
 * Handles invoice generation and download.
 */
class InvoiceController
{
    private InvoiceService $invoiceService;

    public function __construct()
    {
        $this->invoiceService = new InvoiceService();
    }

    /**
     * Generate invoice HTML for an order
     */
    public function generate(Request $request, string $orderNumber): Response
    {
        $order = Order::findByOrderNumber($orderNumber);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        // Check authorization
        if (!$this->canAccessOrder($order)) {
            return Response::error('Unauthorized', 403);
        }
        
        $html = $this->invoiceService->generate($order);
        
        return new Response($html);
    }

    /**
     * Download invoice
     */
    public function download(Request $request, string $orderNumber): Response
    {
        $order = Order::findByOrderNumber($orderNumber);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        // Check authorization
        if (!$this->canAccessOrder($order)) {
            return Response::error('Unauthorized', 403);
        }
        
        $invoice = $this->invoiceService->download($order);
        
        $response = new Response($invoice['content']);
        $response->header('Content-Type', $invoice['content_type']);
        $response->header('Content-Disposition', 'attachment; filename="' . $invoice['filename'] . '"');
        
        return $response;
    }

    /**
     * View invoice in browser
     */
    public function view(Request $request, string $orderNumber): Response
    {
        $order = Order::findByOrderNumber($orderNumber);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        // Check authorization
        if (!$this->canAccessOrder($order)) {
            return Response::error('Unauthorized', 403);
        }
        
        $html = $this->invoiceService->generate($order);
        
        $response = new Response($html);
        $response->header('Content-Type', 'text/html; charset=UTF-8');
        
        return $response;
    }

    /**
     * Check if current user can access order
     */
    private function canAccessOrder(Order $order): bool
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        $userRole = $session?->get('user')['role'] ?? null;
        
        // Admin can access all orders
        if ($userRole === 'admin' || $userRole === 'manager') {
            return true;
        }
        
        // User can access their own orders
        if ($userId !== null && $order->attributes['user_id'] === $userId) {
            return true;
        }
        
        // Guest can access order if email matches (for guest orders)
        // Use hash_equals to prevent timing attacks
        $userEmail = $session?->get('user')['email'] ?? '';
        $orderEmail = $order->attributes['shipping_email'] ?? '';
        if (!empty($userEmail) && !empty($orderEmail) && hash_equals($orderEmail, $userEmail)) {
            return true;
        }
        
        // Allow access if order was just created (same session)
        $lastOrderId = $session?->get('last_order_id');
        if ($lastOrderId !== null && $order->getKey() === $lastOrderId) {
            return true;
        }
        
        return false;
    }
}
