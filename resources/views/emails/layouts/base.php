<?php
/**
 * Base Email Layout
 * KHAIRAWANG DAIRY
 * 
 * Variables available: $content (required), $title (optional)
 */
$title = $title ?? 'KHAIRAWANG DAIRY';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #8B4513;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0;
            color: #f5deb3;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .footer a {
            color: #8B4513;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #8B4513;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 15px 0;
        }
        .btn:hover {
            background-color: #6d3710;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #666666;
        }
        hr {
            border: none;
            border-top: 1px solid #eeeeee;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eeeeee;
        }
        th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>🥛 KHAIRAWANG DAIRY</h1>
                <p>Premium Fresh Dairy Products</p>
            </div>
            <div class="content">
                <?= $content ?? '' ?>
            </div>
            <div class="footer">
                <p>
                    &copy; <?= date('Y') ?> KHAIRAWANG DAIRY. All rights reserved.
                </p>
                <p>
                    <a href="<?= htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') ?>">Visit our website</a> |
                    <a href="<?= htmlspecialchars(url('/contact'), ENT_QUOTES, 'UTF-8') ?>">Contact Us</a>
                </p>
                <p class="text-muted">
                    Kathmandu, Nepal | +977-9800000000
                </p>
            </div>
        </div>
    </div>
</body>
</html>
