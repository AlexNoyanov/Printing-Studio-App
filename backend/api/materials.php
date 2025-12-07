<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));

// Extract material ID if present (from path or query)
$materialId = null;
if (count($pathParts) >= 4 && $pathParts[3] === 'materials' && isset($pathParts[4])) {
    $materialId = $pathParts[4];
}
// Also check query parameter for ID
if (!$materialId && isset($_GET['id'])) {
    $materialId = $_GET['id'];
}

// Get query parameters
$userId = $_GET['userId'] ?? null;

try {
    $pdo = getDBConnection();

    switch ($method) {
        case 'GET':
            if ($materialId) {
                // Get single material
                $stmt = $pdo->prepare("SELECT id, user_id, name, color, material_type, shop_link, created_at, updated_at FROM materials WHERE id = ?");
                $stmt->execute([$materialId]);
                $material = $stmt->fetch();
                
                if (!$material) {
                    sendJSON(['error' => 'Material not found'], 404);
                }
                
                sendJSON([
                    'id' => $material['id'],
                    'userId' => $material['user_id'],
                    'name' => $material['name'],
                    'color' => $material['color'],
                    'materialType' => $material['material_type'],
                    'shopLink' => $material['shop_link'],
                    'createdAt' => $material['created_at'],
                    'updatedAt' => $material['updated_at']
                ]);
            } else {
                // Get all materials (optionally filtered by userId)
                $query = "SELECT id, user_id, name, color, material_type, shop_link, created_at, updated_at FROM materials";
                $params = [];
                
                if ($userId) {
                    $query .= " WHERE user_id = ?";
                    $params[] = $userId;
                }
                
                $query .= " ORDER BY material_type, name";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $materials = $stmt->fetchAll();
                
                $result = array_map(function($material) {
                    return [
                        'id' => $material['id'],
                        'userId' => $material['user_id'],
                        'name' => $material['name'],
                        'color' => $material['color'],
                        'materialType' => $material['material_type'],
                        'shopLink' => $material['shop_link'],
                        'createdAt' => $material['created_at'],
                        'updatedAt' => $material['updated_at']
                    ];
                }, $materials);
                
                sendJSON($result);
            }
            break;

        case 'POST':
            // Create new material
            $data = getRequestBody();
            
            if (!isset($data['id']) || !isset($data['userId']) || !isset($data['name']) || 
                !isset($data['color']) || !isset($data['materialType'])) {
                sendJSON(['error' => 'Missing required fields'], 400);
            }
            
            $stmt = $pdo->prepare("INSERT INTO materials (id, user_id, name, color, material_type, shop_link) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['id'],
                $data['userId'],
                $data['name'],
                $data['color'],
                $data['materialType'],
                $data['shopLink'] ?? null
            ]);
            
            sendJSON(['success' => true, 'id' => $data['id']], 201);
            break;

        case 'PUT':
            // Update material
            if (!$materialId) {
                sendJSON(['error' => 'Material ID required'], 400);
            }
            
            $data = getRequestBody();
            $updates = [];
            $values = [];
            
            if (isset($data['name'])) {
                $updates[] = 'name = ?';
                $values[] = $data['name'];
            }
            if (isset($data['color'])) {
                $updates[] = 'color = ?';
                $values[] = $data['color'];
            }
            if (isset($data['materialType'])) {
                $updates[] = 'material_type = ?';
                $values[] = $data['materialType'];
            }
            if (isset($data['shopLink'])) {
                $updates[] = 'shop_link = ?';
                $values[] = $data['shopLink'] ?: null;
            }
            
            if (empty($updates)) {
                sendJSON(['error' => 'No fields to update'], 400);
            }
            
            $values[] = $materialId;
            $stmt = $pdo->prepare("UPDATE materials SET " . implode(', ', $updates) . " WHERE id = ?");
            $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'Material not found'], 404);
            }
            
            sendJSON(['success' => true]);
            break;

        case 'DELETE':
            // Delete material
            if (!$materialId) {
                sendJSON(['error' => 'Material ID required'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
            $stmt->execute([$materialId]);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'Material not found'], 404);
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

