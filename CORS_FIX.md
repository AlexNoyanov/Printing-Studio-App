# CORS Fix Instructions

## Problem
CORS errors when accessing API from Firebase-hosted frontend (printing-studio-app-4e0e6.web.app)

## Solution Applied

### 1. Created `backend/cors.php`
Centralized CORS configuration file that sets headers before any output.

### 2. Updated All API Files
All API endpoints now include `cors.php` first:
- `backend/index.php`
- `backend/api/users.php`
- `backend/api/colors.php`
- `backend/api/orders.php`
- `backend/api/health.php`

### 3. Updated `.htaccess` Files
- Root `.htaccess`: Added Apache-level CORS headers and proper routing
- `backend/.htaccess`: Added CORS headers at Apache level

## Files to Upload

Upload these files to your server:

1. **NEW FILE**: `backend/cors.php`
2. `backend/index.php` (updated)
3. `backend/api/users.php` (updated)
4. `backend/api/colors.php` (updated)
5. `backend/api/orders.php` (updated)
6. `backend/api/health.php` (updated)
7. `.htaccess` (root, updated)
8. `backend/.htaccess` (updated)

## Testing After Upload

### Test 1: OPTIONS Preflight
```bash
curl -X OPTIONS "https://noyanov.com/Apps/Printing/api/users" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -H "Access-Control-Request-Method: GET" \
  -v
```

**Expected**: Should return 200 with `Access-Control-Allow-Origin: *` header

### Test 2: Actual GET Request
```bash
curl "https://noyanov.com/Apps/Printing/api/users" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -v
```

**Expected**: Should return JSON array with `Access-Control-Allow-Origin: *` header

### Test 3: Health Check
```bash
curl "https://noyanov.com/Apps/Printing/api/health"
```

**Expected**: `{"status":"ok","database":"connected"}`

## If Still Not Working

If CORS errors persist after uploading:

1. **Check Apache mod_headers**: Ensure `mod_headers` is enabled
   ```bash
   apache2ctl -M | grep headers
   ```

2. **Check file permissions**: Ensure PHP files are readable
   ```bash
   chmod 644 backend/*.php
   chmod 644 backend/api/*.php
   ```

3. **Check error logs**: Look for PHP errors
   ```bash
   tail -f /var/log/apache2/error.log
   ```

4. **Test direct access**: Try accessing backend directly
   ```bash
   curl "https://noyanov.com/Apps/Printing/backend/api/users.php"
   ```

5. **Verify routing**: Check if .htaccess is being processed
   - Ensure `AllowOverride All` is set in Apache config
   - Check that `mod_rewrite` is enabled

## Alternative: Add CORS in PHP.ini or Apache Config

If .htaccess isn't working, add CORS headers in Apache virtual host:

```apache
<VirtualHost *:443>
    ServerName noyanov.com
    
    <Directory /path/to/Apps/Printing>
        AllowOverride All
        
        <IfModule mod_headers.c>
            Header always set Access-Control-Allow-Origin "*"
            Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
            Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, Accept, Origin"
        </IfModule>
    </Directory>
</VirtualHost>
```

