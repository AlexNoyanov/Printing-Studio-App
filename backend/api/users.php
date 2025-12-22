<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Parse the URI
$parsedUrl = parse_url($requestUri);
$pathInfo = $parsedUrl['path'] ?? '';
$queryString = $parsedUrl['query'] ?? '';

// Parse query string manually to ensure we get the id parameter
parse_str($queryString, $queryParams);

$pathParts = explode('/', trim($pathInfo, '/'));

// Extract user ID if present - check query parameter FIRST (most reliable)
$userId = null;

// First priority: Check query parameter (works for /users.php?id=123)
if (isset($queryParams['id']) && !empty($queryParams['id'])) {
    $userId = $queryParams['id'];
}
// Also check $_GET as fallback (in case parse_str didn't work)
elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $userId = $_GET['id'];
}
// Second priority: Check path format (e.g., /users/123)
else {
    // Find 'users' in path and get next segment as ID
    $usersIndex = array_search('users', $pathParts);
    if ($usersIndex !== false && isset($pathParts[$usersIndex + 1])) {
        $userId = $pathParts[$usersIndex + 1];
        // Remove .php extension if present
        $userId = str_replace('.php', '', $userId);
    }
}

try {
    $pdo = getDBConnection();

    switch ($method) {
        case 'GET':
            if ($userId) {
                // Get single user
                $stmt = $pdo->prepare("SELECT id, username, email, password, role, rating, rating_count, created_at, updated_at FROM users WHERE id = ?");
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
                    'rating' => $user['rating'] !== null ? floatval($user['rating']) : null,
                    'ratingCount' => intval($user['rating_count'] ?? 0),
                    'createdAt' => $user['created_at'],
                    'updatedAt' => $user['updated_at']
                ]);
            } else {
                // Get all users
                $stmt = $pdo->query("SELECT id, username, email, password, role, rating, rating_count, created_at, updated_at FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll();
                
                $result = array_map(function($user) {
                    return [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'password' => $user['password'],
                        'role' => $user['role'],
                        'rating' => $user['rating'] !== null ? floatval($user['rating']) : null,
                        'ratingCount' => intval($user['rating_count'] ?? 0),
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
            // Debug logging
            error_log("PUT request - userId: " . ($userId ? $userId : 'NULL'));
            error_log("GET params: " . print_r($_GET, true));
            error_log("Request URI: " . $_SERVER['REQUEST_URI']);
            
            if (!$userId) {
                sendJSON(['error' => 'User ID required', 'debug' => [
                    'userId' => $userId,
                    'GET_id' => isset($_GET['id']) ? $_GET['id'] : 'not set',
                    'pathParts' => $pathParts,
                    'requestUri' => $_SERVER['REQUEST_URI']
                ]], 400);
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
            if (isset($data['rating'])) {
                $rating = floatval($data['rating']);
                // Validate rating is between 0 and 10
                if ($rating < 0 || $rating > 10) {
                    sendJSON(['error' => 'Rating must be between 0 and 10'], 400);
                }
                
                // Try to get current rating and count (handle case where columns might not exist yet)
                try {
                    $stmt = $pdo->prepare("SELECT rating, rating_count FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $current = $stmt->fetch();
                    $currentRating = isset($current['rating']) && $current['rating'] !== null ? floatval($current['rating']) : null;
                    $currentCount = isset($current['rating_count']) ? intval($current['rating_count']) : 0;
                } catch (PDOException $e) {
                    // If columns don't exist, treat as first rating
                    $currentRating = null;
                    $currentCount = 0;
                }
                
                // Calculate new average: (old_rating * old_count + new_rating) / (old_count + 1)
                if ($currentRating !== null && $currentCount > 0) {
                    $newRating = (($currentRating * $currentCount) + $rating) / ($currentCount + 1);
                } else {
                    $newRating = $rating;
                }
                
                $updates[] = 'rating = ?';
                $values[] = $newRating;
                // Handle rating_count increment separately (it's a SQL expression, not a placeholder)
                $ratingCountUpdate = 'rating_count = COALESCE(rating_count, 0) + 1';
            }
            
            if (empty($updates)) {
                sendJSON(['error' => 'No fields to update'], 400);
            }
            
            // Add rating_count update if rating was set
            $updateClause = implode(', ', $updates);
            if (isset($ratingCountUpdate)) {
                $updateClause .= ', ' . $ratingCountUpdate;
            }
            
            $values[] = $userId;
            $stmt = $pdo->prepare("UPDATE users SET " . $updateClause . " WHERE id = ?");
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

