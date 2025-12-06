# Deploy API Router to Fix CORS

## Problem
The frontend calls `/Apps/Printing/api/users` (without `.php`), but Nginx returns 404 because it can't find the file. The server needs a router at `/Apps/Printing/api/index.php` to handle these requests.

## Solution

Deploy the `api/index.php` file to your server at `/Apps/Printing/api/index.php`. This file will:
1. Set CORS headers
2. Route requests to the correct backend handler
3. Handle OPTIONS preflight requests

## Quick Deploy

### Option 1: Manual Upload
1. Upload `api/index.php` to `/Apps/Printing/api/index.php` on your server
2. Test: `https://noyanov.com/Apps/Printing/api/users`

### Option 2: Use Deployment Script
The deployment workflow will now include the `api/` directory.

### Option 3: Create File Directly on Server
Create `/Apps/Printing/api/index.php` with this content:

```php
<?php
// API Router - Routes requests from /Apps/Printing/api/* to backend handlers
$backendDir = dirname(__DIR__) . '/backend';
require_once $backendDir . '/cors.php';

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = preg_replace('#^/Apps/Printing/api/?#', '', $path);
$path = trim($path, '/');

if (empty($path)) {
    $path = 'health';
}

$pathParts = explode('/', $path);
$endpoint = $pathParts[0];

$apiDir = $backendDir . '/api/';
switch ($endpoint) {
    case 'users':
        require_once $apiDir . 'users.php';
        break;
    case 'colors':
        require_once $apiDir . 'colors.php';
        break;
    case 'orders':
        require_once $apiDir . 'orders.php';
        break;
    case 'health':
        require_once $apiDir . 'health.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found: ' . $endpoint]);
}
```

## Test After Deployment

```bash
# Should return JSON with CORS headers
curl "https://noyanov.com/Apps/Printing/api/users" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -v

# Should return 204 with CORS headers
curl -X OPTIONS "https://noyanov.com/Apps/Printing/api/users" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -H "Access-Control-Request-Method: GET" \
  -v
```

