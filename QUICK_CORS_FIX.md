# Quick CORS Fix for Nginx Server

Your server uses **Nginx**, not Apache, so `.htaccess` files are **ignored**. That's why CORS isn't working.

## Immediate Solution

You have two options:

### Option 1: Contact Your Hosting Provider (Recommended)

Ask them to add this Nginx configuration for `/Apps/Printing/api`:

```nginx
location /Apps/Printing/api {
    # CORS headers
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-Requested-With, Accept, Origin' always;
    add_header 'Access-Control-Max-Age' '3600' always;
    
    # Handle OPTIONS preflight
    if ($request_method = 'OPTIONS') {
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-Requested-With, Accept, Origin' always;
        add_header 'Content-Length' '0' always;
        return 204;
    }
    
    # Route to PHP backend
    try_files $uri $uri/ /Apps/Printing/backend/index.php?$query_string;
    
    # PHP processing
    fastcgi_pass unix:/var/run/php/php-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root/Apps/Printing/backend/index.php;
    include fastcgi_params;
}
```

### Option 2: Use PHP to Set Headers (Already Implemented)

The PHP files already set CORS headers, but they need to be executed. Make sure:

1. **Backend files are deployed correctly:**
   - `/Apps/Printing/backend/index.php` exists
   - `/Apps/Printing/backend/cors.php` exists
   - `/Apps/Printing/backend/api/users.php` exists

2. **Test if PHP is working:**
   - Visit: `https://noyanov.com/Apps/Printing/api/health`
   - Should return: `{"status":"ok"}`
   - If it returns HTML or 404, PHP routing isn't working

3. **Test CORS endpoint:**
   - Visit: `https://noyanov.com/Apps/Printing/api/cors-test.php`
   - Should return JSON with CORS info

## Deployment Steps

1. **Deploy backend files:**
   ```bash
   # Use the deployment script
   ./deploy-backend.sh
   
   # Or manually upload via FTP:
   # Upload all files from backend/ directory to /Apps/Printing/backend/
   ```

2. **Verify deployment:**
   ```bash
   # Test health endpoint
   curl https://noyanov.com/Apps/Printing/api/health
   
   # Test CORS
   curl -X OPTIONS "https://noyanov.com/Apps/Printing/api/users" \
     -H "Origin: https://printing-studio-app-4e0e6.web.app" \
     -v
   ```

3. **Check file structure on server:**
   ```
   /Apps/Printing/
   ├── backend/
   │   ├── index.php          ← Must exist
   │   ├── cors.php           ← Must exist
   │   ├── config.php         ← Create with your DB credentials
   │   ├── api/
   │   │   ├── users.php
   │   │   ├── orders.php
   │   │   ├── colors.php
   │   │   └── health.php
   │   └── .htaccess          ← Won't work on Nginx, but harmless
   ```

## If Still Not Working

1. **Check if requests reach PHP:**
   - Add this to `backend/index.php` at the very top:
   ```php
   <?php
   error_log("API Request: " . $_SERVER['REQUEST_URI'] . " Method: " . $_SERVER['REQUEST_METHOD']);
   ```
   - Check server error logs to see if requests are reaching PHP

2. **Test with a simple PHP file:**
   - Create `test.php` in `/Apps/Printing/backend/`:
   ```php
   <?php
   header('Access-Control-Allow-Origin: *');
   header('Content-Type: application/json');
   echo json_encode(['test' => 'works']);
   ```
   - Access: `https://noyanov.com/Apps/Printing/backend/test.php`
   - Check browser Network tab for CORS headers

3. **Contact hosting support:**
   - Ask them to configure Nginx for `/Apps/Printing/api/*` paths
   - Provide them the Nginx config above

## Automatic Deployment

Set up GitHub Actions for automatic deployment:

1. Go to: https://github.com/AlexNoyanov/Printing-Studio-App/settings/secrets/actions
2. Add secrets:
   - `FTP_SERVER`
   - `FTP_USERNAME`
   - `FTP_PASSWORD`
3. Push changes to `backend/` directory
4. GitHub Actions will automatically deploy

See `DEPLOYMENT_BACKEND.md` for detailed instructions.

