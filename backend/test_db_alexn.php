<?php
// Test database connection with alexn_printing_admin - Access via browser
// Place this file at: /Apps/Printing/backend/test_db_alexn.php
// Access: https://noyanov.com/Apps/Printing/backend/test_db_alexn.php

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Database credentials
$host = 's136.webhost1.ru';
$user = 'alexn_printing_admin';
$pass = 'vL2tI2sV7c';
$db = 'printing';
$charset = 'utf8mb4';

$result = [
    'status' => 'testing',
    'host' => $host,
    'user' => $user,
    'database' => $db,
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $result['status'] = 'success';
    $result['message'] = 'Connection successful!';
    
    // Get server info
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as database_name, USER() as current_user");
    $info = $stmt->fetch();
    $result['server_info'] = $info;
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $result['tables'] = $tables;
    $result['table_count'] = count($tables);
    
    // Check user permissions
    try {
        $stmt = $pdo->query("SELECT user, host FROM mysql.user WHERE user = '$user'");
        $users = $stmt->fetchAll();
        $result['user_permissions'] = $users;
    } catch (PDOException $e) {
        $result['user_permissions'] = 'Could not check: ' . $e->getMessage();
    }
    
    // Test a simple query
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch();
        $result['user_count'] = $userCount['count'];
    } catch (PDOException $e) {
        $result['user_count'] = 'Table users does not exist or error: ' . $e->getMessage();
    }
    
} catch (PDOException $e) {
    $result['status'] = 'error';
    $result['error_code'] = $e->getCode();
    $result['error_message'] = $e->getMessage();
    
    // Provide helpful error messages
    if ($e->getCode() == 1045) {
        $result['suggestion'] = "Authentication failed. Try granting permissions: GRANT ALL PRIVILEGES ON printing.* TO '$user'@'%' IDENTIFIED BY 'vL2tI2sV7c'; FLUSH PRIVILEGES;";
    } elseif ($e->getCode() == 2002) {
        $result['suggestion'] = 'Connection failed. Check hostname and server status.';
    } elseif ($e->getCode() == 1049) {
        $result['suggestion'] = 'Database does not exist. Create it with: CREATE DATABASE printing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
    } else {
        $result['suggestion'] = 'Check database configuration and server status.';
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);

