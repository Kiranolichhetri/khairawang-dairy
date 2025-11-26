<?php

declare(strict_types=1);

/**
 * Web Routes
 * 
 * Define all web routes for the application.
 * The $router variable is available in this file.
 */

use Core\Router;
use Core\Request;
use Core\Response;

/** @var Router $router */

// ==================================================
// Public Routes
// ==================================================

// Home page
$router->get('/', function(Request $request) {
    return new Response('<h1>Welcome to KHAIRAWANG DAIRY</h1><p>Premium Dairy Products</p>');
}, 'home');

// Health check
$router->get('/health', function() {
    return Response::json([
        'status' => 'ok',
        'timestamp' => date('c'),
    ]);
}, 'health');

// Products
$router->get('/products', function(Request $request) {
    return Response::json(['message' => 'Product listing']);
}, 'products.index');

$router->get('/products/{slug}', function(Request $request, string $slug) {
    return Response::json(['message' => "Product: {$slug}"]);
}, 'products.show');

// Categories
$router->get('/categories', function(Request $request) {
    return Response::json(['message' => 'Category listing']);
}, 'categories.index');

$router->get('/categories/{slug}', function(Request $request, string $slug) {
    return Response::json(['message' => "Category: {$slug}"]);
}, 'categories.show');

// Cart
$router->get('/cart', function(Request $request) {
    return Response::json(['message' => 'Shopping cart']);
}, 'cart.index');

$router->post('/cart/add', function(Request $request) {
    return Response::json(['message' => 'Item added to cart']);
}, 'cart.add');

// ==================================================
// Authentication Routes
// ==================================================

$router->get('/login', function(Request $request) {
    return new Response('<h1>Login</h1><form method="POST"><button>Login</button></form>');
}, 'login');

$router->post('/login', function(Request $request) {
    return Response::redirect('/dashboard');
}, 'login.post');

$router->get('/register', function(Request $request) {
    return new Response('<h1>Register</h1><form method="POST"><button>Register</button></form>');
}, 'register');

$router->post('/register', function(Request $request) {
    return Response::redirect('/login');
}, 'register.post');

$router->post('/logout', function(Request $request) {
    return Response::redirect('/');
}, 'logout');

// ==================================================
// Protected Routes (require authentication)
// ==================================================

$router->group(['prefix' => '/account', 'middleware' => [\App\Middleware\AuthMiddleware::class]], function(Router $router) {
    $router->get('/', function(Request $request) {
        return Response::json(['message' => 'Account dashboard']);
    }, 'account.dashboard');
    
    $router->get('/orders', function(Request $request) {
        return Response::json(['message' => 'Order history']);
    }, 'account.orders');
    
    $router->get('/orders/{id}', function(Request $request, string $id) {
        return Response::json(['message' => "Order #{$id}"]);
    }, 'account.orders.show');
    
    $router->get('/profile', function(Request $request) {
        return Response::json(['message' => 'Profile settings']);
    }, 'account.profile');
    
    $router->post('/profile', function(Request $request) {
        return Response::json(['message' => 'Profile updated']);
    }, 'account.profile.update');
});

// ==================================================
// Checkout Routes
// ==================================================

$router->group(['prefix' => '/checkout'], function(Router $router) {
    $router->get('/', function(Request $request) {
        return Response::json(['message' => 'Checkout page']);
    }, 'checkout.index');
    
    $router->post('/', function(Request $request) {
        return Response::json(['message' => 'Order placed']);
    }, 'checkout.process');
    
    $router->get('/success/{order}', function(Request $request, string $order) {
        return Response::json(['message' => "Order {$order} confirmed"]);
    }, 'checkout.success');
});

// ==================================================
// Admin Routes
// ==================================================

$router->group([
    'prefix' => '/admin',
    'middleware' => [
        \App\Middleware\AuthMiddleware::class,
        \App\Middleware\AdminMiddleware::class,
    ],
], function(Router $router) {
    // Dashboard
    $router->get('/', function(Request $request) {
        return Response::json(['message' => 'Admin dashboard']);
    }, 'admin.dashboard');
    
    // Products management
    $router->get('/products', function(Request $request) {
        return Response::json(['message' => 'Products list']);
    }, 'admin.products.index');
    
    $router->get('/products/create', function(Request $request) {
        return Response::json(['message' => 'Create product form']);
    }, 'admin.products.create');
    
    $router->post('/products', function(Request $request) {
        return Response::json(['message' => 'Product created']);
    }, 'admin.products.store');
    
    $router->get('/products/{id}/edit', function(Request $request, string $id) {
        return Response::json(['message' => "Edit product {$id}"]);
    }, 'admin.products.edit');
    
    $router->put('/products/{id}', function(Request $request, string $id) {
        return Response::json(['message' => "Product {$id} updated"]);
    }, 'admin.products.update');
    
    $router->delete('/products/{id}', function(Request $request, string $id) {
        return Response::json(['message' => "Product {$id} deleted"]);
    }, 'admin.products.delete');
    
    // Orders management
    $router->get('/orders', function(Request $request) {
        return Response::json(['message' => 'Orders list']);
    }, 'admin.orders.index');
    
    $router->get('/orders/{id}', function(Request $request, string $id) {
        return Response::json(['message' => "Order {$id} details"]);
    }, 'admin.orders.show');
    
    // Categories management
    $router->get('/categories', function(Request $request) {
        return Response::json(['message' => 'Categories list']);
    }, 'admin.categories.index');
    
    // Users management
    $router->get('/users', function(Request $request) {
        return Response::json(['message' => 'Users list']);
    }, 'admin.users.index');
});

// ==================================================
// Static Pages
// ==================================================

$router->get('/about', function(Request $request) {
    return new Response('<h1>About KHAIRAWANG DAIRY</h1>');
}, 'about');

$router->get('/contact', function(Request $request) {
    return new Response('<h1>Contact Us</h1>');
}, 'contact');

$router->post('/contact', function(Request $request) {
    return Response::json(['message' => 'Message sent']);
}, 'contact.post');

$router->get('/terms', function(Request $request) {
    return new Response('<h1>Terms & Conditions</h1>');
}, 'terms');

$router->get('/privacy', function(Request $request) {
    return new Response('<h1>Privacy Policy</h1>');
}, 'privacy');
