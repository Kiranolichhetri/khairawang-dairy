<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Core\Application;

/**
 * Invoice Service
 * 
 * Handles PDF invoice generation using HTML/CSS rendering.
 */
class InvoiceService
{
    /**
     * Generate invoice HTML for an order
     */
    public function generate(Order $order): string
    {
        $items = $order->itemsWithProducts();
        $company = config('app.company', []);
        
        $html = $this->getInvoiceTemplate();
        
        // Replace placeholders
        $replacements = [
            '{{COMPANY_NAME}}' => htmlspecialchars($company['name'] ?? 'KHAIRAWANG DAIRY'),
            '{{COMPANY_EMAIL}}' => htmlspecialchars($company['email'] ?? 'info@khairawangdairy.com'),
            '{{COMPANY_PHONE}}' => htmlspecialchars($company['phone'] ?? '+977-9800000000'),
            '{{COMPANY_ADDRESS}}' => htmlspecialchars($company['address'] ?? 'Kathmandu, Nepal'),
            '{{ORDER_NUMBER}}' => htmlspecialchars($order->attributes['order_number'] ?? ''),
            '{{ORDER_DATE}}' => date('F j, Y', strtotime($order->attributes['created_at'] ?? 'now')),
            '{{CUSTOMER_NAME}}' => htmlspecialchars($order->attributes['shipping_name'] ?? ''),
            '{{CUSTOMER_EMAIL}}' => htmlspecialchars($order->attributes['shipping_email'] ?? ''),
            '{{CUSTOMER_PHONE}}' => htmlspecialchars($order->attributes['shipping_phone'] ?? ''),
            '{{CUSTOMER_ADDRESS}}' => htmlspecialchars($order->attributes['shipping_address'] ?? ''),
            '{{CUSTOMER_CITY}}' => htmlspecialchars($order->attributes['shipping_city'] ?? ''),
            '{{ORDER_ITEMS}}' => $this->generateItemsHtml($items),
            '{{SUBTOTAL}}' => 'Rs. ' . number_format((float) ($order->attributes['subtotal'] ?? 0), 2),
            '{{SHIPPING}}' => 'Rs. ' . number_format((float) ($order->attributes['shipping_cost'] ?? 0), 2),
            '{{DISCOUNT}}' => 'Rs. ' . number_format((float) ($order->attributes['discount'] ?? 0), 2),
            '{{TOTAL}}' => 'Rs. ' . number_format((float) ($order->attributes['total'] ?? 0), 2),
            '{{PAYMENT_METHOD}}' => $this->formatPaymentMethod($order->attributes['payment_method'] ?? ''),
            '{{PAYMENT_STATUS}}' => $order->getPaymentStatus()->label(),
            '{{ORDER_STATUS}}' => $order->getStatus()->label(),
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    /**
     * Generate items table HTML
     * 
     * @param array<array<string, mixed>> $items
     */
    private function generateItemsHtml(array $items): string
    {
        $html = '';
        
        foreach ($items as $item) {
            $name = htmlspecialchars($item['product_name'] ?? '');
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $total = (float) ($item['total'] ?? 0);
            
            $html .= '<tr>';
            $html .= '<td>' . $name . '</td>';
            $html .= '<td class="text-center">' . $quantity . '</td>';
            $html .= '<td class="text-right">Rs. ' . number_format($price, 2) . '</td>';
            $html .= '<td class="text-right">Rs. ' . number_format($total, 2) . '</td>';
            $html .= '</tr>';
        }
        
        return $html;
    }

    /**
     * Format payment method for display
     */
    private function formatPaymentMethod(string $method): string
    {
        return match ($method) {
            'esewa' => 'eSewa',
            'khalti' => 'Khalti',
            'cod' => 'Cash on Delivery',
            default => ucfirst($method),
        };
    }

    /**
     * Get invoice HTML template
     */
    private function getInvoiceTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ORDER_NUMBER}}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            padding: 40px;
        }
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2e7d32;
        }
        .logo h1 {
            font-size: 28px;
            color: #2e7d32;
            margin-bottom: 5px;
        }
        .logo p {
            color: #666;
            font-size: 12px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-details p {
            margin: 3px 0;
        }
        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .address-box {
            width: 48%;
        }
        .address-box h3 {
            font-size: 14px;
            color: #2e7d32;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .address-box p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #2e7d32;
            color: #fff;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals tr td {
            padding: 8px 15px;
        }
        .totals tr:last-child {
            font-weight: bold;
            font-size: 16px;
            background: #f9f9f9;
        }
        .totals tr:last-child td {
            border-top: 2px solid #2e7d32;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-paid { background: #e8f5e9; color: #2e7d32; }
        .status-pending { background: #fff3e0; color: #e65100; }
        @media print {
            body { padding: 0; }
            .invoice { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <div class="logo">
                <h1>{{COMPANY_NAME}}</h1>
                <p>Premium Dairy Products</p>
                <p>{{COMPANY_ADDRESS}}</p>
                <p>{{COMPANY_PHONE}}</p>
                <p>{{COMPANY_EMAIL}}</p>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ORDER_NUMBER}}</p>
                <p><strong>Date:</strong> {{ORDER_DATE}}</p>
                <p><strong>Status:</strong> {{ORDER_STATUS}}</p>
                <p><strong>Payment:</strong> {{PAYMENT_STATUS}}</p>
            </div>
        </div>

        <div class="addresses">
            <div class="address-box">
                <h3>Bill To:</h3>
                <p><strong>{{CUSTOMER_NAME}}</strong></p>
                <p>{{CUSTOMER_ADDRESS}}</p>
                <p>{{CUSTOMER_CITY}}</p>
                <p>{{CUSTOMER_PHONE}}</p>
                <p>{{CUSTOMER_EMAIL}}</p>
            </div>
            <div class="address-box">
                <h3>Payment Method:</h3>
                <p>{{PAYMENT_METHOD}}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                {{ORDER_ITEMS}}
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{SUBTOTAL}}</td>
            </tr>
            <tr>
                <td>Shipping:</td>
                <td class="text-right">{{SHIPPING}}</td>
            </tr>
            <tr>
                <td>Discount:</td>
                <td class="text-right">-{{DISCOUNT}}</td>
            </tr>
            <tr>
                <td>Total:</td>
                <td class="text-right">{{TOTAL}}</td>
            </tr>
        </table>

        <div class="footer">
            <p>Thank you for shopping with {{COMPANY_NAME}}!</p>
            <p>For any queries, please contact us at {{COMPANY_EMAIL}} or {{COMPANY_PHONE}}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Download invoice as HTML (browser will render/print)
     * 
     * @return array<string, mixed>
     */
    public function download(Order $order): array
    {
        $html = $this->generate($order);
        
        return [
            'content' => $html,
            'filename' => 'invoice-' . $order->attributes['order_number'] . '.html',
            'content_type' => 'text/html',
        ];
    }
}
