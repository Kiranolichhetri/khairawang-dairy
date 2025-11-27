<?php
/**
 * Order Confirmation Email Template
 * KHAIRAWANG DAIRY
 * 
 * Variables: $order (array with order details)
 */
$orderNumber = htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8');
$customerName = htmlspecialchars($order['shipping_name'] ?? $order['name'] ?? 'Customer', ENT_QUOTES, 'UTF-8');
$total = number_format((float) ($order['total'] ?? 0), 2);
$subtotal = number_format((float) ($order['subtotal'] ?? 0), 2);
$shipping = number_format((float) ($order['shipping_cost'] ?? 0), 2);
$paymentMethod = htmlspecialchars($order['payment_method'] ?? 'Cash on Delivery', ENT_QUOTES, 'UTF-8');

ob_start();
?>

<h2>Order Confirmation</h2>

<p>Dear <?= $customerName ?>,</p>

<p>Thank you for your order! We're excited to prepare your fresh dairy products.</p>

<div style="background-color: #f5f5f5; padding: 20px; border-radius: 6px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Order #<?= $orderNumber ?></h3>
    <p style="margin-bottom: 0;">
        <strong>Status:</strong> Confirmed<br>
        <strong>Payment Method:</strong> <?= $paymentMethod ?>
    </p>
</div>

<?php if (!empty($order['items']) && is_array($order['items'])): ?>
<h3>Order Items</h3>
<table>
    <thead>
        <tr>
            <th>Product</th>
            <th style="text-align: right;">Qty</th>
            <th style="text-align: right;">Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($order['items'] as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['product_name'] ?? $item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align: right;"><?= (int) ($item['quantity'] ?? 1) ?></td>
            <td style="text-align: right;">Rs. <?= number_format((float) ($item['total'] ?? $item['price'] ?? 0), 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<table style="margin-top: 20px;">
    <tr>
        <td><strong>Subtotal:</strong></td>
        <td style="text-align: right;">Rs. <?= $subtotal ?></td>
    </tr>
    <tr>
        <td><strong>Shipping:</strong></td>
        <td style="text-align: right;">Rs. <?= $shipping ?></td>
    </tr>
    <tr style="font-size: 18px;">
        <td><strong>Total:</strong></td>
        <td style="text-align: right;"><strong>Rs. <?= $total ?></strong></td>
    </tr>
</table>

<h3>Delivery Address</h3>
<p>
    <?= htmlspecialchars($order['shipping_address'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
    <?php if (!empty($order['shipping_city'])): ?>
    <?= htmlspecialchars($order['shipping_city'], ENT_QUOTES, 'UTF-8') ?><br>
    <?php endif; ?>
    Phone: <?= htmlspecialchars($order['shipping_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
</p>

<div class="text-center">
    <a href="<?= htmlspecialchars(url('/account/orders/' . ($order['order_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" class="btn">
        Track Your Order
    </a>
</div>

<hr>

<p class="text-muted">
    If you have any questions about your order, please don't hesitate to contact us.
</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
?>
