# PHP Backend Deployment Guide

## Overview

This app uses a **PHP backend** and is deployed at: **noyanov.com/Apps/Printing/**

## Prerequisites

1. PHP 7.4+ with PDO MySQL extension
2. MySQL database access (already configured)
3. Apache web server with mod_rewrite enabled
4. Database tables created (see below)

## Step-by-Step Deployment

### 1. Create Database Tables

Connect to your MySQL database and run the schema:

```bash
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD printing < database/schema.sql
```

Or via phpMyAdmin:
1. Login to phpMyAdmin
2. Select the `printing` database
3. Go to SQL tab
4. Copy and paste contents of `database/schema.sql`
5. Click "Go"

**Verify tables were created:**
```bash
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD printing -e "SHOW TABLES;"
```

Should show: `users`, `colors`, `orders`, `order_colors`

### 2. Upload Files to Server

Upload the following structure to your web server:

```
/Apps/Printing/
├── backend/
│   ├── api/
│   │   ├── users.php
│   │   ├── colors.php
│   │   ├── orders.php
│   │   └── health.php
│   ├── config.php
│   ├── index.php
│   └── .htaccess
├── dist/                    (frontend build files)
│   ├── index.html
│   ├── assets/
│   └── ...
├── .htaccess                (root .htaccess)
└── index.html              (or point to dist/index.html)
```

### 3. Build Frontend

On your local machine:

```bash
npm install
npm run build
```

This creates the `dist` folder with all static files.

### 4. Upload Files

**Option A: Using FTP/SFTP**
- Upload `backend/` folder to `/Apps/Printing/backend/`
- Upload contents of `dist/` folder to `/Apps/Printing/`
- Upload root `.htaccess` to `/Apps/Printing/`

**Option B: Using Git**
```bash
# On server
cd /path/to/webroot/Apps/Printing
git clone your-repo .
npm run build
```

### 5. Set File Permissions

```bash
# Make sure PHP files are readable
chmod 644 backend/*.php
chmod 644 backend/api/*.php
chmod 644 .htaccess

# Make directories executable
chmod 755 backend
chmod 755 backend/api
```

### 6. Configure Apache

Ensure your Apache virtual host or `.htaccess` allows:
- `mod_rewrite` enabled
- `AllowOverride All` for the directory

Example Apache virtual host:
```apache
<VirtualHost *:80>
    ServerName noyanov.com
    DocumentRoot /path/to/webroot
    
    <Directory /path/to/webroot/Apps/Printing>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 7. Test Deployment

1. **Test Health Endpoint:**
   ```bash
   curl https://noyanov.com/Apps/Printing/api/health
   ```
   Should return: `{"status":"ok","database":"connected"}`

2. **Test Frontend:**
   Visit: `https://noyanov.com/Apps/Printing/`
   Should load the login page.

3. **Test API:**
   ```bash
   curl https://noyanov.com/Apps/Printing/api/users
   ```
   Should return an array of users (empty if none exist).

## File Structure on Server

```
/Apps/Printing/
├── .htaccess                 # Main routing rules
├── index.html                # Frontend entry point (from dist/)
├── assets/                   # Frontend assets (from dist/)
│   ├── index-*.js
│   └── index-*.css
├── backend/
│   ├── .htaccess            # Backend routing
│   ├── config.php           # Database config
│   ├── index.php            # API router
│   └── api/
│       ├── users.php        # Users endpoint
│       ├── colors.php       # Colors endpoint
│       ├── orders.php       # Orders endpoint
│       └── health.php       # Health check
└── database/
    └── schema.sql           # Database schema (for reference)
```

## API Endpoints

All endpoints are available at: `https://noyanov.com/Apps/Printing/api/`

### Users
- `GET /api/users` - Get all users
- `GET /api/users/:id` - Get user by ID
- `POST /api/users` - Create user
- `PUT /api/users/:id` - Update user
- `DELETE /api/users/:id` - Delete user

### Colors
- `GET /api/colors` - Get all colors (optional `?userId=xxx`)
- `GET /api/colors/:id` - Get color by ID
- `POST /api/colors` - Create color
- `PUT /api/colors/:id` - Update color
- `DELETE /api/colors/:id` - Delete color

### Orders
- `GET /api/orders` - Get all orders (optional `?userId=xxx&status=xxx`)
- `GET /api/orders/:id` - Get order by ID
- `POST /api/orders` - Create order
- `PUT /api/orders/:id` - Update order
- `DELETE /api/orders/:id` - Delete order

### Health Check
- `GET /api/health` - Check server and database status

## Troubleshooting

### 500 Internal Server Error
- Check PHP error logs: `/var/log/apache2/error.log` or cPanel error logs
- Verify database credentials in `backend/config.php`
- Check file permissions
- Ensure PDO MySQL extension is enabled: `php -m | grep pdo_mysql`

### API Returns 404
- Verify `.htaccess` files are uploaded
- Check `mod_rewrite` is enabled: `apache2ctl -M | grep rewrite`
- Verify `AllowOverride All` is set in Apache config
- Check file paths are correct

### Database Connection Fails
- Verify MySQL server is accessible: `mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD`
- Check firewall allows connections
- Verify database name is `printing`
- Test connection: `php -r "require 'backend/config.php'; getDBConnection();"`

### CORS Errors
- CORS headers are already set in `backend/config.php`
- If issues persist, check web server configuration
- Verify frontend is making requests to correct URL

### Frontend Shows Blank Page
- Check browser console for errors
- Verify `index.html` is in correct location
- Check that assets are loading (Network tab in DevTools)
- Verify base path in `vite.config.js` is `/Apps/Printing/`

## Updating the App

1. **Update Frontend:**
   ```bash
   npm run build
   # Upload new dist/ contents
   ```

2. **Update Backend:**
   ```bash
   # Upload new backend/ files
   # No restart needed for PHP
   ```

3. **Clear Browser Cache:**
   - Users may need to hard refresh (Ctrl+F5 / Cmd+Shift+R)

## Security Notes

⚠️ **Important Security Considerations:**

1. **Passwords**: Currently stored in plain text. Consider:
   - Implementing password hashing (password_hash/password_verify)
   - Using prepared statements (already implemented)

2. **HTTPS**: Ensure SSL certificate is configured

3. **Input Validation**: Add server-side validation for all inputs

4. **Rate Limiting**: Consider adding rate limiting for API endpoints

5. **Error Messages**: Don't expose sensitive information in error messages

## PHP Requirements

- PHP 7.4 or higher
- PDO extension
- PDO MySQL driver
- mod_rewrite (Apache)

Check PHP version:
```bash
php -v
```

Check extensions:
```bash
php -m | grep pdo
php -m | grep pdo_mysql
```

## Quick Test Script

Create `test.php` in backend folder:

```php
<?php
require_once 'config.php';
try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful!\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Users in database: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

Run: `php backend/test.php`

