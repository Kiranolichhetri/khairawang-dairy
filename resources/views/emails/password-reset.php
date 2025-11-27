<?php
/**
 * Password Reset Email Template
 * KHAIRAWANG DAIRY
 * 
 * Variables: $user (array), $resetUrl (string), $token (string)
 */
$userName = htmlspecialchars($user['name'] ?? 'Customer', ENT_QUOTES, 'UTF-8');
$resetLink = htmlspecialchars($resetUrl ?? '', ENT_QUOTES, 'UTF-8');

ob_start();
?>

<h2>Reset Your Password</h2>

<p>Dear <?= $userName ?>,</p>

<p>We received a request to reset the password for your KHAIRAWANG DAIRY account.</p>

<div style="background-color: #fff3e0; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #ff9800;">
    <p style="margin: 0;">
        <strong>⚠️ This link will expire in 1 hour.</strong>
    </p>
</div>

<p>Click the button below to reset your password:</p>

<div class="text-center">
    <a href="<?= $resetLink ?>" class="btn">
        Reset Password
    </a>
</div>

<p class="text-muted" style="margin-top: 20px;">
    Or copy and paste this link into your browser:<br>
    <small style="word-break: break-all;"><?= $resetLink ?></small>
</p>

<hr>

<h3>Didn't Request This?</h3>
<p>
    If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.
</p>

<p class="text-muted">
    For security reasons, this link can only be used once. If you need to reset your password again, 
    please visit our website and request a new reset link.
</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
?>
