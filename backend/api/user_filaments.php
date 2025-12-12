<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDBConnection();
    
    // Ensure materials table exists first (required for foreign key)
    $pdo->exec("CREATE TABLE IF NOT EXISTS materials (
        id VARCHAR(50) PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        color VARCHAR(50) NOT NULL,
        material_type VARCHAR(50),
        shop_link TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Ensure user_filaments table exists
    // Note: Using SET FOREIGN_KEY_CHECKS=0 to avoid issues if tables don't exist yet
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_filaments (
        id VARCHAR(50) PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        material_id VARCHAR(50) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_material_id (material_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_material (user_id, material_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    switch ($method) {
        case 'GET':
            // Get filaments for a specific user
            $userId = $_GET['userId'] ?? null;
            
            if (!$userId) {
                sendJSON(['error' => 'User ID required'], 400);
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    uf.id,
                    uf.user_id,
                    uf.material_id,
                    uf.quantity,
                    uf.created_at,
                    uf.updated_at,
                    m.name as material_name,
                    m.color,
                    m.material_type,
                    m.shop_link
                FROM user_filaments uf
                JOIN materials m ON uf.material_id = m.id
                WHERE uf.user_id = ?
                ORDER BY uf.created_at DESC
            ");
            $stmt->execute([$userId]);
            $filaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no filaments found, return empty array
            if (empty($filaments)) {
                sendJSON([]);
                return;
            }
            
            $result = array_map(function($f) {
                return [
                    'id' => $f['id'],
                    'userId' => $f['user_id'],
                    'materialId' => $f['material_id'],
                    'quantity' => (int)$f['quantity'],
                    'name' => $f['material_name'],
                    'color' => $f['color'],
                    'materialType' => $f['material_type'],
                    'shopLink' => $f['shop_link'],
                    'createdAt' => $f['created_at'],
                    'updatedAt' => $f['updated_at']
                ];
            }, $filaments);
            
            sendJSON($result);
            break;

        case 'POST':
            // Assign filament to user (or update quantity if exists)
            $data = getRequestBody();
            
            if (!isset($data['userId']) || !isset($data['materialId'])) {
                sendJSON(['error' => 'User ID and Material ID required'], 400);
            }
            
            $userId = $data['userId'];
            $materialId = $data['materialId'];
            $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
            
            // Verify material exists
            $stmt = $pdo->prepare("SELECT id FROM materials WHERE id = ?");
            $stmt->execute([$materialId]);
            if (!$stmt->fetch()) {
                sendJSON(['error' => 'Material not found'], 404);
            }
            
            // Verify user exists and is a client (role = 'user')
            $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if (!$user) {
                sendJSON(['error' => 'User not found'], 404);
            }
            if ($user['role'] !== 'user') {
                sendJSON(['error' => 'Can only assign filaments to clients (users)'], 400);
            }
            
            // Check if already exists
            $stmt = $pdo->prepare("SELECT id, quantity FROM user_filaments WHERE user_id = ? AND material_id = ?");
            $stmt->execute([$userId, $materialId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update quantity
                $newQuantity = $existing['quantity'] + $quantity;
                $stmt = $pdo->prepare("UPDATE user_filaments SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$newQuantity, $existing['id']]);
                sendJSON(['success' => true, 'id' => $existing['id'], 'quantity' => $newQuantity, 'message' => 'Quantity updated']);
            } else {
                // Create new
                $id = 'user_filament_' . uniqid();
                $stmt = $pdo->prepare("INSERT INTO user_filaments (id, user_id, material_id, quantity) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $userId, $materialId, $quantity]);
                sendJSON(['success' => true, 'id' => $id, 'quantity' => $quantity], 201);
            }
            break;

        case 'PUT':
            // Update quantity
            $id = $_GET['id'] ?? null;
            $data = getRequestBody();
            
            if (!$id) {
                sendJSON(['error' => 'User filament ID required'], 400);
            }
            
            if (!isset($data['quantity']) || $data['quantity'] < 0) {
                sendJSON(['error' => 'Valid quantity required'], 400);
            }
            
            $quantity = (int)$data['quantity'];
            
            if ($quantity === 0) {
                // Delete if quantity is 0
                $stmt = $pdo->prepare("DELETE FROM user_filaments WHERE id = ?");
                $stmt->execute([$id]);
                sendJSON(['success' => true, 'message' => 'Filament removed']);
            } else {
                $stmt = $pdo->prepare("UPDATE user_filaments SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$quantity, $id]);
                
                if ($stmt->rowCount() === 0) {
                    sendJSON(['error' => 'User filament not found'], 404);
                }
                
                sendJSON(['success' => true, 'quantity' => $quantity]);
            }
            break;

        case 'DELETE':
            // Remove filament from user
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                sendJSON(['error' => 'User filament ID required'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM user_filaments WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'User filament not found'], 404);
            }
            
            sendJSON(['success' => true]);
            break;

        default:
            sendJSON(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    http_response_code(500);
    sendJSON(['error' => 'Database error: ' . $e->getMessage()]);
}

