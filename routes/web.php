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
use App\Controllers\ProfileController;
use App\Controllers\AddressController;
use App\Controllers\WishlistController;
use App\Controllers\ReviewController;
use App\Controllers\Account\OrderController as AccountOrderController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Admin\ReportController as AdminReportController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\ContactController;
use App\Controllers\NewsletterController;
use App\Controllers\NotificationController;
use App\Controllers\CouponController;
use App\Controllers\BlogController;
use App\Controllers\SeoController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Controllers\Admin\ContactController as AdminContactController;
use App\Controllers\Admin\CouponController as AdminCouponController;
use App\Controllers\Admin\BlogController as AdminBlogController;
use App\Controllers\Admin\InventoryController as AdminInventoryController;

/** @var Router $router */

// ==================================================
// Public Routes
// ==================================================

// Home page
$router->get('/', [HomeController::class, 'index'], 'home');

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

// Coupon Routes (Cart)
$router->post('/cart/coupon/apply', [CouponController::class, 'apply'], 'cart.coupon.apply');
$router->delete('/cart/coupon/remove', [CouponController::class, 'remove'], 'cart.coupon.remove');
$router->post('/cart/coupon/validate', [CouponController::class, 'validate'], 'cart.coupon.validate');

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
    // Dashboard
    $router->get('/', [ProfileController::class, 'dashboard'], 'account.dashboard');
    
    // Profile Management
    $router->get('/profile', [ProfileController::class, 'show'], 'account.profile');
    $router->get('/profile/edit', [ProfileController::class, 'edit'], 'account.profile.edit');
    $router->post('/profile', [ProfileController::class, 'update'], 'account.profile.update');
    $router->get('/password', [ProfileController::class, 'changePassword'], 'account.password');
    $router->post('/password', [ProfileController::class, 'updatePassword'], 'account.password.update');
    $router->post('/avatar', [ProfileController::class, 'uploadAvatar'], 'account.avatar.upload');
    $router->delete('/avatar', [ProfileController::class, 'deleteAvatar'], 'account.avatar.delete');
    $router->delete('/delete', [ProfileController::class, 'deleteAccount'], 'account.delete');
    
    // Address Management
    $router->get('/addresses', [AddressController::class, 'index'], 'account.addresses');
    $router->get('/addresses/create', [AddressController::class, 'create'], 'account.addresses.create');
    $router->post('/addresses', [AddressController::class, 'store'], 'account.addresses.store');
    $router->get('/addresses/{id}/edit', [AddressController::class, 'edit'], 'account.addresses.edit');
    $router->put('/addresses/{id}', [AddressController::class, 'update'], 'account.addresses.update');
    $router->delete('/addresses/{id}', [AddressController::class, 'delete'], 'account.addresses.delete');
    $router->post('/addresses/{id}/default', [AddressController::class, 'setDefault'], 'account.addresses.default');
    
    // Wishlist Management
    $router->get('/wishlist', [WishlistController::class, 'index'], 'account.wishlist');
    $router->post('/wishlist/{productId}', [WishlistController::class, 'add'], 'account.wishlist.add');
    $router->delete('/wishlist/{productId}', [WishlistController::class, 'remove'], 'account.wishlist.remove');
    $router->post('/wishlist/{productId}/toggle', [WishlistController::class, 'toggle'], 'account.wishlist.toggle');
    $router->post('/wishlist/{productId}/move-to-cart', [WishlistController::class, 'moveToCart'], 'account.wishlist.move');
    $router->delete('/wishlist/clear', [WishlistController::class, 'clear'], 'account.wishlist.clear');
    
    // Order Management
    $router->get('/orders', [AccountOrderController::class, 'index'], 'account.orders');
    $router->get('/orders/{orderNumber}', [AccountOrderController::class, 'show'], 'account.orders.show');
    $router->get('/orders/{orderNumber}/track', [AccountOrderController::class, 'track'], 'account.orders.track');
    $router->post('/orders/{orderNumber}/cancel', [AccountOrderController::class, 'cancel'], 'account.orders.cancel');
    $router->post('/orders/{orderNumber}/reorder', [AccountOrderController::class, 'reorder'], 'account.orders.reorder');
    $router->get('/orders/{orderNumber}/invoice', [AccountOrderController::class, 'downloadInvoice'], 'account.orders.invoice');
    
    // Notification Management
    $router->get('/notifications', [NotificationController::class, 'index'], 'account.notifications');
    $router->get('/notifications/preferences', [NotificationController::class, 'preferences'], 'account.notifications.preferences');
    $router->post('/notifications/preferences', [NotificationController::class, 'updatePreferences'], 'account.notifications.preferences.update');
    $router->post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'], 'account.notifications.read-all');
    $router->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'], 'account.notifications.read');
    $router->delete('/notifications/{id}', [NotificationController::class, 'delete'], 'account.notifications.delete');
});

