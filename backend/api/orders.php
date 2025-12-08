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
    
    // Helper function to ensure order_links table exists
    $ensureOrderLinksTable = function() use ($pdo) {
        try {
            // Check if table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'order_links'");
            if ($stmt->rowCount() === 0) {
                // Table doesn't exist, create it
                $pdo->exec("CREATE TABLE order_links (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id VARCHAR(50) NOT NULL,
                    link_url TEXT NOT NULL,
                    copies INT NOT NULL DEFAULT 1,
                    printed BOOLEAN NOT NULL DEFAULT FALSE,
                    link_order INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_order_id (order_id),
                    INDEX idx_link_order (link_order),
                    INDEX idx_printed (printed),
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } else {
                // Check if printed column exists, add if not
                $stmt = $pdo->query("SHOW COLUMNS FROM order_links LIKE 'printed'");
                if ($stmt->rowCount() === 0) {
                    $pdo->exec("ALTER TABLE order_links ADD COLUMN printed BOOLEAN NOT NULL DEFAULT FALSE AFTER copies");
                    $pdo->exec("CREATE INDEX idx_printed ON order_links(printed)");
                }
            }
        } catch (PDOException $e) {
            // If table creation fails, log but don't throw (might already exist)
            error_log("Error ensuring order_links table: " . $e->getMessage());
        }
    };
    
    // Ensure table exists before any operations
    $ensureOrderLinksTable();

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
                
                // Get order links with copies and printed status
                $stmt = $pdo->prepare("SELECT id, link_url, copies, printed FROM order_links WHERE order_id = ? ORDER BY link_order, id");
                $stmt->execute([$orderId]);
                $linkRows = $stmt->fetchAll();
                $links = array_column($linkRows, 'link_url');
                $linksWithCopies = array_map(function($row) {
                    return [
                        'id' => $row['id'],
                        'url' => $row['link_url'], 
                        'copies' => intval($row['copies'] ?? 1),
                        'printed' => (bool)($row['printed'] ?? false)
                    ];
                }, $linkRows);
                
                // For backward compatibility, include modelLink (first link or old model_link)
                $modelLink = !empty($links) ? $links[0] : ($order['model_link'] ?? '');
                
                sendJSON([
                    'id' => $order['id'],
                    'userId' => $order['user_id'],
                    'userName' => $order['user_name'],
                    'modelLink' => $modelLink, // Backward compatibility
                    'modelLinks' => $links, // New: array of all links (for backward compatibility)
                    'modelLinksWithCopies' => $linksWithCopies, // New: array with copies
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
                
                // Get links for all orders with copies and printed status
                $orderIds = array_column($orders, 'id');
                $linksMap = [];
                $linksWithCopiesMap = [];
                if (!empty($orderIds)) {
                    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
                    $linkStmt = $pdo->prepare("SELECT id, order_id, link_url, copies, printed FROM order_links WHERE order_id IN ($placeholders) ORDER BY link_order, id");
                    $linkStmt->execute($orderIds);
                    $linkRows = $linkStmt->fetchAll();
                    
                    foreach ($linkRows as $linkRow) {
                        $orderId = $linkRow['order_id'];
                        if (!isset($linksMap[$orderId])) {
                            $linksMap[$orderId] = [];
                            $linksWithCopiesMap[$orderId] = [];
                        }
                        $linksMap[$orderId][] = $linkRow['link_url'];
                        $linksWithCopiesMap[$orderId][] = [
                            'id' => $linkRow['id'],
                            'url' => $linkRow['link_url'],
                            'copies' => intval($linkRow['copies'] ?? 1),
                            'printed' => (bool)($linkRow['printed'] ?? false)
                        ];
                    }
                }
                
                $result = array_map(function($order) use ($linksMap, $linksWithCopiesMap) {
                    $links = $linksMap[$order['id']] ?? [];
                    $linksWithCopies = $linksWithCopiesMap[$order['id']] ?? [];
                    $modelLink = !empty($links) ? $links[0] : ($order['model_link'] ?? '');
                    
                    return [
                        'id' => $order['id'],
                        'userId' => $order['user_id'],
                        'userName' => $order['user_name'],
                        'modelLink' => $modelLink, // Backward compatibility
                        'modelLinks' => $links, // New: array of all links (for backward compatibility)
                        'modelLinksWithCopies' => $linksWithCopies, // New: array with copies
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
            
            // Support both modelLink (single) and modelLinks (array) for backward compatibility
            $modelLinks = [];
            if (isset($data['modelLinks']) && is_array($data['modelLinks'])) {
                $modelLinks = array_filter($data['modelLinks'], function($link) {
                    return !empty(trim($link));
                });
            } elseif (isset($data['modelLink']) && !empty($data['modelLink'])) {
                $modelLinks = [$data['modelLink']];
            }
            
            if (!isset($data['id']) || !isset($data['userId']) || !isset($data['userName']) || 
                empty($modelLinks) || !isset($data['colors']) || !is_array($data['colors']) || 
                empty($data['colors'])) {
                sendJSON(['error' => 'Missing required fields'], 400);
            }
            
            $pdo->beginTransaction();
            
            try {
                // Use first link as model_link for backward compatibility
                $firstLink = $modelLinks[0];
                
                // Insert order
                $stmt = $pdo->prepare("INSERT INTO orders (id, user_id, user_name, model_link, comment, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['id'],
                    $data['userId'],
                    $data['userName'],
                    $firstLink,
                    $data['comment'] ?? null,
                    $data['status'] ?? 'Created'
                ]);
                
                // Insert order links with copies
                // Support both old format (array of strings) and new format (array of objects with url and copies)
                $linkStmt = $pdo->prepare("INSERT INTO order_links (order_id, link_url, copies, printed, link_order) VALUES (?, ?, ?, ?, ?)");
                $linksWithCopies = [];
                
                if (isset($data['modelLinksWithCopies']) && is_array($data['modelLinksWithCopies'])) {
                    // New format: array of objects with url and copies
                    $linksWithCopies = $data['modelLinksWithCopies'];
                } else {
                    // Old format: array of strings, default to 1 copy each
                    foreach ($modelLinks as $link) {
                        $linksWithCopies[] = ['url' => $link, 'copies' => 1];
                    }
                }
                
                foreach ($linksWithCopies as $index => $linkData) {
                    $url = is_array($linkData) ? $linkData['url'] : $linkData;
                    $copies = is_array($linkData) && isset($linkData['copies']) ? max(1, intval($linkData['copies'])) : 1;
                    $printed = false; // New links are not printed by default
                    $linkStmt->execute([$data['id'], $url, $copies, $printed, $index]);
                }
                
                // Insert order colors
                $stmt = $pdo->prepare("INSERT INTO order_colors (order_id, color_id, color_name) VALUES (?, ?, ?)");
                foreach ($data['colors'] as $colorName) {
                    if (empty($colorName)) {
                        continue; // Skip empty color names
                    }
                    
                    // Try to find color ID for this user (check both colors and materials tables)
                    $colorStmt = $pdo->prepare("SELECT id, value FROM colors WHERE name = ? AND user_id = ? LIMIT 1");
                    $colorStmt->execute([$colorName, $data['userId']]);
                    $colorRow = $colorStmt->fetch();
                    $colorId = $colorRow ? $colorRow['id'] : null;
                    $hexValue = $colorRow ? $colorRow['value'] : null;
                    
                    // If not found in colors, check materials table (filaments)
                    if ($colorId === null) {
                        $materialStmt = $pdo->prepare("SELECT id, color FROM materials WHERE name = ? AND user_id = ? LIMIT 1");
                        $materialStmt->execute([$colorName, $data['userId']]);
                        $materialRow = $materialStmt->fetch();
                        if ($materialRow) {
                            $colorId = $materialRow['id'];
                            $hexValue = $materialRow['color'];
                        }
                    }
                    
                    // If color doesn't exist, create it automatically
                    if ($colorId === null) {
                        // Generate a color ID
                        $colorId = 'color_' . time() . '_' . rand(1000, 9999);
                        
                        // Default hex color if no specific color provided
                        // Try to extract hex from colorName if it looks like a hex code, otherwise use default
                        if (!$hexValue) {
                            $hexValue = '#FFFFFF'; // Default white
                            if (preg_match('/^#?[0-9A-Fa-f]{6}$/', $colorName)) {
                                $hexValue = strpos($colorName, '#') === 0 ? $colorName : '#' . $colorName;
                            } elseif (strlen($colorName) <= 7 && preg_match('/^#[0-9A-Fa-f]{3,6}$/', $colorName)) {
                                $hexValue = $colorName;
                            }
                        }
                        
                        // Ensure hex value is not longer than 50 characters (new column limit)
                        $hexValue = substr($hexValue, 0, 50);
                        
                        // Create the color entry
                        $colorInsertStmt = $pdo->prepare("INSERT INTO colors (id, user_id, name, value, filament_link) VALUES (?, ?, ?, ?, ?)");
                        $colorInsertStmt->execute([
                            $colorId,
                            $data['userId'],
                            $colorName,
                            $hexValue, // Use hex value, not color name
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

        case 'PATCH':
            // Update printed status of a specific order link
            $data = getRequestBody();
            
            if (!isset($data['linkId']) || !isset($data['printed'])) {
                sendJSON(['error' => 'Link ID and printed status required'], 400);
            }
            
            $linkId = intval($data['linkId']);
            $printed = (bool)$data['printed'];
            
            $stmt = $pdo->prepare("UPDATE order_links SET printed = ? WHERE id = ?");
            $stmt->execute([$printed, $linkId]);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'Order link not found'], 404);
            }
            
            sendJSON(['success' => true, 'printed' => $printed]);
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

