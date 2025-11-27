<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SeoService;
use Core\Request;
use Core\Response;

/**
 * SEO Controller
 * 
 * Handles SEO-related endpoints like sitemap and robots.txt.
 */
class SeoController
{
    private SeoService $seoService;

    public function __construct()
    {
        $this->seoService = new SeoService();
    }

    /**
     * Generate and serve sitemap.xml
     */
    public function sitemap(Request $request): Response
    {
        $content = $this->seoService->generateSitemap();
        
        return new Response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve robots.txt
     */
    public function robots(Request $request): Response
    {
        $content = $this->seoService->generateRobotsTxt();
        
        return new Response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
