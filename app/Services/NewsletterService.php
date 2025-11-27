<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NewsletterSubscriber;
use App\Models\NewsletterCampaign;
use Core\Application;

/**
 * Newsletter Service
 * 
 * Handles newsletter subscription management and campaign sending.
 */
class NewsletterService
{
    private EmailService $emailService;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
    }

    /**
     * Subscribe email to newsletter
     * 
     * @return array<string, mixed>
     */
    public function subscribe(string $email, ?string $name = null): array
    {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Check if already subscribed
        $existing = NewsletterSubscriber::findByEmail($email);
        
        if ($existing !== null) {
            if ($existing->isActive()) {
                return ['success' => false, 'message' => 'Email already subscribed'];
            }
            
            // Reactivate subscription
            $existing->reactivate();
            return ['success' => true, 'message' => 'Subscription reactivated'];
        }
        
        // Create new subscriber
        $subscriber = NewsletterSubscriber::create([
            'email' => $email,
            'name' => $name,
            'is_active' => true,
            'unsubscribe_token' => bin2hex(random_bytes(32)),
            'subscribed_at' => date('Y-m-d H:i:s'),
        ]);
        
        return [
            'success' => true,
            'message' => 'Successfully subscribed to newsletter',
            'subscriber_id' => $subscriber->getKey(),
        ];
    }

    /**
     * Unsubscribe from newsletter using token
     * 
     * @return array<string, mixed>
     */
    public function unsubscribe(string $token): array
    {
        $subscriber = NewsletterSubscriber::findByToken($token);
        
        if ($subscriber === null) {
            return ['success' => false, 'message' => 'Invalid unsubscribe link'];
        }
        
        $subscriber->unsubscribe();
        
        return ['success' => true, 'message' => 'Successfully unsubscribed from newsletter'];
    }

    /**
     * Unsubscribe by email
     * 
     * @return array<string, mixed>
     */
    public function unsubscribeByEmail(string $email): array
    {
        $subscriber = NewsletterSubscriber::findByEmail($email);
        
        if ($subscriber === null) {
            return ['success' => false, 'message' => 'Email not found'];
        }
        
        $subscriber->unsubscribe();
        
        return ['success' => true, 'message' => 'Successfully unsubscribed from newsletter'];
    }

    /**
     * Get all active subscribers
     * 
     * @return array<NewsletterSubscriber>
     */
    public function getSubscribers(bool $activeOnly = true): array
    {
        if ($activeOnly) {
            return NewsletterSubscriber::getActive();
        }
        
        return NewsletterSubscriber::all();
    }

    /**
     * Get subscriber count
     */
    public function getSubscriberCount(bool $activeOnly = true): int
    {
        return NewsletterSubscriber::count($activeOnly);
    }

    /**
     * Send campaign to all subscribers
     * 
     * @param array<string, mixed>|NewsletterCampaign $campaign
     * @return array<string, mixed>
     */
    public function sendCampaign(array|NewsletterCampaign $campaign): array
    {
        if (is_array($campaign)) {
            $campaignData = $campaign;
        } else {
            $campaignData = $campaign->toArray();
        }
        
        // Get active subscribers
        $subscribers = $this->getSubscribers(true);
        
        if (empty($subscribers)) {
            return ['success' => false, 'message' => 'No active subscribers'];
        }
        
        // Prepare subscriber data for email service
        $subscriberData = array_map(fn($sub) => $sub->toArray(), $subscribers);
        
        // Send newsletter
        $sentCount = $this->emailService->sendNewsletter($subscriberData, $campaignData);
        
        // Update campaign sent count if it's a model
        if ($campaign instanceof NewsletterCampaign) {
            $campaign->markAsSent($sentCount);
        }
        
        return [
            'success' => true,
            'message' => "Newsletter sent to {$sentCount} subscribers",
            'sent_count' => $sentCount,
            'total_subscribers' => count($subscribers),
        ];
    }

    /**
     * Create a new campaign
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createCampaign(array $data): array
    {
        // Validate required fields
        if (empty($data['subject']) || empty($data['content'])) {
            return ['success' => false, 'message' => 'Subject and content are required'];
        }
        
        $campaign = NewsletterCampaign::create([
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => $data['status'] ?? 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);
        
        return [
            'success' => true,
            'message' => 'Campaign created successfully',
            'campaign_id' => $campaign->getKey(),
        ];
    }

    /**
     * Get all campaigns
     * 
     * @return array<NewsletterCampaign>
     */
    public function getCampaigns(): array
    {
        return NewsletterCampaign::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Import subscribers from CSV
     * 
     * @return array<string, mixed>
     */
    public function importSubscribers(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File not found'];
        }
        
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            return ['success' => false, 'message' => 'Unable to read file'];
        }
        
        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $isFirst = true;
        
        while (($data = fgetcsv($handle)) !== false) {
            // Skip header row
            if ($isFirst) {
                $isFirst = false;
                continue;
            }
            
            $email = $data[0] ?? '';
            $name = $data[1] ?? null;
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors++;
                continue;
            }
            
            $result = $this->subscribe($email, $name);
            
            if ($result['success']) {
                $imported++;
            } else {
                $skipped++;
            }
        }
        
        fclose($handle);
        
        return [
            'success' => true,
            'message' => "Import completed: {$imported} imported, {$skipped} skipped, {$errors} errors",
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Export subscribers to CSV
     * 
     * @return array<string, mixed>
     */
    public function exportSubscribers(bool $activeOnly = true): array
    {
        $subscribers = $this->getSubscribers($activeOnly);
        
        $csv = "email,name,subscribed_at,status\n";
        
        foreach ($subscribers as $subscriber) {
            $csv .= sprintf(
                '"%s","%s","%s","%s"' . "\n",
                $subscriber->email ?? '',
                str_replace('"', '""', $subscriber->name ?? ''),
                $subscriber->subscribed_at ?? '',
                $subscriber->isActive() ? 'active' : 'unsubscribed'
            );
        }
        
        return [
            'success' => true,
            'content' => $csv,
            'filename' => 'newsletter_subscribers_' . date('Y-m-d') . '.csv',
            'count' => count($subscribers),
        ];
    }
}
