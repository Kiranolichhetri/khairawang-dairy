<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Request;
use Core\Response;

/**
 * Page Controller
 * 
 * Handles static pages like About, Terms, Privacy, etc.
 */
class PageController
{
    /**
     * Display the About page
     */
    public function about(Request $request): Response
    {
        return Response::view('pages.about', [
            'title' => 'About Us',
            'pageDescription' => 'Learn about KHAIRAWANG DAIRY - Our story, mission, and commitment to delivering the finest dairy products from Nepal.',
        ]);
    }

    /**
     * Display the Terms and Conditions page
     */
    public function terms(Request $request): Response
    {
        return Response::view('pages.terms', [
            'title' => 'Terms & Conditions',
            'pageDescription' => 'Terms and conditions for using KHAIRAWANG DAIRY services and website.',
        ]);
    }

    /**
     * Display the Privacy Policy page
     */
    public function privacy(Request $request): Response
    {
        return Response::view('pages.privacy', [
            'title' => 'Privacy Policy',
            'pageDescription' => 'KHAIRAWANG DAIRY privacy policy - How we collect, use, and protect your personal information.',
        ]);
    }
}
