<?php
// CORS configuration - include this at the very top of all API files
// Set CORS headers before any output

if (!headers_sent()) {
    // Allow all origins (for development/production)
    // In production, you might want to restrict this to specific domains
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
    
    // Allow specific origins or all
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Max-Age: 3600');
    
    // Set content type
    header('Content-Type: application/json; charset=utf-8');
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

