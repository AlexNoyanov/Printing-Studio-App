<?php
// API Wrapper for migrate colors endpoint
// This file is placed directly in /Apps/Printing/api/migrate_colors.php

// Include CORS configuration FIRST
$backendDir = dirname(__DIR__) . '/backend';
require_once $backendDir . '/cors.php';
require_once $backendDir . '/config.php';

// Include the actual migrate colors handler
require_once $backendDir . '/api/migrate_colors.php';

