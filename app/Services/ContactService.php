<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactInquiry;
use Core\Application;

/**
 * Contact Service
 * 
 * Handles contact form submissions and inquiry management.
 */
class ContactService
{
    private EmailService $emailService;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
    }

    /**
     * Submit a contact inquiry
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function submit(array $data): array
    {
        // Validate required fields
        $required = ['name', 'email', 'subject', 'message'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Validate phone if provided (Nepal format)
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                return ['success' => false, 'message' => 'Invalid phone number'];
            }
            $data['phone'] = $phone;
        }
        
        // Create inquiry
        $inquiry = ContactInquiry::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'new',
        ]);
        
        // Notify admin
        $this->notifyAdmin($inquiry);
        
        return [
            'success' => true,
            'message' => 'Your inquiry has been submitted. We will get back to you soon!',
            'inquiry_id' => $inquiry->getKey(),
        ];
    }

    /**
     * Get all inquiries with optional filters
     * 
     * @param array<string, mixed> $filters
     * @return array<ContactInquiry>
     */
    public function getInquiries(array $filters = []): array
    {
        $query = ContactInquiry::query();
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                  ->orWhere('email', 'LIKE', $search)
                  ->orWhere('subject', 'LIKE', $search);
            });
        }
        
        return $query->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get inquiry by ID
     */
    public function getInquiry(int $id): ?ContactInquiry
    {
        return ContactInquiry::find($id);
    }

    /**
     * Reply to an inquiry
     * 
     * @return array<string, mixed>
     */
    public function reply(int $inquiryId, string $message): array
    {
        $inquiry = ContactInquiry::find($inquiryId);
        
        if ($inquiry === null) {
            return ['success' => false, 'message' => 'Inquiry not found'];
        }
        
        // Update inquiry with reply
        $inquiry->admin_reply = $message;
        $inquiry->replied_at = date('Y-m-d H:i:s');
        $inquiry->status = 'in_progress';
        $inquiry->save();
        
        // Send reply email
        $sent = $this->sendReplyEmail($inquiry, $message);
        
        return [
            'success' => true,
            'message' => $sent ? 'Reply sent successfully' : 'Reply saved but email failed to send',
            'email_sent' => $sent,
        ];
    }

    /**
     * Mark inquiry as resolved
     * 
     * @return array<string, mixed>
     */
    public function markAsResolved(int $inquiryId): array
    {
        $inquiry = ContactInquiry::find($inquiryId);
        
        if ($inquiry === null) {
            return ['success' => false, 'message' => 'Inquiry not found'];
        }
        
        $inquiry->status = 'resolved';
        $inquiry->save();
        
        return ['success' => true, 'message' => 'Inquiry marked as resolved'];
    }

    /**
     * Get inquiry statistics
     * 
     * @return array<string, int>
     */
    public function getStats(): array
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return ['new' => 0, 'in_progress' => 0, 'resolved' => 0, 'total' => 0];
        }
        
        $stats = $db->selectOne("
            SELECT 
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
                COUNT(*) as total
            FROM contact_inquiries
        ");
        
        return [
            'new' => (int) ($stats['new_count'] ?? 0),
            'in_progress' => (int) ($stats['in_progress_count'] ?? 0),
            'resolved' => (int) ($stats['resolved_count'] ?? 0),
            'total' => (int) ($stats['total'] ?? 0),
        ];
    }

    /**
     * Delete an inquiry
     * 
     * @return array<string, mixed>
     */
    public function delete(int $inquiryId): array
    {
        $inquiry = ContactInquiry::find($inquiryId);
        
        if ($inquiry === null) {
            return ['success' => false, 'message' => 'Inquiry not found'];
        }
        
        $inquiry->delete();
        
        return ['success' => true, 'message' => 'Inquiry deleted'];
    }

    /**
     * Notify admin about new inquiry
     */
    private function notifyAdmin(ContactInquiry $inquiry): bool
    {
        $app = Application::getInstance();
        $adminEmail = $app?->config('mail.from.address', 'admin@khairawangdairy.com');
        
        $subject = 'New Contact Inquiry: ' . ($inquiry->subject ?? 'No Subject');
        
        $message = "
            <h2>New Contact Inquiry</h2>
            <p><strong>From:</strong> {$inquiry->name} ({$inquiry->email})</p>
            <p><strong>Phone:</strong> {$inquiry->phone}</p>
            <p><strong>Subject:</strong> {$inquiry->subject}</p>
            <p><strong>Message:</strong></p>
            <p>{$inquiry->message}</p>
            <hr>
            <p>View in admin panel: " . url('/admin/contacts/' . $inquiry->getKey()) . "</p>
        ";
        
        return $this->emailService->send($adminEmail, $subject, 'emails/admin-notification', [
            'inquiry' => $inquiry->toArray(),
            'content' => $message,
        ]);
    }

    /**
     * Send reply email to customer
     */
    private function sendReplyEmail(ContactInquiry $inquiry, string $replyMessage): bool
    {
        $subject = 'Re: ' . ($inquiry->subject ?? 'Your Inquiry') . ' - KHAIRAWANG DAIRY';
        
        $message = "
            <h2>Thank you for contacting KHAIRAWANG DAIRY</h2>
            <p>Dear {$inquiry->name},</p>
            <p>Thank you for your inquiry. Here is our response:</p>
            <blockquote style='border-left: 3px solid #ccc; padding-left: 10px; margin: 10px 0;'>
                {$replyMessage}
            </blockquote>
            <hr>
            <p><strong>Your original message:</strong></p>
            <p>{$inquiry->message}</p>
            <hr>
            <p>If you have any further questions, please don't hesitate to reach out.</p>
            <p>Best regards,<br>KHAIRAWANG DAIRY Team</p>
        ";
        
        return $this->emailService->send(
            $inquiry->email,
            $subject,
            'emails/contact-reply',
            [
                'inquiry' => $inquiry->toArray(),
                'reply' => $replyMessage,
                'content' => $message,
            ]
        );
    }
}