// ==================================================
// Wishlist API Routes (for AJAX)
// ==================================================

$router->get('/api/wishlist/count', [WishlistController::class, 'count'], 'api.wishlist.count');
$router->get('/api/wishlist/{productId}/check', [WishlistController::class, 'check'], 'api.wishlist.check');
$router->post('/api/wishlist/sync', [WishlistController::class, 'sync'], 'api.wishlist.sync');

// ==================================================
// Review Routes
// ==================================================

$router->get('/products/{slug}/reviews', [ReviewController::class, 'index'], 'reviews.index');
$router->post('/products/{slug}/reviews', [ReviewController::class, 'store'], 'reviews.store');
$router->get('/reviews/{id}/edit', [ReviewController::class, 'edit'], 'reviews.edit');
$router->put('/reviews/{id}', [ReviewController::class, 'update'], 'reviews.update');
$router->delete('/reviews/{id}', [ReviewController::class, 'delete'], 'reviews.delete');
$router->post('/reviews/{id}/helpful', [ReviewController::class, 'helpful'], 'reviews.helpful');

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
    
    // Newsletter management
    $router->get('/newsletter', [AdminNewsletterController::class, 'index'], 'admin.newsletter.index');
    $router->get('/newsletter/export', [AdminNewsletterController::class, 'export'], 'admin.newsletter.export');
    $router->delete('/newsletter/subscribers/{id}', [AdminNewsletterController::class, 'deleteSubscriber'], 'admin.newsletter.subscribers.delete');
    $router->get('/newsletter/campaigns', [AdminNewsletterController::class, 'campaigns'], 'admin.newsletter.campaigns');
    $router->get('/newsletter/campaigns/create', [AdminNewsletterController::class, 'createCampaign'], 'admin.newsletter.campaigns.create');
    $router->post('/newsletter/campaigns', [AdminNewsletterController::class, 'storeCampaign'], 'admin.newsletter.campaigns.store');
    $router->post('/newsletter/campaigns/{id}/send', [AdminNewsletterController::class, 'sendCampaign'], 'admin.newsletter.campaigns.send');
    $router->delete('/newsletter/campaigns/{id}', [AdminNewsletterController::class, 'deleteCampaign'], 'admin.newsletter.campaigns.delete');
    
    // Contact inquiries management
    $router->get('/contacts', [AdminContactController::class, 'index'], 'admin.contacts.index');
    $router->get('/contacts/{id}', [AdminContactController::class, 'show'], 'admin.contacts.show');
    $router->post('/contacts/{id}/reply', [AdminContactController::class, 'reply'], 'admin.contacts.reply');
    $router->post('/contacts/{id}/resolve', [AdminContactController::class, 'resolve'], 'admin.contacts.resolve');
    $router->delete('/contacts/{id}', [AdminContactController::class, 'delete'], 'admin.contacts.delete');
    
    // Coupons management
    $router->get('/coupons', [AdminCouponController::class, 'index'], 'admin.coupons.index');
    $router->get('/coupons/create', [AdminCouponController::class, 'create'], 'admin.coupons.create');
    $router->post('/coupons', [AdminCouponController::class, 'store'], 'admin.coupons.store');
    $router->get('/coupons/generate-code', [AdminCouponController::class, 'generateCode'], 'admin.coupons.generate-code');
    $router->get('/coupons/{id}/edit', [AdminCouponController::class, 'edit'], 'admin.coupons.edit');
    $router->put('/coupons/{id}', [AdminCouponController::class, 'update'], 'admin.coupons.update');
    $router->delete('/coupons/{id}', [AdminCouponController::class, 'delete'], 'admin.coupons.delete');
    $router->post('/coupons/{id}/toggle-status', [AdminCouponController::class, 'toggleStatus'], 'admin.coupons.toggle');
    
    // Blog management
    $router->get('/blog', [AdminBlogController::class, 'index'], 'admin.blog.index');
    $router->get('/blog/create', [AdminBlogController::class, 'create'], 'admin.blog.create');
    $router->post('/blog', [AdminBlogController::class, 'store'], 'admin.blog.store');
    $router->get('/blog/categories', [AdminBlogController::class, 'categories'], 'admin.blog.categories');
    $router->post('/blog/categories', [AdminBlogController::class, 'storeCategory'], 'admin.blog.categories.store');
    $router->put('/blog/categories/{id}', [AdminBlogController::class, 'updateCategory'], 'admin.blog.categories.update');
    $router->delete('/blog/categories/{id}', [AdminBlogController::class, 'deleteCategory'], 'admin.blog.categories.delete');
    $router->get('/blog/{id}/edit', [AdminBlogController::class, 'edit'], 'admin.blog.edit');
    $router->put('/blog/{id}', [AdminBlogController::class, 'update'], 'admin.blog.update');
    $router->delete('/blog/{id}', [AdminBlogController::class, 'delete'], 'admin.blog.delete');
    $router->post('/blog/{id}/toggle-publish', [AdminBlogController::class, 'togglePublish'], 'admin.blog.toggle');
    
    // Inventory management
    $router->get('/inventory', [AdminInventoryController::class, 'index'], 'admin.inventory.index');
    $router->get('/inventory/low-stock', [AdminInventoryController::class, 'lowStock'], 'admin.inventory.low-stock');
    $router->get('/inventory/movements', [AdminInventoryController::class, 'movements'], 'admin.inventory.movements');
    $router->post('/inventory/{productId}/adjust', [AdminInventoryController::class, 'adjust'], 'admin.inventory.adjust');
    $router->post('/inventory/bulk-adjust', [AdminInventoryController::class, 'bulkAdjust'], 'admin.inventory.bulk-adjust');
    
    // Settings
    $router->get('/settings', [AdminSettingsController::class, 'index'], 'admin.settings.index');
    $router->post('/settings', [AdminSettingsController::class, 'update'], 'admin.settings.update');
    $router->get('/settings/{key}', [AdminSettingsController::class, 'get'], 'admin.settings.get');
});

