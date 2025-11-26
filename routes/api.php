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

/** @var Router $router */

// ==================================================
// API v1 Routes
// ==================================================

$router->group(['prefix' => '/api/v1'], function(Router $router) {
    
    // Products API
    $router->get('/products', function(Request $request) {
        return Response::json([
            'success' => true,
            'data' => [],
            'meta' => [
                'total' => 0,
                'per_page' => 15,
                'current_page' => 1,
            ],
        ]);
    }, 'api.products.index');
    
    $router->get('/products/{id}', function(Request $request, string $id) {
        return Response::json([
            'success' => true,
            'data' => ['id' => $id],
        ]);
    }, 'api.products.show');
    
    $router->get('/products/search', function(Request $request) {
        $query = $request->query('q', '');
        return Response::json([
            'success' => true,
            'data' => [],
            'query' => $query,
        ]);
    }, 'api.products.search');
    
    // Categories API
    $router->get('/categories', function(Request $request) {
        return Response::json([
            'success' => true,
            'data' => [],
        ]);
    }, 'api.categories.index');
    
    $router->get('/categories/{slug}', function(Request $request, string $slug) {
        return Response::json([
            'success' => true,
            'data' => ['slug' => $slug],
        ]);
    }, 'api.categories.show');
    
    // Cart API
    $router->get('/cart', function(Request $request) {
        return Response::json([
            'success' => true,
            'data' => [
                'items' => [],
                'subtotal' => 0,
                'count' => 0,
            ],
        ]);
    }, 'api.cart.index');
    
    $router->post('/cart/items', function(Request $request) {
        return Response::json([
            'success' => true,
            'message' => 'Item added to cart',
        ], 201);
    }, 'api.cart.add');
    
    $router->put('/cart/items/{id}', function(Request $request, string $id) {
        return Response::json([
            'success' => true,
            'message' => 'Cart item updated',
        ]);
    }, 'api.cart.update');
    
    $router->delete('/cart/items/{id}', function(Request $request, string $id) {
        return Response::json([
            'success' => true,
            'message' => 'Cart item removed',
        ]);
    }, 'api.cart.remove');
    
    // Newsletter subscription
    $router->post('/newsletter/subscribe', function(Request $request) {
        return Response::json([
            'success' => true,
            'message' => 'Successfully subscribed to newsletter',
        ]);
    }, 'api.newsletter.subscribe');
    
    // Contact form
    $router->post('/contact', function(Request $request) {
        return Response::json([
            'success' => true,
            'message' => 'Message sent successfully',
        ]);
    }, 'api.contact');
});

// ==================================================
// Payment Callbacks (excluded from CSRF)
// ==================================================

$router->group(['prefix' => '/api/payment'], function(Router $router) {
    // eSewa callback
    $router->get('/esewa/callback', function(Request $request) {
        return Response::json(['status' => 'received']);
    }, 'api.payment.esewa.callback');
    
    $router->post('/esewa/verify', function(Request $request) {
        return Response::json(['verified' => true]);
    }, 'api.payment.esewa.verify');
    
    // Khalti callback
    $router->get('/khalti/callback', function(Request $request) {
        return Response::json(['status' => 'received']);
    }, 'api.payment.khalti.callback');
    
    $router->post('/khalti/verify', function(Request $request) {
        return Response::json(['verified' => true]);
    }, 'api.payment.khalti.verify');
});
