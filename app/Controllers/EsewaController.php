<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\EsewaService;
use App\Services\OrderService;
use Core\Request;
use Core\Response;

/**
 * eSewa Payment Controller
 * 
 * Handles eSewa payment integration callbacks.
 */
class EsewaController
{
    private EsewaService $esewaService;
    private OrderService $orderService;

    public function __construct()
    {
        $this->esewaService = new EsewaService();
        $this->orderService = new OrderService();
    }

    /**
     * Initiate eSewa payment
     */
    public function initiate(Request $request): Response
    {
        // Validate required parameters
        $orderId = $request->input('order_id');
        $amount = $request->input('amount');
        $shipping = $request->input('shipping', 0);
        
        // Input validation
        if (empty($orderId)) {
            return Response::error('Order ID is required', 400);
        }
        
        if (!is_numeric($amount) || (float)$amount <= 0) {
            return Response::error('Valid amount is required', 400);
        }
        
        $amount = (float) $amount;
        $shipping = (float) $shipping;
        
        // Verify order exists
        $order = $this->orderService->getOrder($orderId);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        // Verify order is not already paid
        if (isset($order->attributes['payment_status']) && $order->attributes['payment_status'] === 'paid') {
            return Response::error('Order has already been paid', 400);
        }
        
        try {
            // Generate payment data
            $paymentData = $this->esewaService->initiatePayment(
                $orderId,
                $amount,
                0,
                0,
                $shipping
            );
            
            return Response::json([
                'success' => true,
                'payment_url' => $paymentData['payment_url'],
                'params' => $paymentData['params'],
                'signature' => $paymentData['signature'],
                'test_mode' => $paymentData['test_mode'],
            ]);
        } catch (\Exception $e) {
            return Response::error('Failed to initiate payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle eSewa success callback
     */
    public function success(Request $request): Response
    {
        // Extract and validate parameters
        $refId = $request->query('refId', '');
        $orderId = $request->query('oid', '');
        $amount = $request->query('amt', '');
        
        // Validate required parameters
        if (empty($refId)) {
            return Response::redirect('/checkout/failed?error=' . urlencode('Missing reference ID'));
        }
        
        if (empty($orderId)) {
            return Response::redirect('/checkout/failed?error=' . urlencode('Missing order ID'));
        }
        
        if (empty($amount) || !is_numeric($amount)) {
            return Response::redirect('/checkout/failed?error=' . urlencode('Invalid amount'));
        }
        
        $params = [
            'refId' => $refId,
            'oid' => $orderId,
            'amt' => $amount,
        ];
        
        try {
            $result = $this->orderService->handlePaymentSuccess('esewa', $params);
            
            if ($result['success']) {
                // Redirect to confirmation page
                return Response::redirect('/checkout/success/' . $result['order_number']);
            }
            
            // Redirect to failure page with error
            return Response::redirect('/checkout/failed?error=' . urlencode($result['message']));
        } catch (\Exception $e) {
            return Response::redirect('/checkout/failed?error=' . urlencode('Payment verification failed'));
        }
    }

    /**
     * Handle eSewa failure callback
     */
    public function failure(Request $request): Response
    {
        // Extract order ID
        $orderId = $request->query('oid', '');
        
        // Validate order ID
        if (empty($orderId)) {
            return Response::redirect('/checkout/failed?error=' . urlencode('Order not found'));
        }
        
        $params = [
            'oid' => $orderId,
        ];
        
        try {
            $result = $this->orderService->handlePaymentFailure('esewa', $params);
            
            // Redirect to failure page
            $errorMsg = urlencode($result['message'] ?? 'Payment cancelled');
            $orderNumber = $result['order_number'] ?? '';
            
            return Response::redirect("/checkout/failed?order={$orderNumber}&error={$errorMsg}");
        } catch (\Exception $e) {
            return Response::redirect('/checkout/failed?error=' . urlencode('Payment processing failed'));
        }
    }

    /**
     * Verify eSewa transaction
     */
    public function verify(Request $request): Response
    {
        // Extract parameters
        $refId = $request->input('reference_id', '');
        $orderId = $request->input('order_id', '');
        $amount = $request->input('amount', '');
        
        // Validate required parameters
        if (empty($refId)) {
            return Response::error('Reference ID is required', 400);
        }
        
        if (empty($orderId)) {
            return Response::error('Order ID is required', 400);
        }
        
        if (!is_numeric($amount) || (float)$amount <= 0) {
            return Response::error('Valid amount is required', 400);
        }
        
        $amount = (float) $amount;
        
        try {
            $result = $this->esewaService->verifyPayment($refId, $orderId, $amount);
            
            if ($result['success']) {
                return Response::json([
                    'success' => true,
                    'verified' => true,
                    'transaction_id' => $result['transaction_id'],
                    'order_id' => $result['order_id'],
                    'amount' => $result['amount'],
                ]);
            }
            
            return Response::json([
                'success' => false,
                'verified' => false,
                'message' => $result['message'],
            ], 400);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'verified' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment form HTML for eSewa
     */
    public function form(Request $request): Response
    {
        // Extract and validate parameters
        $orderId = $request->query('order_id', '');
        $amount = $request->query('amount', '');
        $shipping = $request->query('shipping', 0);
        
        // Validate required parameters
        if (empty($orderId)) {
            return Response::error('Order ID is required', 400);
        }
        
        if (!is_numeric($amount) || (float)$amount <= 0) {
            return Response::error('Valid amount is required', 400);
        }
        
        $amount = (float) $amount;
        $shipping = (float) $shipping;
        
        try {
            $paymentData = $this->esewaService->initiatePayment(
                $orderId,
                $amount,
                0,
                0,
                $shipping
            );
            
            // Generate auto-submit form
            $html = $this->generatePaymentForm($paymentData);
            
            return new Response($html);
        } catch (\Exception $e) {
            return Response::error('Failed to generate payment form: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate auto-submit payment form HTML
     * 
     * @param array<string, mixed> $data
     */
    private function generatePaymentForm(array $data): string
    {
        $url = htmlspecialchars($data['payment_url']);
        $params = $data['params'];
        
        $fields = '';
        foreach ($params as $key => $value) {
            $key = htmlspecialchars($key);
            $value = htmlspecialchars((string) $value);
            $fields .= "<input type=\"hidden\" name=\"{$key}\" value=\"{$value}\">\n";
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to eSewa...</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #60a917 0%, #2e7d32 100%);
        }
        .loader-container {
            text-align: center;
            color: white;
        }
        .loader {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        h2 {
            margin: 0 0 10px;
            font-weight: 400;
        }
        p {
            margin: 0;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <h2>Redirecting to eSewa</h2>
        <p>Please wait while we redirect you to the payment gateway...</p>
    </div>
    <form id="esewaForm" action="{$url}" method="POST" style="display:none;">
        {$fields}
    </form>
    <script>
        document.getElementById('esewaForm').submit();
    </script>
</body>
</html>
HTML;
    }
}