// ==================================================
// Static Pages
// ==================================================

$router->get('/about', [PageController::class, 'about'], 'about');

// ==================================================
// Contact Routes
// ==================================================

$router->get('/contact', [ContactController::class, 'show'], 'contact');
$router->post('/contact', [ContactController::class, 'submit'], 'contact.submit');
$router->get('/contact/thank-you', [ContactController::class, 'thankYou'], 'contact.thank-you');

// ==================================================
// Newsletter Routes
// ==================================================

$router->post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'], 'newsletter.subscribe');
$router->get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'], 'newsletter.unsubscribe');

// ==================================================
// Blog Routes
// ==================================================

$router->get('/blog', [BlogController::class, 'index'], 'blog.index');
$router->get('/blog/search', [BlogController::class, 'search'], 'blog.search');
$router->get('/blog/category/{slug}', [BlogController::class, 'category'], 'blog.category');
$router->get('/blog/tag/{slug}', [BlogController::class, 'tag'], 'blog.tag');
$router->get('/blog/{slug}', [BlogController::class, 'show'], 'blog.show');

// ==================================================
// SEO Routes
// ==================================================

$router->get('/sitemap.xml', [SeoController::class, 'sitemap'], 'seo.sitemap');
$router->get('/robots.txt', [SeoController::class, 'robots'], 'seo.robots');

$router->get('/terms', [PageController::class, 'terms'], 'terms');

$router->get('/privacy', [PageController::class, 'privacy'], 'privacy');

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
