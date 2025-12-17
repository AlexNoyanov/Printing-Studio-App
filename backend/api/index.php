<?php
// API Router - Routes requests from /Apps/Printing/api/* to backend handlers
// This file should be placed at: /Apps/Printing/api/index.php

// Include CORS configuration FIRST - use absolute path
$backendDir = dirname(__DIR__);
require_once $backendDir . '/cors.php';

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove base path /Apps/Printing/api
$path = preg_replace('#^/Apps/Printing/api/?#', '', $path);
$path = trim($path, '/');

// If path is empty, default to health
if (empty($path)) {
    $path = 'health';
}

$pathParts = explode('/', $path);
$endpoint = $pathParts[0];

// Route to appropriate endpoint in backend/api/
$apiDir = $backendDir . '/api/';
switch ($endpoint) {
    case 'users':
        require_once $apiDir . 'users.php';
        break;
    case 'colors':
        require_once $apiDir . 'colors.php';
        break;
    case 'orders':
        require_once $apiDir . 'orders.php';
        break;
    case 'health':
        require_once $apiDir . 'health.php';
        break;
    case 'shop':
        require_once $apiDir . 'shop.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found: ' . $endpoint]);
}

