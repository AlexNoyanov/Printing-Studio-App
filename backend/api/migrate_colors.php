<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    sendJSON(['error' => 'Method not allowed'], 405);
}

try {
    $pdo = getDBConnection();
    
    // Check if materials table exists, create if not
    $stmt = $pdo->query("SHOW TABLES LIKE 'materials'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS materials (
            id VARCHAR(50) PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(7) NOT NULL,
            material_type VARCHAR(50) NOT NULL,
            shop_link TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_material_type (material_type),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    
    // Get all colors from all users
    $stmt = $pdo->query("SELECT id, user_id, name, value, filament_link, created_at, updated_at FROM colors ORDER BY user_id, created_at");
    $colors = $stmt->fetchAll();
    
    if (empty($colors)) {
        sendJSON(['success' => true, 'message' => 'No colors found to migrate', 'migrated' => 0, 'users' => []]);
    }
    
    // Migrate colors to materials for all users
    $migrated = 0;
    $skipped = 0;
    $userStats = [];
    
    $insertStmt = $pdo->prepare("INSERT INTO materials (id, user_id, name, color, material_type, shop_link, created_at, updated_at)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                 ON DUPLICATE KEY UPDATE 
                                   shop_link = COALESCE(VALUES(shop_link), materials.shop_link),
                                   updated_at = CURRENT_TIMESTAMP");
    
    foreach ($colors as $color) {
        $userId = $color['user_id'];
        
        // Initialize user stats if not exists
        if (!isset($userStats[$userId])) {
            $userStats[$userId] = ['migrated' => 0, 'skipped' => 0];
        }
        
        // Check if material already exists for this user
        $checkStmt = $pdo->prepare("SELECT id FROM materials WHERE user_id = ? AND name = ? AND color = ?");
        $checkStmt->execute([$userId, $color['name'], $color['value']]);
        
        if ($checkStmt->rowCount() > 0) {
            $skipped++;
            $userStats[$userId]['skipped']++;
            continue;
        }
        
        // Create filament ID
        $filamentId = 'filament_' . $color['id'];
        
        // Insert as material with default type PLA
        $insertStmt->execute([
            $filamentId,
            $color['user_id'],
            $color['name'],
            $color['value'],
            'PLA', // Default material type
            $color['filament_link'] ?: null,
            $color['created_at'],
            $color['updated_at']
        ]);
        
        $migrated++;
        $userStats[$userId]['migrated']++;
    }
    
    // Get user names for stats
    $userIds = array_keys($userStats);
    $userNames = [];
    if (!empty($userIds)) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $userStmt = $pdo->prepare("SELECT id, username FROM users WHERE id IN ($placeholders)");
        $userStmt->execute($userIds);
        $users = $userStmt->fetchAll();
        foreach ($users as $user) {
            $userNames[$user['id']] = $user['username'];
        }
    }
    
    // Format user stats
    $formattedStats = [];
    foreach ($userStats as $userId => $stats) {
        if ($stats['migrated'] > 0 || $stats['skipped'] > 0) {
            $formattedStats[] = [
                'userId' => $userId,
                'username' => $userNames[$userId] ?? 'Unknown',
                'migrated' => $stats['migrated'],
                'skipped' => $stats['skipped']
            ];
        }
    }
    
    sendJSON([
        'success' => true,
        'message' => "Migrated $migrated colors to filaments for " . count($formattedStats) . " user(s)",
        'migrated' => $migrated,
        'skipped' => $skipped,
        'total' => count($colors),
        'users' => $formattedStats
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    sendJSON(['error' => 'Database error: ' . $e->getMessage()]);
}

