# Deploy API PHP Files to Fix CORS

## Problem
The frontend is calling `/Apps/Printing/api/users.php` but CORS headers aren't being sent, causing browser to block the requests.

## Solution
Deploy the wrapper PHP files from the `api/` directory to `/Apps/Printing/api/` on your server.

## Files to Deploy

Upload these files to `/Apps/Printing/api/` on your server:

1. `api/users.php` → `/Apps/Printing/api/users.php`
2. `api/orders.php` → `/Apps/Printing/api/orders.php`
3. `api/colors.php` → `/Apps/Printing/api/colors.php`
4. `api/health.php` → `/Apps/Printing/api/health.php`
5. `api/index.php` → `/Apps/Printing/api/index.php` (optional router)

## Quick Deploy

### Option 1: Manual FTP Upload
1. Connect to your FTP server
2. Navigate to `/Apps/Printing/api/`
3. Upload all files from the `api/` directory

### Option 2: Automatic Deployment
If you've set up GitHub Secrets, the workflow will automatically deploy these files when you push changes.

### Option 3: Create Files Directly on Server

If you prefer to create the files directly on the server, here's the content for `users.php`:

```php
<?php
// Users API endpoint - Place this at /Apps/Printing/api/users.php
$backendDir = dirname(__DIR__) . '/backend';
require_once $backendDir . '/cors.php';
require_once $backendDir . '/config.php';
require_once $backendDir . '/api/users.php';
```

Repeat for `orders.php`, `colors.php`, and `health.php` (changing the last require_once line).

## Verify Deployment

After deploying, test:

```bash
# Should return JSON with CORS headers
curl "https://noyanov.com/Apps/Printing/api/users.php" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -v | grep -i "access-control"

# Should return 204 with CORS headers
curl -X OPTIONS "https://noyanov.com/Apps/Printing/api/users.php" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -H "Access-Control-Request-Method: GET" \
  -v | grep -i "access-control"
```

You should see CORS headers in the response.

## How It Works

1. Frontend calls: `https://noyanov.com/Apps/Printing/api/users.php`
2. Nginx serves: `/Apps/Printing/api/users.php`
3. PHP file executes:
   - Includes `cors.php` (sets CORS headers)
   - Includes `config.php` (database config)
   - Includes `backend/api/users.php` (actual API logic)
4. Response includes CORS headers and JSON data

## Important Notes

- Make sure `backend/cors.php` exists on the server
- Make sure `backend/config.php` exists on the server with your database credentials
- Make sure `backend/api/users.php` exists on the server
- All files must be in the correct directory structure

