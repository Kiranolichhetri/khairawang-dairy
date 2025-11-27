<?php

declare(strict_types=1);

namespace Core;

/**
 * Session Manager
 * 
 * Provides secure session handling with database sessions support,
 * flash messages, and CSRF token management.
 */
class Session
{
    private bool $started = false;
    private string $sessionId = '';
    
    /** @var array<string, mixed> */
    private array $data = [];
    
    /** @var array<string, mixed> */
    private array $flash = [];
    
    private bool $useDatabase = false;
    private ?Database $db = null;
    private string $tableName = 'sessions';

    /**
     * Session configuration options
     * 
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->configure($options);
    }

    /**
     * Configure session options
     * 
     * @param array<string, mixed> $options
     */
    private function configure(array $options): void
    {
        $defaults = [
            'name' => 'KHAIRAWANG_SESSION',
            'lifetime' => 7200, // 2 hours
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        
        $options = array_merge($defaults, $options);
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => $options['lifetime'],
            'path' => $options['path'],
            'domain' => $options['domain'],
            'secure' => $options['secure'],
            'httponly' => $options['httponly'],
            'samesite' => $options['samesite'],
        ]);
        
        session_name($options['name']);
        
        // Check for database sessions
        if (isset($options['driver']) && $options['driver'] === 'database') {
            $this->useDatabase = true;
            if (isset($options['table'])) {
                $this->tableName = $options['table'];
            }
        }
    }

    /**
     * Set database connection for database sessions
     */
    public function setDatabase(Database $db): void
    {
        $this->db = $db;
        
        if ($this->useDatabase) {
            $this->setupDatabaseHandler();
        }
    }

    /**
     * Setup database session handler
     */
    private function setupDatabaseHandler(): void
    {
        if ($this->db === null) {
            return;
        }
        
        $db = $this->db;
        $tableName = $this->tableName;
        
        session_set_save_handler(
            // Open
            function(string $savePath, string $sessionName) use ($db): bool {
                return true;
            },
            // Close
            function(): bool {
                return true;
            },
            // Read
            function(string $sessionId) use ($db, $tableName): string {
                $result = $db->selectOne(
                    "SELECT data FROM {$tableName} WHERE id = ? AND expires_at > NOW()",
                    [$sessionId]
                );
                return $result['data'] ?? '';
            },
            // Write
            function(string $sessionId, string $data) use ($db, $tableName): bool {
                $lifetime = (int) ini_get('session.gc_maxlifetime');
                $expiresAt = date('Y-m-d H:i:s', time() + $lifetime);
                
                $db->query(
                    "INSERT INTO {$tableName} (id, data, expires_at) 
                     VALUES (?, ?, ?) 
                     ON DUPLICATE KEY UPDATE data = ?, expires_at = ?",
                    [$sessionId, $data, $expiresAt, $data, $expiresAt]
                );
                
                return true;
            },
            // Destroy
            function(string $sessionId) use ($db, $tableName): bool {
                $db->delete($tableName, ['id' => $sessionId]);
                return true;
            },
            // Garbage collection
            function(int $maxLifetime) use ($db, $tableName): int|false {
                return $db->query(
                    "DELETE FROM {$tableName} WHERE expires_at < NOW()"
                )->rowCount();
            }
        );
        
        register_shutdown_function('session_write_close');
    }

    /**
     * Start the session
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            $this->sessionId = session_id();
            $this->loadData();
            return true;
        }
        
        if (!headers_sent()) {
            session_start();
            $this->started = true;
            $this->sessionId = session_id();
            $this->loadData();
            
            // Rotate session ID periodically for security
            $this->rotateIfNeeded();
            
            return true;
        }
        
        return false;
    }

    /**
     * Load session data
     */
    private function loadData(): void
    {
        $this->data = $_SESSION['data'] ?? [];
        $this->flash = $_SESSION['flash'] ?? [];
        
        // Clear previous flash data
        $_SESSION['flash'] = [];
    }

    /**
     * Save session data
     */
    private function saveData(): void
    {
        $_SESSION['data'] = $this->data;
        $_SESSION['flash'] = array_merge($_SESSION['flash'] ?? [], $this->flash);
    }

    /**
     * Rotate session ID if needed (every 30 minutes)
     */
    private function rotateIfNeeded(): void
    {
        $lastRotation = $this->get('_last_rotation', 0);
        
        if (time() - $lastRotation > 1800) { // 30 minutes
            $this->regenerate();
            $this->set('_last_rotation', time());
        }
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        return $this->sessionId;
    }

    /**
     * Set a session value
     */
    public function set(string $key, mixed $value): void
    {
        $this->start();
        $this->data[$key] = $value;
        $this->saveData();
    }

    /**
     * Get a session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if session has a key
     */
    public function has(string $key): bool
    {
        $this->start();
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove a session value
     */
    public function remove(string $key): void
    {
        $this->start();
        unset($this->data[$key]);
        $this->saveData();
    }

    /**
     * Get all session data
     * 
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $this->start();
        return $this->data;
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $this->start();
        $this->data = [];
        $this->flash = [];
        $_SESSION = [];
    }

    /**
     * Set a flash message
     */
    public function flash(string $key, mixed $value): void
    {
        $this->start();
        $this->flash[$key] = $value;
        $this->saveData();
    }

    /**
     * Get a flash message
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $this->flash[$key] ?? $default;
    }

    /**
     * Check if flash message exists
     */
    public function hasFlash(string $key): bool
    {
        $this->start();
        return array_key_exists($key, $this->flash);
    }

    /**
     * Keep flash data for next request
     */
    public function reflash(): void
    {
        $this->start();
        $_SESSION['flash'] = $this->flash;
    }

    /**
     * Regenerate session ID
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        $this->start();
        
        if (session_regenerate_id($deleteOldSession)) {
            $this->sessionId = session_id();
            return true;
        }
        
        return false;
    }

    /**
     * Destroy the session
     */
    public function destroy(): bool
    {
        $this->start();
        
        $this->data = [];
        $this->flash = [];
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        $this->started = false;
        
        return session_destroy();
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        $this->start();
        
        $token = bin2hex(random_bytes(32));
        $this->set('_csrf_token', $token);
        $this->set('_csrf_time', time());
        
        return $token;
    }

    /**
     * Get CSRF token (generate if not exists)
     */
    public function getCsrfToken(): string
    {
        $this->start();
        
        $token = $this->get('_csrf_token');
        
        if ($token === null) {
            $token = $this->generateCsrfToken();
        }
        
        return $token;
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        $this->start();
        
        $storedToken = $this->get('_csrf_token');
        $csrfTime = $this->get('_csrf_time', 0);
        
        // Token expires after 2 hours
        if (time() - $csrfTime > 7200) {
            return false;
        }
        
        return hash_equals($storedToken ?? '', $token);
    }

    /**
     * Store intended URL for redirect after login
     */
    public function setIntendedUrl(string $url): void
    {
        $this->set('_intended_url', $url);
    }

    /**
     * Get intended URL and clear it
     */
    public function getIntendedUrl(string $default = '/'): string
    {
        $url = $this->get('_intended_url', $default);
        $this->remove('_intended_url');
        return $url;
    }

    /**
     * Store old input for form repopulation
     * 
     * @param array<string, mixed> $input
     */
    public function flashInput(array $input): void
    {
        // Filter out sensitive fields
        $sensitive = ['password', 'password_confirmation', 'current_password'];
        $input = array_filter(
            $input,
            fn($key) => !in_array($key, $sensitive, true),
            ARRAY_FILTER_USE_KEY
        );
        
        $this->flash('old_input', $input);
    }

    /**
     * Store validation errors
     * 
     * @param array<string, array<string>> $errors
     */
    public function flashErrors(array $errors): void
    {
        $this->flash('errors', $errors);
    }

    /**
     * Set success message
     */
    public function success(string $message): void
    {
        $this->flash('success', $message);
    }

    /**
     * Set error message
     */
    public function error(string $message): void
    {
        $this->flash('error', $message);
    }

    /**
     * Set warning message
     */
    public function warning(string $message): void
    {
        $this->flash('warning', $message);
    }

    /**
     * Set info message
     */
    public function info(string $message): void
    {
        $this->flash('info', $message);
    }

    /**
     * Check if session is started
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Push a value onto an array in session
     */
    public function push(string $key, mixed $value): void
    {
        $this->start();
        
        $array = $this->get($key, []);
        
        if (!is_array($array)) {
            $array = [];
        }
        
        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * Increment a value in session
     */
    public function increment(string $key, int $amount = 1): int
    {
        $this->start();
        
        $value = (int) $this->get($key, 0);
        $value += $amount;
        $this->set($key, $value);
        
        return $value;
    }

    /**
     * Decrement a value in session
     */
    public function decrement(string $key, int $amount = 1): int
    {
        return $this->increment($key, -$amount);
    }
}
