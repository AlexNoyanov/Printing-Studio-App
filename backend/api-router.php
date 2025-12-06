<?php
// API Router - Place this file at /Apps/Printing/api/index.php
// This file routes all API requests to the backend

// Include CORS configuration FIRST
require_once __DIR__ . '/../cors.php';

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

// Route to appropriate endpoint
$backendDir = __DIR__ . '/../backend/api/';
switch ($endpoint) {
    case 'users':
        require_once $backendDir . 'users.php';
        break;
    case 'colors':
        require_once $backendDir . 'colors.php';
        break;
    case 'orders':
        require_once $backendDir . 'orders.php';
        break;
    case 'health':
        require_once $backendDir . 'health.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found: ' . $endpoint]);
}

