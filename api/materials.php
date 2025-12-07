<?php
// API Wrapper for materials endpoint
// This file is placed directly in /Apps/Printing/api/materials.php
// It includes the necessary CORS and config, then routes to the actual backend logic.

$backendDir = dirname(__DIR__) . '/backend'; // Path to the backend directory
require_once $backendDir . '/cors.php';
require_once $backendDir . '/config.php';
require_once $backendDir . '/api/materials.php'; // Include the actual backend logic

