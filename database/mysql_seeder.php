<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;
use Throwable;

// Load environment variables if available
if (class_exists(Dotenv::class)) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($_ENV['DB_PORT'] ?? 3306);
$database = $_ENV['DB_DATABASE'] ?? 'khairawang_dairy';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);

echo "===========================================\n";
echo "KHAIRAWANG DAIRY - MySQL Seeder\n";
echo "===========================================\n\n";

echo "Connecting to MySQL...\n";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "❌ Failed to connect to MySQL: " . $e->getMessage() . "\n");
    exit(1);
}

$pdo->beginTransaction();

try {
    // Load schema
    $schemaPath = __DIR__ . '/schema.sql';
    if (file_exists($schemaPath)) {
        echo "Applying schema...\n";
        $schemaSql = file_get_contents($schemaPath);
        $pdo->exec($schemaSql ?: '');
    }

    echo "Seeding roles...\n";
    $roles = [
        ['name' => 'admin', 'permissions' => json_encode(['*'])],
        ['name' => 'manager', 'permissions' => json_encode(['manage_products', 'manage_orders'])],
        ['name' => 'staff', 'permissions' => json_encode(['view_orders'])],
        ['name' => 'customer', 'permissions' => json_encode(['place_order'])],
    ];

    $roleStmt = $pdo->prepare('INSERT INTO roles (name, permissions) VALUES (:name, :permissions)
        ON DUPLICATE KEY UPDATE permissions = VALUES(permissions)');

    foreach ($roles as $role) {
        $roleStmt->execute($role);
    }

    echo "Seeding admin user...\n";
    $adminPassword = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);

    $roleId = (int) ($pdo->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1")->fetchColumn() ?? 0);

    $userStmt = $pdo->prepare(
        'INSERT INTO users (role_id, email, password, name, phone, status, email_verified_at)
         VALUES (:role_id, :email, :password, :name, :phone, :status, NOW())
         ON DUPLICATE KEY UPDATE role_id = VALUES(role_id), password = VALUES(password),
         name = VALUES(name), phone = VALUES(phone), status = VALUES(status), email_verified_at = NOW()'
    );

    $userStmt->execute([
        'role_id' => $roleId,
        'email' => 'admin@khairawangdairy.com',
        'password' => $adminPassword,
        'name' => 'Admin User',
        'phone' => '+977-9800000000',
        'status' => 'active',
    ]);

    echo "Seeding default settings...\n";
    $settings = [
        ['key' => 'site_name', 'value' => 'KHAIRAWANG DAIRY', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_tagline', 'value' => 'Premium Dairy Products', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_email', 'value' => 'info@khairawangdairy.com', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_phone', 'value' => '+977-9800000000', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_address', 'value' => 'Kathmandu, Nepal', 'type' => 'string', 'group' => 'general'],
        ['key' => 'currency_code', 'value' => 'NPR', 'type' => 'string', 'group' => 'general'],
        ['key' => 'currency_symbol', 'value' => 'Rs.', 'type' => 'string', 'group' => 'general'],
        ['key' => 'shipping_cost', 'value' => '100', 'type' => 'integer', 'group' => 'shipping'],
        ['key' => 'free_shipping_threshold', 'value' => '1000', 'type' => 'integer', 'group' => 'shipping'],
        ['key' => 'tax_rate', 'value' => '13', 'type' => 'integer', 'group' => 'tax'],
        ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general'],
    ];

    $settingsStmt = $pdo->prepare(
        'INSERT INTO settings (`key`, `value`, `type`, `group`) VALUES (:key, :value, :type, :group)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `type` = VALUES(`type`), `group` = VALUES(`group`);'
    );

    foreach ($settings as $setting) {
        $settingsStmt->execute($setting);
    }

    $pdo->commit();

    echo "\n===========================================\n";
    echo "MySQL seeding completed successfully!\n";
    echo "===========================================\n\n";
    echo "Admin Login Credentials:\n";
    echo "  Email: admin@khairawangdairy.com\n";
    echo "  Password: admin123\n\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "\n❌ Seeder failed: " . $e->getMessage() . "\n");
    exit(1);
}
