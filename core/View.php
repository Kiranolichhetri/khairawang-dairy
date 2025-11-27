<?php

declare(strict_types=1);

namespace Core;

/**
 * Template View Engine
 * 
 * Provides template rendering with layout support, component includes,
 * and automatic XSS prevention through output escaping.
 */
class View
{
    private string $viewsPath;
    private string $layoutsPath;
    private string $componentsPath;
    private ?string $layout = null;
    
    /** @var array<string, mixed> */
    private array $sections = [];
    
    /** @var array<string, mixed> */
    private array $shared = [];
    
    private ?string $currentSection = null;

    public function __construct(
        string $viewsPath = '',
        string $layoutsPath = '',
        string $componentsPath = ''
    ) {
        $app = Application::getInstance();
        $basePath = $app ? $app->basePath() : dirname(__DIR__);
        
        $this->viewsPath = $viewsPath ?: $basePath . '/resources/views';
        $this->layoutsPath = $layoutsPath ?: $this->viewsPath . '/layouts';
        $this->componentsPath = $componentsPath ?: $this->viewsPath . '/components';
    }

    /**
     * Render a view template
     * 
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $this->layout = null;
        $this->sections = [];
        $this->currentSection = null;
        
        // Merge shared data
        $data = array_merge($this->shared, $data);
        
        // Render the template - sections are captured via section()/endSection() calls within the template
        $content = $this->renderTemplate($template, $data);
        
        // If layout is set, render layout with sections
        if ($this->layout !== null) {
            // Only use raw content if no section was defined or section is whitespace-only
            if (!isset($this->sections['content']) || trim($this->sections['content']) === '') {
                $this->sections['content'] = $content;
            }
            $content = $this->renderTemplate($this->layout, $data, true);
        }
        
        return $content;
    }

    /**
     * Render a template file
     * 
     * @param array<string, mixed> $data
     */
    private function renderTemplate(string $template, array $data, bool $isLayout = false): string
    {
        $basePath = $isLayout ? $this->layoutsPath : $this->viewsPath;
        $filePath = $basePath . '/' . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($filePath)) {
            throw new \RuntimeException("View not found: {$template} ({$filePath})");
        }
        
