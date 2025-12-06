<?php
// CORS configuration - include this at the very top of all API files
// Set CORS headers before any output

// Get the origin from the request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';

// List of allowed origins (add your Firebase domain here)
$allowedOrigins = [
    'https://printing-studio-app-4e0e6.web.app',
    'https://printing-studio-app-4e0e6.firebaseapp.com',
    'http://localhost:5173',
    'http://localhost:3000',
    '*'
];

// Check if origin is allowed, or use wildcard
$allowedOrigin = in_array($origin, $allowedOrigins) ? $origin : '*';

// Set CORS headers - MUST be before any output
if (!headers_sent()) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Max-Age: 3600');
    
    // Handle preflight OPTIONS requests immediately
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        header('Content-Length: 0');
        exit();
    }
    
    // Set content type for non-OPTIONS requests
    header('Content-Type: application/json; charset=utf-8');
}

