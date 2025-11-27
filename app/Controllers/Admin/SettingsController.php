<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin Settings Controller
 * 
 * Handles application settings management.
 */
class SettingsController
{
    /**
     * Show settings page
     */
    public function index(Request $request): Response
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::error('Database connection error', 500);
        }
        
        // Get all settings grouped
        $settings = $db->select("SELECT * FROM settings ORDER BY `group`, `key`");
        
        $groupedSettings = [];
        foreach ($settings as $setting) {
            $group = $setting['group'] ?? 'general';
            if (!isset($groupedSettings[$group])) {
                $groupedSettings[$group] = [];
            }
            
            // Cast value based on type
            $value = $setting['value'];
            switch ($setting['type']) {
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $groupedSettings[$group][] = [
                'key' => $setting['key'],
                'value' => $value,
                'type' => $setting['type'],
            ];
        }
        
        return Response::view('admin.settings.index', [
            'title' => 'Settings',
            'settings' => $groupedSettings,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request): Response
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::error('Database connection error', 500);
        }
        
        $settings = $request->input('settings', []);
        
        if (empty($settings) || !is_array($settings)) {
            if ($request->expectsJson()) {
                return Response::error('No settings provided', 400);
            }
            
            $session = $app->session();
            $session->error('No settings to update.');
            return Response::redirect('/admin/settings');
        }
        
        foreach ($settings as $key => $value) {
            // Sanitize key
            $sanitizedKey = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
            if (empty($sanitizedKey) || $sanitizedKey !== $key) {
                continue;
            }
            
            // Get current setting to determine type
            $current = $db->selectOne("SELECT * FROM settings WHERE `key` = ?", [$key]);
            
            if ($current === null) {
                continue;
            }
            
            // Process value based on type
            $processedValue = $value;
            switch ($current['type']) {
                case 'boolean':
                    $processedValue = $value ? '1' : '0';
                    break;
                case 'json':
                    $processedValue = is_string($value) ? $value : json_encode($value);
                    break;
                case 'integer':
                    $processedValue = (string) (int) $value;
                    break;
                default:
                    $processedValue = (string) $value;
            }
            
            $db->update('settings', ['value' => $processedValue], ['key' => $key]);
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
        }
        
        $session = $app->session();
        $session->success('Settings updated successfully!');
        
        return Response::redirect('/admin/settings');
    }

    /**
     * Get a specific setting value
     */
    public function get(Request $request, string $key): Response
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::error('Database connection error', 500);
        }
        
        // Sanitize key
        $sanitizedKey = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
        if (empty($sanitizedKey) || $sanitizedKey !== $key) {
            return Response::error('Invalid setting key', 400);
        }
        
        $setting = $db->selectOne("SELECT * FROM settings WHERE `key` = ?", [$key]);
        
        if ($setting === null) {
            return Response::error('Setting not found', 404);
        }
        
        // Cast value based on type
        $value = $setting['value'];
        switch ($setting['type']) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
        }
        
        return Response::json([
            'success' => true,
            'data' => [
                'key' => $setting['key'],
                'value' => $value,
                'type' => $setting['type'],
                'group' => $setting['group'],
            ],
        ]);
    }
}
