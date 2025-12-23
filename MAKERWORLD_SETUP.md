# MakerWorld Model Fetcher Setup

This service uses Puppeteer (headless Chrome) to fetch MakerWorld model data with full JavaScript rendering.

## Installation

1. **Install Puppeteer:**
   ```bash
   npm install puppeteer
   ```

   Note: Puppeteer will automatically download Chromium (~300MB) on first install.

## Running the Service

### Development Mode:
```bash
npm run makerworld-fetcher:dev
```

### Production Mode:
```bash
npm run makerworld-fetcher
```

The service will run on port 3002 by default.

## Environment Variables

You can configure the service using environment variables:

- `PORT` - Port to run the service on (default: 3002)
- `MAKERWORLD_FETCHER_URL` - Backend PHP will use this URL (default: http://localhost:3002/fetch)

## API Endpoints

### Health Check
```bash
GET http://localhost:3002/health
```

Returns:
```json
{
  "status": "ok",
  "puppeteer": true,
  "browser": true
}
```

### Fetch Model Data
```bash
POST http://localhost:3002/fetch
Content-Type: application/json

{
  "url": "https://makerworld.com/en/models/123456-model-name"
}
```

Returns:
```json
{
  "title": "Model Title",
  "description": "Model description",
  "image": "https://...",
  "author": "Author Name",
  "likes": 100,
  "downloads": 500,
  "views": 1000,
  "url": "https://makerworld.com/...",
  "fetchedAt": "2024-01-01T00:00:00.000Z"
}
```

## Backend Integration

The PHP backend (`backend/api/shop.php`) will automatically use this service if it's running. If the service is not available, it will fall back to regular HTML scraping.

To configure the service URL in PHP, set the environment variable:
```php
// In backend/config.php or .env
define('MAKERWORLD_FETCHER_URL', 'http://localhost:3002/fetch');
```

## Deployment

### Option 1: Run as Background Service

Using PM2 (recommended for production):
```bash
npm install -g pm2
pm2 start backend/makerworld-fetcher.js --name makerworld-fetcher
pm2 save
pm2 startup
```

### Option 2: Run as Systemd Service

Create `/etc/systemd/system/makerworld-fetcher.service`:
```ini
[Unit]
Description=MakerWorld Model Fetcher
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/Printing-App
ExecStart=/usr/bin/node backend/makerworld-fetcher.js
Restart=always

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl enable makerworld-fetcher
sudo systemctl start makerworld-fetcher
```

### Option 3: Run on Different Server

If running on a different server, update the PHP config:
```php
define('MAKERWORLD_FETCHER_URL', 'http://your-server:3002/fetch');
```

## Troubleshooting

### Puppeteer Installation Issues

If Puppeteer fails to install or launch:

1. **On Linux servers**, you may need additional dependencies:
   ```bash
   sudo apt-get update
   sudo apt-get install -y \
     ca-certificates \
     fonts-liberation \
     libappindicator3-1 \
     libasound2 \
     libatk-bridge2.0-0 \
     libatk1.0-0 \
     libc6 \
     libcairo2 \
     libcups2 \
     libdbus-1-3 \
     libexpat1 \
     libfontconfig1 \
     libgbm1 \
     libgcc1 \
     libglib2.0-0 \
     libgtk-3-0 \
     libnspr4 \
     libnss3 \
     libpango-1.0-0 \
     libpangocairo-1.0-0 \
     libstdc++6 \
     libx11-6 \
     libx11-xcb1 \
     libxcb1 \
     libxcomposite1 \
     libxcursor1 \
     libxdamage1 \
     libxext6 \
     libxfixes3 \
     libxi6 \
     libxrandr2 \
     libxrender1 \
     libxss1 \
     libxtst6 \
     lsb-release \
     wget \
     xdg-utils
   ```

2. **On macOS**, usually works out of the box after `npm install`

3. **Memory issues**: If the service crashes, increase Node.js memory:
   ```bash
   NODE_OPTIONS="--max-old-space-size=2048" npm run makerworld-fetcher
   ```

### Service Not Available

If the service is not running, the PHP backend will automatically fall back to regular HTML scraping (without JavaScript rendering). This is less reliable but still functional.

### Performance

- Each request takes 2-5 seconds (page load + rendering)
- Browser instance is reused across requests for better performance
- Consider implementing caching to avoid repeated fetches

## MakerWorld API (If Available)

If MakerWorld provides an official API in the future, update the `fetchMakerWorldWithBrowser()` function to use the API first, then fall back to browser scraping.

Check MakerWorld's documentation at:
- https://makerworld.com
- https://github.com/bambulab (if they have open source components)

