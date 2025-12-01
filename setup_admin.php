<?php
$host = '56orx3. h.filess. io';
$port = 3307;
$dbname = 'khairawang_dairy_mirrorheif';
$username = 'khairawang_dairy_mirrorheif';
$password = '87572297a22dc2b9c3fcd80d0d97d1b0b5ede17d';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Connected to database!\n";
    
    $adminPassword = password_hash('admin123', PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3,
    ]);
    
    $pdo->exec("DELETE FROM users WHERE email = 'admin@khairawangdairy.com'");
    
    $stmt = $pdo->prepare("
        INSERT INTO users (role_id, email, password, name, phone, status, email_verified_at, created_at, updated_at)
        VALUES (1, 'admin@khairawangdairy.com', :password, 'Admin User', '9800000000', 'active', NOW(), NOW(), NOW())
    ");
    $stmt->execute(['password' => $adminPassword]);
    
    echo "Admin user created!\n";
    echo "Email: admin@khairawangdairy.com\n";
    echo "Password: admin123\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() .  "\n";
}


