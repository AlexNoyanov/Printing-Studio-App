<?php
// Colors API endpoint - Place this at /Apps/Printing/api/colors.php
// This file includes CORS headers and routes to the backend handler

// Include CORS configuration FIRST
$backendDir = dirname(__DIR__) . '/backend';
require_once $backendDir . '/cors.php';
require_once $backendDir . '/config.php';

// Include the actual colors handler
require_once $backendDir . '/api/colors.php';

