<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KHAIRAWANG DAIRY - Premium fresh dairy products delivered from our farm to your table.">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🥛</text></svg>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via CDN (for development) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'accent-orange': '#FD7C44',
                        'accent-orange-dark': '#e56a35',
                        'dark-brown': '#201916',
                        'cream': '#F7EFDF',
                        'light-gray': '#F5F5F5',
                    },
                    fontFamily: {
                        'heading': ['Poppins', 'sans-serif'],
                        'body': ['DM Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft-lg': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05)',
                    },
                }
            }
        }
    </script>
    
    <title><?= $view->yield('title', 'Authentication') ?> - KHAIRAWANG DAIRY</title>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-body text-dark-brown bg-cream antialiased">
    <?= $view->yield('content') ?>
</body>
</html>
