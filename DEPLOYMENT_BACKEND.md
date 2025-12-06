# Backend API Deployment Guide

This guide explains how to deploy the backend API to `https://noyanov.com/Apps/Printing/api/`

## Automatic Deployment (GitHub Actions)

### Option 1: FTP Deployment

1. **Set up GitHub Secrets:**
   - Go to your repository: https://github.com/AlexNoyanov/Printing-Studio-App
   - Navigate to Settings → Secrets and variables → Actions
   - Add the following secrets:
     - `FTP_SERVER`: Your FTP server address (e.g., `ftp.noyanov.com`)
     - `FTP_USERNAME`: Your FTP username
     - `FTP_PASSWORD`: Your FTP password

2. **Enable the workflow:**
   - The workflow file `.github/workflows/deploy-backend.yml` is already created
   - It will automatically deploy when you push changes to the `backend/` directory

3. **Manual trigger:**
   - Go to Actions tab in GitHub
   - Select "Deploy Backend API" workflow
   - Click "Run workflow"

### Option 2: SFTP Deployment

If your server supports SFTP:

1. **Set up GitHub Secrets:**
   - `SFTP_SERVER`: Your SFTP server address
   - `SFTP_USERNAME`: Your SFTP username
   - `SFTP_SSH_KEY`: Your private SSH key (generate with `ssh-keygen`)

2. **Enable the workflow:**
   - The workflow file `.github/workflows/deploy-backend-sftp.yml` is already created

## Manual Deployment

### Using the Deployment Script

1. **Make the script executable:**
   ```bash
   chmod +x deploy-backend.sh
   ```

2. **Set environment variables:**
   ```bash
   export FTP_SERVER="ftp.noyanov.com"
   export FTP_USER="your-ftp-user"
   export FTP_PASS="your-ftp-password"
   ```

3. **Run the script:**
   ```bash
   ./deploy-backend.sh
   ```

### Using FTP Client

1. **Connect to your FTP server:**
   - Server: Your FTP server address
   - Username: Your FTP username
   - Password: Your FTP password

2. **Upload files:**
   - Navigate to `/Apps/Printing/backend/` on the server
   - Upload all files from the `backend/` directory EXCEPT:
     - `config.php` (contains sensitive credentials)
     - `test_*.php` (test files)
     - `.git*` (git files)

3. **Verify file structure:**
   ```
   /Apps/Printing/backend/
   ├── api/
   │   ├── users.php
   │   ├── orders.php
   │   ├── colors.php
   │   └── health.php
   ├── cors.php
   ├── index.php
   ├── config.php (create this on server with your credentials)
   └── .htaccess
   ```

## Server Configuration

### For Nginx (Your Current Server)

Since your server uses Nginx, you need to configure it properly. Add this to your Nginx configuration:

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

Then reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### For Apache

If using Apache, the `.htaccess` file should handle routing. Ensure `mod_rewrite` and `mod_headers` are enabled.

## Post-Deployment Checklist

1. **Test the health endpoint:**
   ```bash
   curl https://noyanov.com/Apps/Printing/api/health
   ```
   Should return: `{"status":"ok"}`

2. **Test CORS headers:**
   ```bash
   curl -X OPTIONS "https://noyanov.com/Apps/Printing/api/users" \
     -H "Origin: https://printing-studio-app-4e0e6.web.app" \
     -H "Access-Control-Request-Method: GET" \
     -v
   ```
   Should return `204` with CORS headers

3. **Test API endpoint:**
   ```bash
   curl "https://noyanov.com/Apps/Printing/api/users" \
     -H "Origin: https://printing-studio-app-4e0e6.web.app" \
     -v
   ```
   Should return JSON with CORS headers

## Troubleshooting CORS Issues

If you're still getting CORS errors:

1. **Check if PHP files are being executed:**
   - Visit `https://noyanov.com/Apps/Printing/api/health` in browser
   - Should return JSON, not HTML

2. **Check server logs:**
   - Nginx error log: `/var/log/nginx/error.log`
   - PHP error log: Check your PHP configuration

3. **Verify file permissions:**
   ```bash
   chmod 644 backend/*.php
   chmod 755 backend/api/
   ```

4. **Test with a simple PHP file:**
   Create `test-cors.php`:
   ```php
   <?php
   header('Access-Control-Allow-Origin: *');
   header('Content-Type: application/json');
   echo json_encode(['test' => 'CORS works']);
   ```
   Access it and check if headers are present

## Important Notes

- **Never commit `backend/config.php`** - it contains database credentials
- Always test after deployment
- Keep backups of your `config.php` file
- Monitor server logs for errors

