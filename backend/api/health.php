<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT 1");
    $stmt->fetch();
    
    sendJSON([
        'status' => 'ok',
        'database' => 'connected'
    ]);
} catch (Exception $e) {
    sendJSON([
        'status' => 'error',
        'database' => 'disconnected',
        'error' => $e->getMessage()
    ], 500);
}

