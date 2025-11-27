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
        $orderId = $request->input('order_id');
        $amount = (float) $request->input('amount', 0);
        $shipping = (float) $request->input('shipping', 0);
        
        if (empty($orderId) || $amount <= 0) {
            return Response::error('Invalid order or amount', 400);
        }
        
        // Verify order exists
        $order = $this->orderService->getOrder($orderId);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
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
        ]);
    }

    /**
     * Handle eSewa success callback
     */
    public function success(Request $request): Response
    {
        $params = [
            'refId' => $request->query('refId', ''),
            'oid' => $request->query('oid', ''),
            'amt' => $request->query('amt', ''),
        ];
        
        $result = $this->orderService->handlePaymentSuccess('esewa', $params);
        
        if ($result['success']) {
            // Redirect to confirmation page
            return Response::redirect('/checkout/success/' . $result['order_number']);
        }
        
        // Redirect to failure page with error
        return Response::redirect('/checkout/failed?error=' . urlencode($result['message']));
    }

    /**
     * Handle eSewa failure callback
     */
    public function failure(Request $request): Response
    {
        $params = [
            'oid' => $request->query('oid', ''),
        ];
        
        $result = $this->orderService->handlePaymentFailure('esewa', $params);
        
        // Redirect to failure page
        $errorMsg = urlencode($result['message']);
        $orderNumber = $result['order_number'] ?? '';
        
        return Response::redirect("/checkout/failed?order={$orderNumber}&error={$errorMsg}");
    }

    /**
     * Verify eSewa transaction
     */
    public function verify(Request $request): Response
    {
        $refId = $request->input('reference_id', '');
        $orderId = $request->input('order_id', '');
        $amount = (float) $request->input('amount', 0);
        
        if (empty($refId) || empty($orderId) || $amount <= 0) {
            return Response::error('Missing required parameters', 400);
        }
        
        $result = $this->esewaService->verifyPayment($refId, $orderId, $amount);
        
        if ($result['success']) {
            return Response::json([
                'success' => true,
                'verified' => true,
                'transaction_id' => $result['transaction_id'],
            ]);
        }
        
        return Response::json([
            'success' => false,
            'verified' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Get payment form HTML for eSewa
     */
    public function form(Request $request): Response
    {
        $orderId = $request->query('order_id', '');
        $amount = (float) $request->query('amount', 0);
        $shipping = (float) $request->query('shipping', 0);
        
        if (empty($orderId) || $amount <= 0) {
            return Response::error('Invalid parameters', 400);
        }
        
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
