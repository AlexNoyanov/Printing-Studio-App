<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDBConnection();
    
    // Ensure material_types table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS material_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Insert default types if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM material_types");
    $count = $stmt->fetch()['count'];
    if ($count == 0) {
        $defaultTypes = ['PLA', 'PETG', 'ABS', 'TPU', 'ASA', 'PC', 'Nylon', 'Wood', 'Metal', 'Other'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO material_types (name) VALUES (?)");
        foreach ($defaultTypes as $type) {
            $stmt->execute([$type]);
        }
    }

    switch ($method) {
        case 'GET':
            // Get all material types
            $stmt = $pdo->query("SELECT id, name, created_at FROM material_types ORDER BY name");
            $types = $stmt->fetchAll();
            
            $result = array_map(function($type) {
                return [
                    'id' => $type['id'],
                    'name' => $type['name'],
                    'createdAt' => $type['created_at']
                ];
            }, $types);
            
            sendJSON($result);
            break;

        case 'POST':
            // Create new material type
            $data = getRequestBody();
            
            if (!isset($data['name']) || empty(trim($data['name']))) {
                sendJSON(['error' => 'Material type name is required'], 400);
            }
            
            $name = trim($data['name']);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO material_types (name) VALUES (?)");
                $stmt->execute([$name]);
                
                sendJSON(['success' => true, 'id' => $pdo->lastInsertId(), 'name' => $name], 201);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    sendJSON(['error' => 'Material type already exists'], 409);
                }
                throw $e;
            }
            break;

        case 'DELETE':
            // Delete material type
            $typeId = $_GET['id'] ?? null;
            
            if (!$typeId) {
                sendJSON(['error' => 'Material type ID required'], 400);
            }
            
            // Check if any materials are using this type
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materials WHERE material_type = (SELECT name FROM material_types WHERE id = ?)");
            $stmt->execute([$typeId]);
            $usage = $stmt->fetch()['count'];
            
            if ($usage > 0) {
                sendJSON(['error' => "Cannot delete material type: $usage material(s) are using it"], 409);
            }
            
            $stmt = $pdo->prepare("DELETE FROM material_types WHERE id = ?");
            $stmt->execute([$typeId]);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'Material type not found'], 404);
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

