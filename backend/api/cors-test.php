<?php
// CORS Test Endpoint
// Access: https://noyanov.com/Apps/Printing/api/cors-test.php
// This file helps diagnose CORS issues

// Get the origin from the request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'none';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Max-Age: 3600');
header('Content-Type: application/json; charset=utf-8');

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    header('Content-Length: 0');
    exit();
}

// Return test data
echo json_encode([
    'status' => 'ok',
    'message' => 'CORS test successful',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'origin' => $origin,
    'headers_sent' => headers_sent(),
    'cors_headers' => [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin'
    ]
], JSON_PRETTY_PRINT);

