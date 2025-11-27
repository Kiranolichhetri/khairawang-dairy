<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\ContactService;
use App\Models\ContactInquiry;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin Contact Controller
 * 
 * Handles contact inquiry management in admin panel.
 */
class ContactController
{
    private ContactService $contactService;

    public function __construct(?ContactService $contactService = null)
    {
        $this->contactService = $contactService ?? new ContactService();
    }

    /**
     * List all inquiries
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
        $sql = "SELECT * FROM contact_inquiries WHERE 1=1";
        $params = [];
        
        if ($status && in_array($status, ['new', 'in_progress', 'resolved'])) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Get total count
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*) as count', $sql);
        $countResult = $db->selectOne($countSql, $params);
        $total = (int) ($countResult['count'] ?? 0);
        
        // Add ordering and pagination
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        
        $inquiriesData = $db->select($sql, $params);
        
        // Hydrate inquiries
        $inquiries = [];
        foreach ($inquiriesData as $row) {
            $inquiry = ContactInquiry::find($row['id']);
            if ($inquiry !== null) {
                $inquiries[] = $inquiry;
            }
        }
        
        // Get stats
        $stats = $this->contactService->getStats();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'inquiries' => array_map(fn($i) => $this->formatInquiry($i), $inquiries),
                ],
                'stats' => $stats,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }
        
        return Response::view('admin.contacts.index', [
            'title' => 'Contact Inquiries',
            'inquiries' => $inquiries,
            'stats' => $stats,
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
     * Show inquiry details
     */
    public function show(Request $request, string $id): Response
    {
        $inquiry = ContactInquiry::find((int) $id);
        
        if ($inquiry === null) {
            if ($request->expectsJson()) {
                return Response::error('Inquiry not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Inquiry not found.');
            
            return Response::redirect('/admin/contacts');
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $this->formatInquiry($inquiry),
            ]);
        }
        
        return Response::view('admin.contacts.show', [
            'title' => 'Inquiry from ' . ($inquiry->name ?? ''),
            'inquiry' => $inquiry,
        ]);
    }

    /**
     * Reply to inquiry
     */
    public function reply(Request $request, string $id): Response
    {
        $validator = new Validator($request->all(), [
            'message' => 'required|min:10|max:5000',
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
            
            return Response::redirect('/admin/contacts/' . $id);
        }
        
        $result = $this->contactService->reply((int) $id, $request->input('message', '') ?? '');
        
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
        
        return Response::redirect('/admin/contacts/' . $id);
    }

    /**
     * Mark inquiry as resolved
     */
    public function resolve(Request $request, string $id): Response
    {
        $result = $this->contactService->markAsResolved((int) $id);
        
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
        
        return Response::redirect('/admin/contacts/' . $id);
    }

    /**
     * Delete inquiry
     */
    public function delete(Request $request, string $id): Response
    {
        $result = $this->contactService->delete((int) $id);
        
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
        
        return Response::redirect('/admin/contacts');
    }

    /**
     * Format inquiry for response
     * 
     * @return array<string, mixed>
     */
    private function formatInquiry(ContactInquiry $inquiry): array
    {
        return [
            'id' => $inquiry->getKey(),
            'name' => $inquiry->name ?? '',
            'email' => $inquiry->email ?? '',
            'phone' => $inquiry->phone ?? '',
            'subject' => $inquiry->subject ?? '',
            'message' => $inquiry->message ?? '',
            'status' => $inquiry->status ?? '',
            'status_label' => $inquiry->getStatusLabel(),
            'status_color' => $inquiry->getStatusColor(),
            'admin_reply' => $inquiry->admin_reply ?? '',
            'replied_at' => $inquiry->replied_at ?? null,
            'has_reply' => $inquiry->hasReply(),
            'created_at' => $inquiry->created_at ?? null,
        ];
    }
}
