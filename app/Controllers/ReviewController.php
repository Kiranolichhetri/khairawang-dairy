<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Services\ReviewService;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Review Controller
 * 
 * Handles product reviews and ratings.
 */
class ReviewController
{
    private ReviewService $reviewService;

    public function __construct()
    {
        $this->reviewService = new ReviewService();
    }

    /**
     * Get current user ID from session
     */
    private function getUserId(): ?int
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        return $userId ? (int) $userId : null;
    }

    /**
     * List product reviews
     */
    public function index(Request $request, string $slug): Response
    {
        // Get product by slug
        $product = Product::findBy('slug', $slug);
        
        if ($product === null) {
            if ($request->expectsJson()) {
                return Response::error('Product not found', 404);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Product not found');
            return Response::redirect('/products');
        }
        
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(20, max(1, (int) $request->query('per_page', 10)));
        
        $productId = (int) $product->getKey();
        $reviewData = $this->reviewService->getProductReviews($productId, $page, $perPage);
        
        // Check if user can review
        $userId = $this->getUserId();
        $canReview = null;
        
        if ($userId !== null) {
            $canReview = $this->reviewService->canReviewProduct($userId, $productId);
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => array_merge($reviewData, [
                    'can_review' => $canReview,
                ]),
            ]);
        }
        
        // For non-JSON requests, redirect to product page
        return Response::redirect('/products/' . $slug . '#reviews');
    }

    /**
     * Submit a review
     */
    public function store(Request $request, string $slug): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Please login to submit a review',
                    'redirect' => '/login',
                ], 401);
            }
            
            $session = Application::getInstance()?->session();
            $session?->setIntendedUrl('/products/' . $slug);
            return Response::redirect('/login');
        }
        
        // Get product by slug
        $product = Product::findBy('slug', $slug);
        
        if ($product === null) {
            if ($request->expectsJson()) {
                return Response::error('Product not found', 404);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Product not found');
            return Response::redirect('/products');
        }
        
        $validator = new Validator($request->all(), [
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $session = Application::getInstance()?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/products/' . $slug . '#reviews');
        }
        
        $productId = (int) $product->getKey();
        
        $result = $this->reviewService->createReview($userId, $productId, [
            'rating' => $request->input('rating'),
            'title' => $request->input('title'),
            'comment' => $request->input('comment'),
            'images' => $_FILES['images'] ?? [],
        ]);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 201 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/products/' . $slug . '#reviews');
    }

    /**
     * Edit own review
     */
    public function edit(Request $request, string $reviewId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::redirect('/login');
        }
        
        $review = Review::find((int) $reviewId);
        
        if ($review === null) {
            $session = Application::getInstance()?->session();
            $session?->error('Review not found');
            return Response::redirect('/account/orders');
        }
        
        // Check ownership
        if ($review->attributes['user_id'] !== $userId) {
            $session = Application::getInstance()?->session();
            $session?->error('You can only edit your own reviews');
            return Response::redirect('/account/orders');
        }
        
        $product = Product::find($review->attributes['product_id']);
        
        return Response::view('reviews.edit', [
            'title' => 'Edit Review',
            'review' => $review->toArray(),
            'product' => $product ? $product->toArray() : null,
        ]);
    }

    /**
     * Update review
     */
    public function update(Request $request, string $reviewId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $validator = new Validator($request->all(), [
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $session = Application::getInstance()?->session();
            $session?->flashErrors($validator->errors());
            
            return Response::redirect('/reviews/' . $reviewId . '/edit');
        }
        
        $result = $this->reviewService->updateReview((int) $reviewId, $userId, [
            'rating' => $request->input('rating'),
            'title' => $request->input('title'),
            'comment' => $request->input('comment'),
        ]);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
            return Response::redirect('/account/orders');
        } else {
            $session?->error($result['message']);
            return Response::redirect('/reviews/' . $reviewId . '/edit');
        }
    }

    /**
     * Delete review
     */
    public function delete(Request $request, string $reviewId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->reviewService->deleteReview((int) $reviewId, $userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/orders');
    }

    /**
     * Mark review as helpful
     */
    public function helpful(Request $request, string $reviewId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Please login to mark reviews as helpful',
                ], 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->reviewService->markHelpful((int) $reviewId, $userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        // Redirect back
        $referer = $request->header('Referer') ?? '/';
        return Response::redirect($referer);
    }

    /**
     * Get user's reviews
     */
    public function myReviews(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $reviews = $this->reviewService->getUserReviews($userId);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $reviews,
            ]);
        }
        
        return Response::view('account.reviews.index', [
            'title' => 'My Reviews',
            'reviews' => $reviews,
        ]);
    }
}
