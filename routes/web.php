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
use App\Controllers\AuthController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Admin\ReportController as AdminReportController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;

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

// Guest-only routes (login, register, forgot password)
$router->group(['middleware' => [\App\Middleware\GuestMiddleware::class]], function(Router $router) {
    $router->get('/login', [AuthController::class, 'showLogin'], 'login');
    $router->post('/login', [AuthController::class, 'login'], 'login.post');
    $router->get('/register', [AuthController::class, 'showRegister'], 'register');
    $router->post('/register', [AuthController::class, 'register'], 'register.post');
    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], 'password.request');
    $router->post('/forgot-password', [AuthController::class, 'forgotPassword'], 'password.email');
    $router->get('/reset-password/{token}', [AuthController::class, 'showResetPassword'], 'password.reset');
    $router->post('/reset-password', [AuthController::class, 'resetPassword'], 'password.update');
});

// Email verification (accessible always)
$router->get('/verify-email/{token}', [AuthController::class, 'verifyEmail'], 'verification.verify');

// Logout (requires authentication)
$router->post('/logout', [AuthController::class, 'logout'], 'logout');

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
    $router->get('/', [AdminDashboardController::class, 'index'], 'admin.dashboard');
    $router->get('/stats', [AdminDashboardController::class, 'getStats'], 'admin.stats');
    $router->get('/sales-chart', [AdminDashboardController::class, 'getSalesChart'], 'admin.sales-chart');
    
    // Products management
    $router->get('/products', [AdminProductController::class, 'index'], 'admin.products.index');
    $router->get('/products/create', [AdminProductController::class, 'create'], 'admin.products.create');
    $router->post('/products', [AdminProductController::class, 'store'], 'admin.products.store');
    $router->get('/products/{id}/edit', [AdminProductController::class, 'edit'], 'admin.products.edit');
    $router->put('/products/{id}', [AdminProductController::class, 'update'], 'admin.products.update');
    $router->delete('/products/{id}', [AdminProductController::class, 'delete'], 'admin.products.delete');
    $router->post('/products/{id}/toggle-status', [AdminProductController::class, 'toggleStatus'], 'admin.products.toggle');
    $router->post('/products/upload-image', [AdminProductController::class, 'uploadImage'], 'admin.products.upload');
    
    // Categories management
    $router->get('/categories', [AdminCategoryController::class, 'index'], 'admin.categories.index');
    $router->get('/categories/create', [AdminCategoryController::class, 'create'], 'admin.categories.create');
    $router->post('/categories', [AdminCategoryController::class, 'store'], 'admin.categories.store');
    $router->get('/categories/{id}/edit', [AdminCategoryController::class, 'edit'], 'admin.categories.edit');
    $router->put('/categories/{id}', [AdminCategoryController::class, 'update'], 'admin.categories.update');
    $router->delete('/categories/{id}', [AdminCategoryController::class, 'delete'], 'admin.categories.delete');
    
    // Orders management
    $router->get('/orders', [AdminOrderController::class, 'index'], 'admin.orders.index');
    $router->get('/orders/export', [AdminOrderController::class, 'export'], 'admin.orders.export');
    $router->get('/orders/{id}', [AdminOrderController::class, 'show'], 'admin.orders.show');
    $router->post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'], 'admin.orders.status');
    $router->get('/orders/{id}/invoice', [AdminOrderController::class, 'printInvoice'], 'admin.orders.invoice');
    $router->post('/orders/{id}/note', [AdminOrderController::class, 'addNote'], 'admin.orders.note');
    
    // Users management
    $router->get('/users', [AdminUserController::class, 'index'], 'admin.users.index');
    $router->get('/users/{id}', [AdminUserController::class, 'show'], 'admin.users.show');
    $router->get('/users/{id}/edit', [AdminUserController::class, 'edit'], 'admin.users.edit');
    $router->put('/users/{id}', [AdminUserController::class, 'update'], 'admin.users.update');
    $router->post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'], 'admin.users.toggle');
    $router->delete('/users/{id}', [AdminUserController::class, 'delete'], 'admin.users.delete');
    
    // Reports
    $router->get('/reports/sales', [AdminReportController::class, 'sales'], 'admin.reports.sales');
    $router->get('/reports/products', [AdminReportController::class, 'products'], 'admin.reports.products');
    $router->get('/reports/customers', [AdminReportController::class, 'customers'], 'admin.reports.customers');
    $router->get('/reports/inventory', [AdminReportController::class, 'inventory'], 'admin.reports.inventory');
    $router->get('/reports/export/{type}', [AdminReportController::class, 'export'], 'admin.reports.export');
    
    // Settings
    $router->get('/settings', [AdminSettingsController::class, 'index'], 'admin.settings.index');
    $router->post('/settings', [AdminSettingsController::class, 'update'], 'admin.settings.update');
    $router->get('/settings/{key}', [AdminSettingsController::class, 'get'], 'admin.settings.get');
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
