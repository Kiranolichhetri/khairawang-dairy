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
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\OrderController;
use App\Controllers\EsewaController;
use App\Controllers\InvoiceController;

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

// ==================================================
// Product Routes
// ==================================================

$router->get('/products', [ProductController::class, 'index'], 'products.index');
$router->get('/products/search', [ProductController::class, 'search'], 'products.search');
$router->get('/products/featured', [ProductController::class, 'featured'], 'products.featured');
$router->get('/products/{slug}', [ProductController::class, 'show'], 'products.show');

// Categories
$router->get('/categories', function(Request $request) {
    return Response::json(['message' => 'Category listing']);
}, 'categories.index');

$router->get('/categories/{slug}', [ProductController::class, 'category'], 'categories.show');

// ==================================================
// Cart Routes
// ==================================================

$router->get('/cart', [CartController::class, 'index'], 'cart.index');
$router->post('/cart/add', [CartController::class, 'add'], 'cart.add');
$router->put('/cart/update/{id}', [CartController::class, 'update'], 'cart.update');
$router->delete('/cart/remove/{id}', [CartController::class, 'remove'], 'cart.remove');
$router->delete('/cart/clear', [CartController::class, 'clear'], 'cart.clear');
$router->post('/cart/sync', [CartController::class, 'sync'], 'cart.sync');
$router->get('/cart/count', [CartController::class, 'count'], 'cart.count');

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
    
    $router->get('/orders', [OrderController::class, 'index'], 'account.orders');
    $router->get('/orders/{orderNumber}', [OrderController::class, 'show'], 'account.orders.show');
    $router->get('/orders/{orderNumber}/track', [OrderController::class, 'track'], 'account.orders.track');
    $router->post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel'], 'account.orders.cancel');
    
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
    $router->get('/', [CheckoutController::class, 'index'], 'checkout.index');
    $router->post('/', [CheckoutController::class, 'process'], 'checkout.process');
    $router->get('/success/{orderNumber}', [CheckoutController::class, 'confirm'], 'checkout.success');
    $router->get('/validate-stock', [CheckoutController::class, 'validateStock'], 'checkout.validate');
    $router->get('/failed', function(Request $request) {
        $error = $request->query('error', 'Payment failed');
        $orderNumber = $request->query('order', '');
        return Response::json([
            'success' => false,
            'message' => $error,
            'order_number' => $orderNumber,
        ]);
    }, 'checkout.failed');
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

// ==================================================
// Payment Routes
// ==================================================

$router->group(['prefix' => '/payment'], function(Router $router) {
    // eSewa payment
    $router->post('/esewa/initiate', [EsewaController::class, 'initiate'], 'payment.esewa.initiate');
    $router->get('/esewa/success', [EsewaController::class, 'success'], 'payment.esewa.success');
    $router->get('/esewa/failure', [EsewaController::class, 'failure'], 'payment.esewa.failure');
    $router->post('/esewa/verify', [EsewaController::class, 'verify'], 'payment.esewa.verify');
    $router->get('/esewa/form', [EsewaController::class, 'form'], 'payment.esewa.form');
});

// ==================================================
// Invoice Routes
// ==================================================

$router->group(['prefix' => '/invoice'], function(Router $router) {
    $router->get('/{orderNumber}', [InvoiceController::class, 'view'], 'invoice.view');
    $router->get('/{orderNumber}/download', [InvoiceController::class, 'download'], 'invoice.download');
});
