# Update Database Username

## Issue
The database username in `config.php` is incorrect. It should be `alexn_printing_admin` instead of `printing_admin`.

## Fix Required

Update the `backend/config.php` file on your server at `/Apps/Printing/backend/config.php`

### Current (Wrong):
```php
define('DB_USER', 'printing_admin');
```

### Should Be:
```php
define('DB_USER', 'alexn_printing_admin');
```

## Steps to Fix

### Option 1: Edit via FTP/File Manager

1. Connect to your server via FTP or file manager
2. Navigate to `/Apps/Printing/backend/`
3. Open `config.php`
4. Change line 6 from:
   ```php
   define('DB_USER', 'printing_admin');
   ```
   to:
   ```php
   define('DB_USER', 'alexn_printing_admin');
   ```
5. Save the file

### Option 2: Create New Config File

If you prefer, create a new `config.php` with this content:

```php
<?php
// Database configuration for Printing App
define('DB_HOST', 's136.webhost1.ru');
define('DB_USER', 'alexn_printing_admin');
define('DB_PASS', 'vL2tI2sV7c');
define('DB_NAME', 'printing');
define('DB_CHARSET', 'utf8mb4');

// API Base Path
define('API_BASE', '/Apps/Printing/api');

// CORS headers
if (!headers_sent()) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 3600');
    header('Content-Type: application/json; charset=utf-8');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && !headers_sent()) {
    http_response_code(200);
    exit();
}

// Database connection function
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit();
    }
}

// Helper function to send JSON response
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// Helper function to get request body
function getRequestBody() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}
```

## Test After Update

1. Upload `backend/test_db_alexn.php` to `/Apps/Printing/backend/test_db_alexn.php`
2. Visit: `https://noyanov.com/Apps/Printing/backend/test_db_alexn.php`
3. Should return JSON with `"status": "success"`

Or test via API:
```bash
curl "https://noyanov.com/Apps/Printing/api/users.php"
```

Should return JSON (even if empty array), not an error.

## Verify Database Permissions

After updating the username, you may still need to grant permissions in MySQL:

```sql
GRANT ALL PRIVILEGES ON printing.* TO 'alexn_printing_admin'@'%' IDENTIFIED BY 'vL2tI2sV7c';
FLUSH PRIVILEGES;
```

Or for specific IP:
```sql
GRANT ALL PRIVILEGES ON printing.* TO 'alexn_printing_admin'@'2a0a:8d80:0:9123::136' IDENTIFIED BY 'vL2tI2sV7c';
FLUSH PRIVILEGES;
```

