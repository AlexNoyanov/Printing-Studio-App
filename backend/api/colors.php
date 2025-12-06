<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));

// Extract color ID if present
$colorId = null;
if (count($pathParts) >= 4 && $pathParts[3] === 'colors' && isset($pathParts[4])) {
    $colorId = $pathParts[4];
}

// Get query parameters
$userId = $_GET['userId'] ?? null;

try {
    $pdo = getDBConnection();

    switch ($method) {
        case 'GET':
            if ($colorId) {
                // Get single color
                $stmt = $pdo->prepare("SELECT id, user_id, name, value, filament_link, created_at, updated_at FROM colors WHERE id = ?");
                $stmt->execute([$colorId]);
                $color = $stmt->fetch();
                
                if (!$color) {
                    sendJSON(['error' => 'Color not found'], 404);
                }
                
                sendJSON([
                    'id' => $color['id'],
                    'userId' => $color['user_id'],
                    'name' => $color['name'],
                    'value' => $color['value'],
                    'hex' => $color['value'],
                    'filamentLink' => $color['filament_link'],
                    'createdAt' => $color['created_at'],
                    'updatedAt' => $color['updated_at']
                ]);
            } else {
                // Get all colors (optionally filtered by userId)
                $query = "SELECT id, user_id, name, value, filament_link, created_at, updated_at FROM colors";
                $params = [];
                
                if ($userId) {
                    $query .= " WHERE user_id = ?";
                    $params[] = $userId;
                }
                
                $query .= " ORDER BY created_at DESC";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $colors = $stmt->fetchAll();
                
                $result = array_map(function($color) {
                    return [
                        'id' => $color['id'],
                        'userId' => $color['user_id'],
                        'name' => $color['name'],
                        'value' => $color['value'],
                        'hex' => $color['value'],
                        'filamentLink' => $color['filament_link'],
                        'createdAt' => $color['created_at'],
                        'updatedAt' => $color['updated_at']
                    ];
                }, $colors);
                
                sendJSON($result);
            }
            break;

        case 'POST':
            // Create new color
            $data = getRequestBody();
            
            if (!isset($data['id']) || !isset($data['userId']) || !isset($data['name']) || !isset($data['value'])) {
                sendJSON(['error' => 'Missing required fields'], 400);
            }
            
            $stmt = $pdo->prepare("INSERT INTO colors (id, user_id, name, value, filament_link) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['id'],
                $data['userId'],
                $data['name'],
                $data['value'],
                $data['filamentLink'] ?? null
            ]);
            
            sendJSON(['success' => true, 'id' => $data['id']], 201);
            break;

        case 'PUT':
            // Update color
            if (!$colorId) {
                sendJSON(['error' => 'Color ID required'], 400);
            }
            
            $data = getRequestBody();
            $updates = [];
            $values = [];
            
            if (isset($data['name'])) {
                $updates[] = 'name = ?';
                $values[] = $data['name'];
            }
            if (isset($data['value']) || isset($data['hex'])) {
                $updates[] = 'value = ?';
                $values[] = $data['value'] ?? $data['hex'];
            }
            if (isset($data['filamentLink'])) {
                $updates[] = 'filament_link = ?';
                $values[] = $data['filamentLink'] ?: null;
            }
            
            if (empty($updates)) {
                sendJSON(['error' => 'No fields to update'], 400);
            }
            
            $values[] = $colorId;
            $stmt = $pdo->prepare("UPDATE colors SET " . implode(', ', $updates) . " WHERE id = ?");
            $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'Color not found'], 404);
            }
            
            sendJSON(['success' => true]);
            break;

        case 'DELETE':
            // Delete color
            if (!$colorId) {
                sendJSON(['error' => 'Color ID required'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM colors WHERE id = ?");
            $stmt->execute([$colorId]);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'Color not found'], 404);
            }
            
            sendJSON(['success' => true]);
            break;

        default:
            sendJSON(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    sendJSON(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    sendJSON(['error' => 'Server error: ' . $e->getMessage()], 500);
}

