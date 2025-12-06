# Deployment Guide for Printing App

## Overview

This app is configured to be deployed at: **noyanov.com/Apps/Printing/**

## Prerequisites

1. Node.js installed on server
2. MySQL database access (already configured)
3. Web server (Apache/Nginx) configured
4. PM2 or similar process manager (recommended)

## Step-by-Step Deployment

### 1. Database Setup

The database is already configured:
- Host: YOUR_DB_HOST
- Database: printing
- User: YOUR_DB_USER
- Password: YOUR_PASSWORD

Initialize the database schema:
```bash
npm run db:init
```

Or manually:
```bash
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD printing < database/schema.sql
```

### 2. Install Dependencies

```bash
npm install
```

### 3. Build Frontend

```bash
npm run build
```

This creates the `dist` folder with all static files.

### 4. Deploy Frontend Files

Copy the contents of the `dist` folder to your web server at:
```
/Apps/Printing/
```

Make sure the following files are accessible:
- `index.html`
- `assets/` folder (contains JS, CSS, images)

### 5. Start Backend Server

#### Option A: Using PM2 (Recommended)

```bash
# Install PM2 globally
npm install -g pm2

# Start the backend
pm2 start backend/server.js --name printing-app-backend

# Save PM2 configuration
pm2 save

# Setup PM2 to start on boot
pm2 startup
```

#### Option B: Using Node directly

```bash
npm run server
```

#### Option C: Using systemd (Linux)

Create `/etc/systemd/system/printing-app.service`:

```ini
[Unit]
Description=Printing App Backend Server
After=network.target

[Service]
Type=simple
User=your-username
WorkingDirectory=/path/to/Printing-App
ExecStart=/usr/bin/node backend/server.js
Restart=always
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl enable printing-app
sudo systemctl start printing-app
```

### 6. Configure Web Server

#### Apache Configuration

Add to your Apache config or `.htaccess`:

```apache
# Rewrite API requests to backend
RewriteEngine On
RewriteRule ^Apps/Printing/api/(.*)$ http://localhost:3001/Apps/Printing/api/$1 [P,L]

# Serve frontend files
Alias /Apps/Printing /path/to/Printing-App/dist
<Directory "/path/to/Printing-App/dist">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

#### Nginx Configuration

Add to your Nginx config:

```nginx
location /Apps/Printing/api {
    proxy_pass http://localhost:3001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
}

location /Apps/Printing {
    alias /path/to/Printing-App/dist;
    try_files $uri $uri/ /Apps/Printing/index.html;
    index index.html;
}
```

### 7. Verify Deployment

1. **Check Backend Health**:
   ```bash
   curl http://localhost:3001/Apps/Printing/api/health
   ```
   Should return: `{"status":"ok","database":"connected"}`

2. **Check Frontend**:
   Visit: `https://noyanov.com/Apps/Printing/`
   Should load the login page.

3. **Test API**:
   ```bash
   curl https://noyanov.com/Apps/Printing/api/health
   ```
   Should return the same health check response.

## Environment Configuration

The backend uses these default values (can be overridden with `.env` file):
- Port: 3001
- Database: Already configured in `backend/server.js`

## Troubleshooting

### Frontend shows blank page
- Check browser console for errors
- Verify all files in `dist` folder are uploaded
- Check web server configuration for correct path

### API calls fail
- Verify backend server is running: `pm2 list` or `systemctl status printing-app`
- Check backend logs: `pm2 logs printing-app-backend`
- Verify reverse proxy configuration
- Test direct backend: `curl http://localhost:3001/Apps/Printing/api/health`

### Database connection errors
- Verify MySQL server is accessible
- Check firewall rules
- Verify credentials in `backend/server.js`

### CORS errors
- Backend already has CORS enabled for all origins
- If issues persist, check web server proxy configuration

## Updating the App

1. Pull latest changes
2. Run `npm install` (if dependencies changed)
3. Run `npm run build` (rebuild frontend)
4. Copy new `dist` folder contents to server
5. Restart backend: `pm2 restart printing-app-backend`

## Monitoring

### Check Backend Status
```bash
pm2 status
pm2 logs printing-app-backend
```

### Check Database Connection
```bash
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD printing -e "SELECT COUNT(*) FROM users;"
```

## Security Notes

⚠️ **Important**: The current implementation stores passwords in plain text. For production, consider:
- Implementing password hashing (bcrypt)
- Using HTTPS for all connections
- Adding authentication tokens (JWT)
- Implementing rate limiting
- Adding input validation and sanitization

