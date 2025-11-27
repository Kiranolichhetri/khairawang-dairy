<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\NewsletterService;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterCampaign;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin Newsletter Controller
 * 
 * Handles newsletter management in admin panel.
 */
class NewsletterController
{
    private NewsletterService $newsletterService;

    public function __construct(?NewsletterService $newsletterService = null)
    {
        $this->newsletterService = $newsletterService ?? new NewsletterService();
    }

    /**
     * List all subscribers
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $status = $request->query('status');
        $search = trim($request->query('q', '') ?? '');
        
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::error('Database connection error', 500);
        }
        
        // Build query
        $sql = "SELECT * FROM newsletter_subscribers WHERE 1=1";
        $params = [];
        
        if ($status === 'active') {
            $sql .= " AND is_active = 1";
        } elseif ($status === 'unsubscribed') {
            $sql .= " AND is_active = 0";
        }
        
        if (!empty($search)) {
            $sql .= " AND (email LIKE ? OR name LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Get total count
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*) as count', $sql);
        $countResult = $db->selectOne($countSql, $params);
        $total = (int) ($countResult['count'] ?? 0);
        
        // Add ordering and pagination
        $sql .= " ORDER BY subscribed_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        
        $subscribers = $db->select($sql, $params);
        
        // Get stats
        $activeCount = $this->newsletterService->getSubscriberCount(true);
        $totalCount = $this->newsletterService->getSubscriberCount(false);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'subscribers' => $subscribers,
                ],
                'meta' => [
                    'total' => $total,
                    'active' => $activeCount,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }
        
        return Response::view('admin.newsletter.index', [
            'title' => 'Newsletter Subscribers',
            'subscribers' => $subscribers,
            'activeCount' => $activeCount,
            'totalCount' => $totalCount,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Delete a subscriber
     */
    public function deleteSubscriber(Request $request, string $id): Response
    {
        $subscriber = NewsletterSubscriber::find((int) $id);
        
        if ($subscriber === null) {
            if ($request->expectsJson()) {
                return Response::error('Subscriber not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Subscriber not found.');
            
            return Response::redirect('/admin/newsletter');
        }
        
        $subscriber->delete();
        
        if ($request->expectsJson()) {
            return Response::json(['success' => true, 'message' => 'Subscriber deleted']);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Subscriber deleted.');
        
        return Response::redirect('/admin/newsletter');
    }

    /**
     * Export subscribers as CSV
     */
    public function export(Request $request): Response
    {
        $activeOnly = $request->query('active_only', true);
        
        $result = $this->newsletterService->exportSubscribers((bool) $activeOnly);
        
        if (!$result['success']) {
            return Response::error($result['message'] ?? 'Export failed', 500);
        }
        
        $response = new Response($result['content'] ?? '');
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-Disposition', 'attachment; filename="' . ($result['filename'] ?? 'subscribers.csv') . '"');
        
        return $response;
    }

    /**
     * List campaigns
     */
    public function campaigns(Request $request): Response
    {
        $campaigns = NewsletterCampaign::getAll();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => array_map(fn($c) => $c->toArray(), $campaigns),
            ]);
        }
        
        return Response::view('admin.newsletter.campaigns', [
            'title' => 'Newsletter Campaigns',
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show create campaign form
     */
    public function createCampaign(Request $request): Response
    {
        return Response::view('admin.newsletter.create-campaign', [
            'title' => 'Create Campaign',
        ]);
    }

    /**
     * Store new campaign
     */
    public function storeCampaign(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'subject' => 'required|min:3|max:255',
            'content' => 'required|min:10',
        ]);
        
        if (!$validator->validate()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->setFlash('errors', $validator->errors());
            $session?->setFlash('old_input', $request->all());
            
            return Response::redirect('/admin/newsletter/campaigns/create');
        }
        
        $result = $this->newsletterService->createCampaign([
            'subject' => trim($request->input('subject', '') ?? ''),
            'content' => $request->input('content', '') ?? '',
            'status' => $request->input('status', 'draft') ?? 'draft',
        ]);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 201 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
            return Response::redirect('/admin/newsletter/campaigns');
        }
        
        $session?->error($result['message']);
        $session?->setFlash('old_input', $request->all());
        
        return Response::redirect('/admin/newsletter/campaigns/create');
    }

    /**
     * Send campaign
     */
    public function sendCampaign(Request $request, string $id): Response
    {
        $campaign = NewsletterCampaign::find((int) $id);
        
        if ($campaign === null) {
            if ($request->expectsJson()) {
                return Response::error('Campaign not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Campaign not found.');
            
            return Response::redirect('/admin/newsletter/campaigns');
        }
        
        if ($campaign->isSent()) {
            if ($request->expectsJson()) {
                return Response::error('Campaign has already been sent', 400);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('This campaign has already been sent.');
            
            return Response::redirect('/admin/newsletter/campaigns');
        }
        
        $result = $this->newsletterService->sendCampaign($campaign);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/admin/newsletter/campaigns');
    }

    /**
     * Delete campaign
     */
    public function deleteCampaign(Request $request, string $id): Response
    {
        $campaign = NewsletterCampaign::find((int) $id);
        
        if ($campaign === null) {
            if ($request->expectsJson()) {
                return Response::error('Campaign not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Campaign not found.');
            
            return Response::redirect('/admin/newsletter/campaigns');
        }
        
        $campaign->delete();
        
        if ($request->expectsJson()) {
            return Response::json(['success' => true, 'message' => 'Campaign deleted']);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Campaign deleted.');
        
        return Response::redirect('/admin/newsletter/campaigns');
    }
}
