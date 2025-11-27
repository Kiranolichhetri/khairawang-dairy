<?php
/**
 * Newsletter Email Template
 * KHAIRAWANG DAIRY
 * 
 * Variables: $subscriber (array), $campaign (array), $unsubscribeUrl (string)
 */
$subscriberName = htmlspecialchars($subscriber['name'] ?? 'Valued Customer', ENT_QUOTES, 'UTF-8');
$content_html = $campaign['content'] ?? '';
$unsubscribeLink = htmlspecialchars($unsubscribeUrl ?? '', ENT_QUOTES, 'UTF-8');

ob_start();
?>

<p>Dear <?= $subscriberName ?>,</p>

<?= $content_html ?>

<hr>

<div class="text-center">
    <a href="<?= htmlspecialchars(url('/products'), ENT_QUOTES, 'UTF-8') ?>" class="btn">
        Shop Now
    </a>
</div>

<hr>

<p class="text-muted text-center" style="font-size: 12px;">
    You are receiving this email because you subscribed to our newsletter.<br>
    <a href="<?= $unsubscribeLink ?>" style="color: #666;">Click here to unsubscribe</a>
</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
?>
