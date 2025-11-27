<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContactService;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Contact Controller
 * 
 * Handles contact form display and submission.
 */
class ContactController
{
    private ContactService $contactService;

    public function __construct(?ContactService $contactService = null)
    {
        $this->contactService = $contactService ?? new ContactService();
    }

    /**
     * Show contact page
     */
    public function show(Request $request): Response
    {
        return Response::view('contact.index', [
            'title' => 'Contact Us',
            'pageTitle' => 'Contact Us',
            'pageDescription' => 'Get in touch with KHAIRAWANG DAIRY. We\'d love to hear from you!',
        ]);
    }

    /**
     * Submit contact form
     */
    public function submit(Request $request): Response
    {
        // Validate input
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|min:3|max:255',
            'message' => 'required|min:10|max:5000',
            'phone' => 'max:20',
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
            
            return Response::redirect('/contact');
        }
        
        $result = $this->contactService->submit([
            'name' => trim($request->input('name', '') ?? ''),
            'email' => trim($request->input('email', '') ?? ''),
            'phone' => trim($request->input('phone', '') ?? ''),
            'subject' => trim($request->input('subject', '') ?? ''),
            'message' => trim($request->input('message', '') ?? ''),
        ]);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
            return Response::redirect('/contact/thank-you');
        }
        
        $session?->error($result['message']);
        $session?->setFlash('old_input', $request->all());
        
        return Response::redirect('/contact');
    }

    /**
     * Show thank you page
     */
    public function thankYou(Request $request): Response
    {
        return Response::view('contact.thank-you', [
            'title' => 'Thank You',
            'pageTitle' => 'Thank You!',
            'pageDescription' => 'Your message has been received. We\'ll get back to you soon.',
        ]);
    }
}
