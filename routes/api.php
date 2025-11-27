<?php

declare(strict_types=1);

/**
 * API Routes
 * 
 * Define all API routes for the application.
 * These routes are prefixed with /api automatically.
 */

use Core\Router;
use Core\Request;
use Core\Response;
use App\Models\Category;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\OrderController;
use App\Controllers\EsewaController;
use App\Controllers\InvoiceController;

/** @var Router $router */

// ==================================================
// API v1 Routes
// ==================================================

$router->group(['prefix' => '/api/v1'], function(Router $router) {
    
    // Products API
    $router->get('/products', [ProductController::class, 'index'], 'api.products.index');
    $router->get('/products/search', [ProductController::class, 'search'], 'api.products.search');
    $router->get('/products/featured', [ProductController::class, 'featured'], 'api.products.featured');
    $router->get('/products/category/{slug}', [ProductController::class, 'category'], 'api.products.category');
    $router->get('/products/{slug}', [ProductController::class, 'show'], 'api.products.show');
    
    // Categories API
    $router->get('/categories', function(Request $request) {
        $categories = Category::roots();
        return Response::json([
            'success' => true,
            'data' => array_map(function($cat) {
                return [
                    'id' => $cat->getKey(),
                    'name' => $cat->getName(),
                    'name_ne' => $cat->getName('ne'),
                    'slug' => $cat->attributes['slug'],
                    'image' => $cat->getImageUrl(),
                    'product_count' => $cat->getProductCount(),
                ];
            }, $categories),
        ]);
    }, 'api.categories.index');
    
    $router->get('/categories/{slug}', [ProductController::class, 'category'], 'api.categories.show');
    
    // Cart API
    $router->get('/cart', [CartController::class, 'index'], 'api.cart.index');
    $router->post('/cart/items', [CartController::class, 'add'], 'api.cart.add');
    $router->put('/cart/items/{id}', [CartController::class, 'update'], 'api.cart.update');
    $router->delete('/cart/items/{id}', [CartController::class, 'remove'], 'api.cart.remove');
    $router->delete('/cart/clear', [CartController::class, 'clear'], 'api.cart.clear');
    $router->post('/cart/sync', [CartController::class, 'sync'], 'api.cart.sync');
    $router->get('/cart/count', [CartController::class, 'count'], 'api.cart.count');
    
    // Checkout API
    $router->get('/checkout', [CheckoutController::class, 'index'], 'api.checkout.index');
    $router->post('/checkout', [CheckoutController::class, 'process'], 'api.checkout.process');
    $router->get('/checkout/validate', [CheckoutController::class, 'validateStock'], 'api.checkout.validate');
    
    // Orders API
    $router->get('/orders', [OrderController::class, 'index'], 'api.orders.index');
    $router->get('/orders/{orderNumber}', [OrderController::class, 'show'], 'api.orders.show');
    $router->get('/orders/{orderNumber}/track', [OrderController::class, 'track'], 'api.orders.track');
    $router->post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel'], 'api.orders.cancel');
    
    // Newsletter subscription
    $router->post('/newsletter/subscribe', [\App\Controllers\NewsletterController::class, 'subscribe'], 'api.newsletter.subscribe');
    
    // Contact form
    $router->post('/contact', [\App\Controllers\ContactController::class, 'submit'], 'api.contact');
    
    // Notifications API
    $router->get('/notifications', [\App\Controllers\NotificationController::class, 'index'], 'api.notifications');
    $router->post('/notifications/{id}/read', [\App\Controllers\NotificationController::class, 'markAsRead'], 'api.notifications.read');
    $router->get('/notifications/unread-count', [\App\Controllers\NotificationController::class, 'unreadCount'], 'api.notifications.unread-count');
});

// ==================================================
// Payment Callbacks (excluded from CSRF)
// ==================================================

$router->group(['prefix' => '/api/payment'], function(Router $router) {
    // eSewa callback
    $router->get('/esewa/success', [EsewaController::class, 'success'], 'api.payment.esewa.success');
    $router->get('/esewa/failure', [EsewaController::class, 'failure'], 'api.payment.esewa.failure');
    $router->post('/esewa/verify', [EsewaController::class, 'verify'], 'api.payment.esewa.verify');
    
    // Khalti callback (placeholder for future implementation)
    $router->get('/khalti/callback', function(Request $request) {
        return Response::json(['status' => 'received']);
    }, 'api.payment.khalti.callback');
    
    $router->post('/khalti/verify', function(Request $request) {
        return Response::json(['verified' => true]);
    }, 'api.payment.khalti.verify');
});
