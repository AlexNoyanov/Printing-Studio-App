<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));

// Extract order ID if present (from path or query)
$orderId = null;
if (count($pathParts) >= 4 && $pathParts[3] === 'orders' && isset($pathParts[4])) {
    $orderId = $pathParts[4];
}
// Also check query parameter for ID
if (!$orderId && isset($_GET['id'])) {
    $orderId = $_GET['id'];
}

// Get query parameters
$userId = $_GET['userId'] ?? null;
$status = $_GET['status'] ?? null;

try {
    $pdo = getDBConnection();

    switch ($method) {
        case 'GET':
            if ($orderId) {
                // Get single order with colors
                $stmt = $pdo->prepare("SELECT id, user_id, user_name, model_link, comment, status, created_at, updated_at FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    sendJSON(['error' => 'Order not found'], 404);
                }
                
                // Get order colors
                $stmt = $pdo->prepare("SELECT color_name FROM order_colors WHERE order_id = ? ORDER BY color_name");
                $stmt->execute([$orderId]);
                $colorRows = $stmt->fetchAll();
                $colors = array_column($colorRows, 'color_name');
                
                sendJSON([
                    'id' => $order['id'],
                    'userId' => $order['user_id'],
                    'userName' => $order['user_name'],
                    'modelLink' => $order['model_link'],
                    'comment' => $order['comment'],
                    'status' => $order['status'],
                    'colors' => $colors,
                    'createdAt' => $order['created_at'],
                    'updatedAt' => $order['updated_at']
                ]);
            } else {
                // Get all orders (optionally filtered)
                $query = "SELECT o.id, o.user_id, o.user_name, o.model_link, o.comment, o.status, 
                                 o.created_at, o.updated_at,
                                 GROUP_CONCAT(oc.color_name ORDER BY oc.color_name SEPARATOR ',') as colors
                          FROM orders o
                          LEFT JOIN order_colors oc ON o.id = oc.order_id";
                
                $conditions = [];
                $params = [];
                
                if ($userId) {
                    $conditions[] = "o.user_id = ?";
                    $params[] = $userId;
                }
                if ($status) {
                    $conditions[] = "o.status = ?";
                    $params[] = $status;
                }
                
                if (!empty($conditions)) {
                    $query .= " WHERE " . implode(' AND ', $conditions);
                }
                
                $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $orders = $stmt->fetchAll();
                
                $result = array_map(function($order) {
                    return [
                        'id' => $order['id'],
                        'userId' => $order['user_id'],
                        'userName' => $order['user_name'],
                        'modelLink' => $order['model_link'],
                        'comment' => $order['comment'],
                        'status' => $order['status'],
                        'colors' => $order['colors'] ? explode(',', $order['colors']) : [],
                        'createdAt' => $order['created_at'],
                        'updatedAt' => $order['updated_at']
                    ];
                }, $orders);
                
                sendJSON($result);
            }
            break;

        case 'POST':
            // Create new order
            $data = getRequestBody();
            
            if (!isset($data['id']) || !isset($data['userId']) || !isset($data['userName']) || 
                !isset($data['modelLink']) || !isset($data['colors']) || !is_array($data['colors']) || 
                empty($data['colors'])) {
                sendJSON(['error' => 'Missing required fields'], 400);
            }
            
            $pdo->beginTransaction();
            
            try {
                // Insert order
                $stmt = $pdo->prepare("INSERT INTO orders (id, user_id, user_name, model_link, comment, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['id'],
                    $data['userId'],
                    $data['userName'],
                    $data['modelLink'],
                    $data['comment'] ?? null,
                    $data['status'] ?? 'Created'
                ]);
                
                // Insert order colors
                $stmt = $pdo->prepare("INSERT INTO order_colors (order_id, color_id, color_name) VALUES (?, ?, ?)");
                foreach ($data['colors'] as $colorName) {
                    if (empty($colorName)) {
                        continue; // Skip empty color names
                    }
                    
                    // Try to find color ID for this user
                    $colorStmt = $pdo->prepare("SELECT id FROM colors WHERE name = ? AND user_id = ? LIMIT 1");
                    $colorStmt->execute([$colorName, $data['userId']]);
                    $colorRow = $colorStmt->fetch();
                    $colorId = $colorRow ? $colorRow['id'] : null;
                    
                    // If color doesn't exist, create it automatically
                    if ($colorId === null) {
                        // Generate a color ID
                        $colorId = 'color_' . time() . '_' . rand(1000, 9999);
                        
                        // Create the color entry
                        $colorInsertStmt = $pdo->prepare("INSERT INTO colors (id, user_id, name, value, filament_link) VALUES (?, ?, ?, ?, ?)");
                        $colorInsertStmt->execute([
                            $colorId,
                            $data['userId'],
                            $colorName,
                            $colorName, // Use color name as value if no hex provided
                            null
                        ]);
                    }
                    
                    $stmt->execute([$data['id'], $colorId, $colorName]);
                }
                
                $pdo->commit();
                sendJSON(['success' => true, 'id' => $data['id']], 201);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'PUT':
            // Update order
            if (!$orderId) {
                sendJSON(['error' => 'Order ID required'], 400);
            }
            
            $data = getRequestBody();
            $pdo->beginTransaction();
            
            try {
                // Update order fields
                $updates = [];
                $values = [];
                
                if (isset($data['modelLink'])) {
                    $updates[] = 'model_link = ?';
                    $values[] = $data['modelLink'];
                }
                if (isset($data['comment'])) {
                    $updates[] = 'comment = ?';
                    $values[] = $data['comment'] ?? null;
                }
                if (isset($data['status'])) {
                    $updates[] = 'status = ?';
                    $values[] = $data['status'];
                }
                
                if (!empty($updates)) {
                    $values[] = $orderId;
                    $stmt = $pdo->prepare("UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?");
                    $stmt->execute($values);
                    
                    if ($stmt->rowCount() === 0) {
                        $pdo->rollBack();
                        sendJSON(['error' => 'Order not found'], 404);
                    }
                }
                
                // Update colors if provided
                if (isset($data['colors']) && is_array($data['colors'])) {
                    // Delete existing order colors
                    $stmt = $pdo->prepare("DELETE FROM order_colors WHERE order_id = ?");
                    $stmt->execute([$orderId]);
                    
                    // Insert new order colors
                    $stmt = $pdo->prepare("INSERT INTO order_colors (order_id, color_id, color_name) VALUES (?, ?, ?)");
                    foreach ($data['colors'] as $colorName) {
                        $stmt->execute([$orderId, null, $colorName]);
                    }
                }
                
                $pdo->commit();
                sendJSON(['success' => true]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'DELETE':
            // Delete order
            if (!$orderId) {
                sendJSON(['error' => 'Order ID required'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'Order not found'], 404);
            }
            
            sendJSON(['success' => true]);
            break;

        default:
            sendJSON(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendJSON(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendJSON(['error' => 'Server error: ' . $e->getMessage()], 500);
}

