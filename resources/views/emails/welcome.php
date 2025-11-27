<?php
/**
 * Welcome Email Template
 * KHAIRAWANG DAIRY
 * 
 * Variables: $user (array with user details)
 */
$userName = htmlspecialchars($user['name'] ?? 'Customer', ENT_QUOTES, 'UTF-8');

ob_start();
?>

<h2>Welcome to KHAIRAWANG DAIRY! 🎉</h2>

<p>Dear <?= $userName ?>,</p>

<p>Thank you for joining the KHAIRAWANG DAIRY family! We're thrilled to have you as a member of our community.</p>

<div style="background-color: #fce4ec; padding: 20px; border-radius: 6px; margin: 20px 0; text-align: center;">
    <h3 style="margin-top: 0; color: #c2185b;">🥛 Fresh Dairy, Delivered to You</h3>
    <p style="margin-bottom: 0;">
        Experience the finest dairy products from local farms, delivered fresh to your doorstep.
    </p>
</div>

<h3>What You Can Do Now</h3>
<ul>
    <li><strong>Browse Products:</strong> Explore our range of fresh milk, cheese, yogurt, and more</li>
    <li><strong>Save Favorites:</strong> Add products to your wishlist for quick access</li>
    <li><strong>Easy Checkout:</strong> Enjoy fast and secure payment options</li>
    <li><strong>Track Orders:</strong> Monitor your deliveries in real-time</li>
</ul>

<h3>Our Promise</h3>
<ul>
    <li>✓ 100% Fresh dairy products</li>
    <li>✓ Quality guaranteed</li>
    <li>✓ Fast delivery across Nepal</li>
    <li>✓ Excellent customer support</li>
</ul>

<div class="text-center">
    <a href="<?= htmlspecialchars(url('/products'), ENT_QUOTES, 'UTF-8') ?>" class="btn">
        Start Shopping
    </a>
</div>

<hr>

<p>
    Have questions? Our support team is here to help. Reach us at 
    <a href="mailto:support@khairawangdairy.com">support@khairawangdairy.com</a>.
</p>

<p class="text-center text-muted">
    Welcome aboard! 🙏
</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
?>
