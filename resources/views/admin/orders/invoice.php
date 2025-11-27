<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $view->e($title) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DM Sans', Arial, sans-serif;
            color: #201916;
            background: #fff;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FD7C44;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            font-size: 28px;
            color: #FD7C44;
        }
        .invoice-info p {
            color: #666;
            margin-top: 5px;
        }
        .addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .address h4 {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .address p {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f5f5f5;
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-left: auto;
            width: 300px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .totals-row.total {
            font-weight: 700;
            font-size: 18px;
            color: #FD7C44;
            border-bottom: none;
            border-top: 2px solid #201916;
            padding-top: 15px;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #FD7C44; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 500;">
            Print Invoice
        </button>
        <a href="/admin/orders/<?= $order['id'] ?>" style="margin-left: 10px; color: #666;">Back to Order</a>
    </div>

    <div class="header">
        <div class="logo">
            🥛 KHAIRAWANG DAIRY
        </div>
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <p>#<?= $view->e($order['order_number']) ?></p>
            <p><?= date('F j, Y', strtotime($order['created_at'])) ?></p>
        </div>
    </div>

    <div class="addresses">
        <div class="address">
            <h4>Bill From</h4>
            <p><strong>KHAIRAWANG DAIRY</strong></p>
            <p>Kathmandu, Nepal</p>
            <p>info@khairawangdairy.com</p>
        </div>
        <div class="address">
            <h4>Bill To</h4>
            <p><strong><?= $view->e($order['shipping_name']) ?></strong></p>
            <p><?= $view->e($order['shipping_address']) ?></p>
            <?php if (!empty($order['shipping_city'])): ?>
                <p><?= $view->e($order['shipping_city']) ?></p>
            <?php endif; ?>
            <p><?= $view->e($order['shipping_email']) ?></p>
            <p><?= $view->e($order['shipping_phone']) ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Price</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td>
                        <strong><?= $view->e($item['product_name']) ?></strong>
                        <?php if (!empty($item['variant_name'])): ?>
                            <br><small style="color: #666;"><?= $view->e($item['variant_name']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">Rs. <?= number_format($item['price'], 2) ?></td>
                    <td class="text-right"><?= $item['quantity'] ?></td>
                    <td class="text-right">Rs. <?= number_format($item['total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span>Rs. <?= number_format($order['subtotal'], 2) ?></span>
        </div>
        <div class="totals-row">
            <span>Shipping</span>
            <span>Rs. <?= number_format($order['shipping_cost'], 2) ?></span>
        </div>
        <?php if ($order['discount'] > 0): ?>
            <div class="totals-row">
                <span>Discount</span>
                <span>-Rs. <?= number_format($order['discount'], 2) ?></span>
            </div>
        <?php endif; ?>
        <div class="totals-row total">
            <span>Total</span>
            <span>Rs. <?= number_format($order['total'], 2) ?></span>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
        <p style="margin-bottom: 5px;"><strong>Payment Method:</strong> <?= strtoupper($view->e($order['payment_method'])) ?></p>
        <p><strong>Payment Status:</strong> <?= $view->e($order['payment_status_label']) ?></p>
    </div>

    <div class="footer">
        <p>Thank you for your order!</p>
        <p style="margin-top: 10px;">KHAIRAWANG DAIRY - Fresh From Farm To Table</p>
    </div>
</body>
</html>
