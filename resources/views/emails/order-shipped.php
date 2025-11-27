<?php
/**
 * Order Shipped Email Template
 * KHAIRAWANG DAIRY
 * 
 * Variables: $order (array with order details)
 */
$orderNumber = htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8');
$customerName = htmlspecialchars($order['shipping_name'] ?? $order['name'] ?? 'Customer', ENT_QUOTES, 'UTF-8');

ob_start();
?>

<h2>Your Order Has Been Shipped! 📦</h2>

<p>Dear <?= $customerName ?>,</p>

<p>Great news! Your order <strong>#<?= $orderNumber ?></strong> has been shipped and is on its way to you.</p>

<div style="background-color: #e8f5e9; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #4caf50;">
    <h3 style="margin-top: 0; color: #2e7d32;">Order Status: Shipped</h3>
    <p style="margin-bottom: 0;">
        Your fresh dairy products are being delivered to:<br>
        <strong><?= htmlspecialchars($order['shipping_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
        <?php if (!empty($order['shipping_city'])): ?>
        <br><?= htmlspecialchars($order['shipping_city'], ENT_QUOTES, 'UTF-8') ?>
        <?php endif; ?>
    </p>
</div>

<h3>Expected Delivery</h3>
<p>
    Your order should arrive within <strong>1-2 business days</strong>. Our delivery team will contact you at 
    <strong><?= htmlspecialchars($order['shipping_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> 
    before delivery.
</p>

<h3>Delivery Instructions</h3>
<ul>
    <li>Please ensure someone is available to receive the order</li>
    <li>Keep dairy products refrigerated upon receipt</li>
    <li>Check all items before accepting delivery</li>
</ul>

<div class="text-center">
    <a href="<?= htmlspecialchars(url('/account/orders/' . ($order['order_number'] ?? '') . '/track'), ENT_QUOTES, 'UTF-8') ?>" class="btn">
        Track Delivery
    </a>
</div>

<hr>

<p class="text-muted">
    If you have any questions or need to reschedule delivery, please contact us immediately.
</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
?>
