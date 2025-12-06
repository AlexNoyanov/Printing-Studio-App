# Nginx CORS Fix - Your server uses Nginx, not Apache!

## Problem
Your server is running **Nginx**, not Apache. This means `.htaccess` files don't work! That's why CORS headers aren't being set.

## Solution

You need to add Nginx configuration to handle CORS. Add this to your Nginx server configuration:

### Option 1: Add to Nginx Server Block (Recommended)

Edit your Nginx configuration file (usually in `/etc/nginx/sites-available/noyanov.com` or similar):

```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name noyanov.com;
    
    root /path/to/your/webroot;
    index index.html index.php;
    
    # CORS and API routing for Printing App
    location /Apps/Printing/api {
        # Set CORS headers
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-Requested-With, Accept, Origin' always;
        add_header 'Access-Control-Max-Age' '3600' always;
        
        # Handle OPTIONS preflight requests
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Allow-Origin' '*' always;
            add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
            add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-Requested-With, Accept, Origin' always;
            add_header 'Access-Control-Max-Age' '3600' always;
            add_header 'Content-Type' 'text/plain charset=UTF-8' always;
            add_header 'Content-Length' '0' always;
            return 204;
        }
        
        # Route to PHP backend
        try_files $uri $uri/ /Apps/Printing/backend/index.php?$query_string;
        
        # PHP configuration
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock; # Adjust PHP version
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Frontend routing
    location /Apps/Printing {
        try_files $uri $uri/ /Apps/Printing/index.html;
    }
}
```

### Option 2: Simpler Rewrite Version

If the above doesn't work, try this simpler version:

```nginx
location /Apps/Printing/api {
    # CORS headers
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-Requested-With' always;
    
    # Handle OPTIONS
    if ($request_method = 'OPTIONS') {
        return 204;
    }
    
    # Route to backend
    rewrite ^/Apps/Printing/api/(.*)$ /Apps/Printing/backend/index.php?$1 last;
    
    # PHP processing
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## Steps to Apply

1. **Find your Nginx config file:**
   ```bash
   # Usually one of these:
   /etc/nginx/sites-available/noyanov.com
   /etc/nginx/conf.d/noyanov.com.conf
   /etc/nginx/nginx.conf
   ```

2. **Edit the file** and add the location block above

3. **Test the configuration:**
   ```bash
   sudo nginx -t
   ```

4. **Reload Nginx:**
   ```bash
   sudo systemctl reload nginx
   # or
   sudo service nginx reload
   ```

5. **Test CORS:**
   ```bash
   curl -X OPTIONS "https://noyanov.com/Apps/Printing/api/users" \
     -H "Origin: https://printing-studio-app-4e0e6.web.app" \
     -H "Access-Control-Request-Method: GET" \
     -v
   ```

## If You Don't Have Access to Nginx Config

If you're on shared hosting and can't edit Nginx config, you have two options:

### Option A: Use PHP to Set Headers (Already Done)
The PHP files already set CORS headers, but they need to be executed. Make sure:
- `backend/cors.php` is uploaded
- `backend/index.php` includes it
- All API files include cors.php

### Option B: Contact Your Hosting Provider
Ask them to add CORS headers for `/Apps/Printing/api/*` paths.

## Quick Test

After adding the Nginx config, test:

```bash
# Should return 204 with CORS headers
curl -X OPTIONS "https://noyanov.com/Apps/Printing/api/users" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -v

# Should return JSON with CORS headers
curl "https://noyanov.com/Apps/Printing/api/users" \
  -H "Origin: https://printing-studio-app-4e0e6.web.app" \
  -v
```

## Important Notes

- **Nginx doesn't use .htaccess** - those files are ignored
- CORS headers must be set in Nginx config OR in PHP (we're doing both)
- The `always` keyword ensures headers are sent even on error responses
- Make sure PHP-FPM is configured correctly for your PHP version

