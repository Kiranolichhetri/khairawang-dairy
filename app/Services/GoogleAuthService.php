<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;

/**
 * Google OAuth Service
 * 
 * Handles Google OAuth authentication flow for user login/registration.
 */
class GoogleAuthService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->clientId = $app?->config('app.services.google.client_id', '') ?? $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $this->clientSecret = $app?->config('app.services.google.client_secret', '') ?? $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $this->redirectUri = $this->getRedirectUri($app);
    }

    /**
     * Get the redirect URI for Google OAuth
     * 
     * Uses GOOGLE_REDIRECT_URI if set, otherwise constructs from APP_URL
     */
    private function getRedirectUri(?Application $app): string
    {
        // First try explicit GOOGLE_REDIRECT_URI
        $explicitUri = $app?->config('app.services.google.redirect_uri', '') ?? $_ENV['GOOGLE_REDIRECT_URI'] ?? '';
        if (!empty($explicitUri)) {
            return $explicitUri;
        }
        
        // Fallback: construct from APP_URL
        $baseUrl = $app?->config('app.url', '') ?? $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $baseUrl = rtrim($baseUrl, '/');
        return $baseUrl . '/auth/google/callback';
    }

    /**
     * Get the Google OAuth authorization URL
     */
    public function getAuthUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     *
     * @return array<string, mixed>|null
     * @throws \RuntimeException When cURL initialization fails
     */
    public function getAccessToken(string $code): ?array
    {
        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $code,
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        if ($ch === false) {
            error_log('GoogleAuthService: Failed to initialize cURL for token exchange');
            throw new \RuntimeException('Failed to initialize HTTP client');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('GoogleAuthService: cURL request failed - ' . $error);
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Get user info from Google using access token
     *
     * @return array<string, mixed>|null
     * @throws \RuntimeException When cURL initialization fails
     */
    public function getUserInfo(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        if ($ch === false) {
            error_log('GoogleAuthService: Failed to initialize cURL for user info');
            throw new \RuntimeException('Failed to initialize HTTP client');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('GoogleAuthService: cURL request failed - ' . $error);
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }
}
