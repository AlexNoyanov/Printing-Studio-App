<?php
// Database configuration for Printing App
// Copy this file to config.php and fill in your database credentials

define('DB_HOST', 'your-database-host');
define('DB_USER', 'your-database-user');
define('DB_PASS', 'your-database-password');
define('DB_NAME', 'your-database-name');
define('DB_CHARSET', 'utf8mb4');

// API Base Path
define('API_BASE', '/Apps/Printing/api');

// CORS headers (set here as backup, but should be set in index.php first)
if (!headers_sent()) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 3600');
    header('Content-Type: application/json; charset=utf-8');
}

// Handle preflight requests (backup, but should be handled in index.php)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && !headers_sent()) {
    http_response_code(200);
    exit();
}

// Database connection function
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit();
    }
}

// Helper function to send JSON response
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// Helper function to get request body
function getRequestBody() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

