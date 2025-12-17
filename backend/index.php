<?php
// Main API router for Printing App
// Routes requests to appropriate API endpoints

// Include CORS configuration FIRST
require_once __DIR__ . '/cors.php';

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Handle different path patterns:
// /Apps/Printing/api/health
// /Apps/Printing/backend/api/health
// /api/health (if accessed from backend directory)

// Remove base paths
$basePaths = ['/Apps/Printing/api', '/Apps/Printing/backend/api', '/api'];
foreach ($basePaths as $basePath) {
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
        break;
    }
}

// Remove leading/trailing slashes
$path = trim($path, '/');
$pathParts = $path ? explode('/', $path) : [];

// Route to appropriate endpoint
if (empty($pathParts) || $pathParts[0] === '') {
    // Health check or root
    require_once __DIR__ . '/api/health.php';
} else {
    $endpoint = $pathParts[0];
    
    switch ($endpoint) {
        case 'users':
            require_once __DIR__ . '/api/users.php';
            break;
        case 'colors':
            require_once __DIR__ . '/api/colors.php';
            break;
        case 'orders':
            require_once __DIR__ . '/api/orders.php';
            break;
        case 'health':
            require_once __DIR__ . '/api/health.php';
            break;
        case 'shop':
            require_once __DIR__ . '/api/shop.php';
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found: ' . $endpoint]);
    }
}

