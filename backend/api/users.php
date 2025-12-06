<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));

// Extract user ID if present (path: /Apps/Printing/api/users/:id)
$userId = null;
if (count($pathParts) >= 4 && $pathParts[3] === 'users' && isset($pathParts[4])) {
    $userId = $pathParts[4];
}

try {
    $pdo = getDBConnection();

    switch ($method) {
        case 'GET':
            if ($userId) {
                // Get single user
                $stmt = $pdo->prepare("SELECT id, username, email, password, role, created_at, updated_at FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    sendJSON(['error' => 'User not found'], 404);
                }
                
                sendJSON([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'createdAt' => $user['created_at'],
                    'updatedAt' => $user['updated_at']
                ]);
            } else {
                // Get all users
                $stmt = $pdo->query("SELECT id, username, email, password, role, created_at, updated_at FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll();
                
                $result = array_map(function($user) {
                    return [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'password' => $user['password'],
                        'role' => $user['role'],
                        'createdAt' => $user['created_at'],
                        'updatedAt' => $user['updated_at']
                    ];
                }, $users);
                
                sendJSON($result);
            }
            break;

        case 'POST':
            // Create new user
            $data = getRequestBody();
            
            if (!isset($data['id']) || !isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                sendJSON(['error' => 'Missing required fields'], 400);
            }
            
            // Check if email already exists in database
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                sendJSON(['error' => 'Email already registered'], 409);
            }
            
            // Check if username already exists in database
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$data['username']]);
            if ($stmt->fetch()) {
                sendJSON(['error' => 'Username already taken'], 409);
            }
            
            // Create new user
            try {
                $stmt = $pdo->prepare("INSERT INTO users (id, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['id'],
                    $data['username'],
                    $data['email'],
                    $data['password'],
                    $data['role'] ?? 'user'
                ]);
                
                sendJSON(['success' => true, 'id' => $data['id']], 201);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry (fallback check)
                    sendJSON(['error' => 'Username or email already exists'], 409);
                }
                throw $e;
            }
            break;

        case 'PUT':
            // Update user
            if (!$userId) {
                sendJSON(['error' => 'User ID required'], 400);
            }
            
            $data = getRequestBody();
            $updates = [];
            $values = [];
            
            if (isset($data['username'])) {
                $updates[] = 'username = ?';
                $values[] = $data['username'];
            }
            if (isset($data['email'])) {
                $updates[] = 'email = ?';
                $values[] = $data['email'];
            }
            if (isset($data['password'])) {
                $updates[] = 'password = ?';
                $values[] = $data['password'];
            }
            if (isset($data['role'])) {
                $updates[] = 'role = ?';
                $values[] = $data['role'];
            }
            
            if (empty($updates)) {
                sendJSON(['error' => 'No fields to update'], 400);
            }
            
            $values[] = $userId;
            $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?");
            $stmt->execute($values);
            
            sendJSON(['success' => true]);
            break;

        case 'DELETE':
            // Delete user
            if (!$userId) {
                sendJSON(['error' => 'User ID required'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            if ($stmt->rowCount() === 0) {
                sendJSON(['error' => 'User not found'], 404);
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

