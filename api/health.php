<?php
// Health check API endpoint - Place this at /Apps/Printing/api/health.php
// This file includes CORS headers and routes to the backend handler

// Include CORS configuration FIRST
$backendDir = dirname(__DIR__) . '/backend';
require_once $backendDir . '/cors.php';

// Include the actual health handler
require_once $backendDir . '/api/health.php';

