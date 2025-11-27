<?php
/**
 * SEO Meta Tags Partial
 * 
 * @var array $seo SEO meta data array
 */
$seo = $seo ?? [];
?>

<!-- Primary Meta Tags -->
<title><?= htmlspecialchars($seo['title'] ?? 'KHAIRAWANG DAIRY') ?></title>
<meta name="title" content="<?= htmlspecialchars($seo['title'] ?? 'KHAIRAWANG DAIRY') ?>">
<meta name="description" content="<?= htmlspecialchars($seo['description'] ?? 'Premium dairy products from Nepal') ?>">
<?php if (!empty($seo['keywords'])): ?>
<meta name="keywords" content="<?= htmlspecialchars($seo['keywords']) ?>">
<?php endif; ?>

<!-- Canonical URL -->
<?php if (!empty($seo['canonical'])): ?>
<link rel="canonical" href="<?= htmlspecialchars($seo['canonical']) ?>">
<?php endif; ?>

<!-- Open Graph / Facebook -->
<meta property="og:type" content="<?= htmlspecialchars($seo['og:type'] ?? 'website') ?>">
<meta property="og:url" content="<?= htmlspecialchars($seo['og:url'] ?? '') ?>">
<meta property="og:title" content="<?= htmlspecialchars($seo['og:title'] ?? $seo['title'] ?? 'KHAIRAWANG DAIRY') ?>">
<meta property="og:description" content="<?= htmlspecialchars($seo['og:description'] ?? $seo['description'] ?? '') ?>">
<meta property="og:image" content="<?= htmlspecialchars($seo['og:image'] ?? '/assets/images/og-image.jpg') ?>">
<meta property="og:site_name" content="<?= htmlspecialchars($seo['og:site_name'] ?? 'KHAIRAWANG DAIRY') ?>">
<meta property="og:locale" content="en_US">

<!-- Twitter -->
<meta name="twitter:card" content="<?= htmlspecialchars($seo['twitter:card'] ?? 'summary_large_image') ?>">
<meta name="twitter:url" content="<?= htmlspecialchars($seo['og:url'] ?? '') ?>">
<meta name="twitter:title" content="<?= htmlspecialchars($seo['twitter:title'] ?? $seo['title'] ?? 'KHAIRAWANG DAIRY') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($seo['twitter:description'] ?? $seo['description'] ?? '') ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($seo['twitter:image'] ?? $seo['og:image'] ?? '/assets/images/og-image.jpg') ?>">

<!-- Additional Meta -->
<meta name="robots" content="index, follow">
<meta name="language" content="English">
<meta name="revisit-after" content="7 days">
<meta name="author" content="KHAIRAWANG DAIRY">