        // Filter data keys to prevent reserved variable pollution
        $reservedKeys = ['this', 'view', 'filePath', 'template', 'data', 'basePath', 'isLayout', '_ENV', '_SERVER', '_SESSION', '_COOKIE', '_GET', '_POST', '_FILES', '_REQUEST'];
        $safeData = array_filter(
            $data,
            fn($key) => !in_array($key, $reservedKeys, true) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key),
            ARRAY_FILTER_USE_KEY
        );
        
        // Extract safe data as variables
        extract($safeData, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        
        // Make view methods available
        $view = $this;
        
        // Include the template
        include $filePath;
        
        return ob_get_clean() ?: '';
    }

    /**
     * Set the layout for the current view
     */
    public function extends(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Start a section
     */
    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    /**
     * End a section
     */
    public function endSection(): void
    {
        if ($this->currentSection === null) {
            throw new \RuntimeException('No section started');
        }
        
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    /**
     * Yield a section content
     */
    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Include a component
     * 
     * @param array<string, mixed> $data
     */
    public function component(string $name, array $data = []): string
    {
        $filePath = $this->componentsPath . '/' . str_replace('.', '/', $name) . '.php';
        
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Component not found: {$name}");
        }
        
        // Merge shared data
        $data = array_merge($this->shared, $data);
        
        // Extract data as variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Make view methods available
        $view = $this;
        
        include $filePath;
        
        return ob_get_clean() ?: '';
    }

    /**
     * Include a partial view
     * 
     * @param array<string, mixed> $data
     */
    public function include(string $template, array $data = []): string
    {
        return $this->renderTemplate($template, array_merge($this->shared, $data));
    }

    /**
     * Escape output (XSS prevention)
     */
    public function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Shorthand for escape
     */
    public function e(mixed $value): string
    {
        return $this->escape($value);
    }

    /**
     * Output raw (unescaped) content - use with caution!
     */
    public function raw(mixed $value): string
    {
        return (string) $value;
    }

    /**
     * Format a date
     */
    public function date(string|\DateTimeInterface $date, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        return $date->format($format);
    }

    /**
     * Format a number
     */
    public function number(float|int $value, int $decimals = 2, string $decPoint = '.', string $thousandsSep = ','): string
    {
        return number_format($value, $decimals, $decPoint, $thousandsSep);
    }

    /**
     * Format currency (NPR - Nepalese Rupee)
     */
    public function currency(float|int $amount, string $symbol = 'Rs.'): string
    {
        return $symbol . ' ' . $this->number($amount);
    }

    /**
     * Truncate text
     */
    public function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Generate URL
     */
    public function url(string $path = ''): string
    {
        $app = Application::getInstance();
        $baseUrl = $app?->config('app.url', '') ?? '';
        
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Generate asset URL
     */
    public function asset(string $path): string
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }

    /**
     * Generate route URL
     * 
     * @param array<string, mixed> $parameters
     */
    public function route(string $name, array $parameters = []): string
    {
        $app = Application::getInstance();
        
        if ($app === null) {
            return '#';
        }
        
        return $app->getRouter()->url($name, $parameters);
    }

    /**
     * CSRF token field
     */
    public function csrf(): string
    {
        $app = Application::getInstance();
        $token = $app?->session()->getCsrfToken() ?? '';
        
        return '<input type="hidden" name="_csrf_token" value="' . $this->e($token) . '">';
    }

    /**
     * Method spoofing field
     */
    public function method(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . $this->e(strtoupper($method)) . '">';
    }

    /**
     * Check if user is authenticated
     */
    public function auth(): bool
    {
        $app = Application::getInstance();
        return $app?->session()->has('user_id') ?? false;
    }

    /**
     * Get authenticated user
     * 
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        $app = Application::getInstance();
        return $app?->session()->get('user');
    }

    /**
     * Get old input value (for form repopulation)
     */
    public function old(string $key, mixed $default = ''): mixed
    {
        $app = Application::getInstance();
        $oldInput = $app?->session()->getFlash('old_input', []) ?? [];
        
        return $oldInput[$key] ?? $default;
    }

    /**
     * Get error for a field
     */
    public function error(string $key): ?string
    {
        $app = Application::getInstance();
        $errors = $app?->session()->getFlash('errors', []) ?? [];
        
        return $errors[$key][0] ?? null;
    }

    /**
     * Check if field has error
     */
    public function hasError(string $key): bool
    {
        return $this->error($key) !== null;
    }

    /**
     * Get all errors
     * 
     * @return array<string, array<string>>
     */
    public function errors(): array
    {
        $app = Application::getInstance();
        return $app?->session()->getFlash('errors', []) ?? [];
    }

    /**
     * Get flash message
     */
    public function flash(string $key, mixed $default = null): mixed
    {
        $app = Application::getInstance();
        return $app?->session()->getFlash($key, $default);
    }

    /**
     * Share data with all views
     * 
     * @param array<string, mixed>|string $key
     */
    public function share(array|string $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
        } else {
            $this->shared[$key] = $value;
        }
    }

    /**
     * Get shared data
     * 
     * @return array<string, mixed>
     */
    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * Check if template exists
     */
    public function exists(string $template): bool
    {
        $filePath = $this->viewsPath . '/' . str_replace('.', '/', $template) . '.php';
        return file_exists($filePath);
    }

    /**
     * Set views path
     */
    public function setViewsPath(string $path): void
    {
        $this->viewsPath = $path;
    }

    /**
     * Set layouts path
     */
    public function setLayoutsPath(string $path): void
    {
        $this->layoutsPath = $path;
    }

    /**
     * Set components path
     */
    public function setComponentsPath(string $path): void
    {
        $this->componentsPath = $path;
    }

    /**
     * Render JSON for inline scripts (safely)
     */
    public function json(mixed $data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Create a conditional class string
     * 
     * @param array<string, bool> $classes
     */
    public function classList(array $classes): string
    {
        $result = [];
        
        foreach ($classes as $class => $condition) {
            if ($condition) {
                $result[] = $class;
            }
        }
        
        return implode(' ', $result);
    }
}
