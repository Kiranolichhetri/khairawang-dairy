<?php
/**
 * Order Delivered Email Template
 * KHAIRAWANG DAIRY
 * 
 * Variables: $order (array with order details)
 */
$orderNumber = htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8');
$customerName = htmlspecialchars($order['shipping_name'] ?? $order['name'] ?? 'Customer', ENT_QUOTES, 'UTF-8');

ob_start();
?>

<h2>Your Order Has Been Delivered! ✅</h2>

<p>Dear <?= $customerName ?>,</p>

<p>Your order <strong>#<?= $orderNumber ?></strong> has been successfully delivered. We hope you enjoy our fresh dairy products!</p>

<div style="background-color: #e3f2fd; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2196f3;">
    <h3 style="margin-top: 0; color: #1565c0;">Order Delivered</h3>
    <p style="margin-bottom: 0;">
        Thank you for choosing KHAIRAWANG DAIRY! Your fresh dairy products are now at your doorstep.
    </p>
</div>

<h3>How to Store Your Dairy Products</h3>
<ul>
    <li>Store milk and cream at 2-4°C (35-39°F)</li>
    <li>Keep cheese wrapped and in a sealed container</li>
    <li>Consume products before their expiration date</li>
    <li>Keep yogurt refrigerated at all times</li>
</ul>

<h3>We'd Love Your Feedback!</h3>
<p>
    Your opinion matters to us. Please take a moment to rate your experience and help us serve you better.
</p>

<div class="text-center">
    <a href="<?= htmlspecialchars(url('/products'), ENT_QUOTES, 'UTF-8') ?>" class="btn">
        Leave a Review
    </a>
</div>

<hr>

<h3>Need Help?</h3>
<p>
    If you have any issues with your order or need assistance, please don't hesitate to contact our support team:
</p>
<ul>
    <li>Email: support@khairawangdairy.com</li>
    <li>Phone: +977-9800000000</li>
</ul>

<p class="text-center" style="font-size: 20px; margin-top: 30px;">
    🙏 Thank you for choosing KHAIRAWANG DAIRY!
</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
?>
