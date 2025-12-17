# Quick Deploy Guide

## One-Command Deployment

The deployment configuration is already set up. To deploy the Backend API:

```bash
./deploy-backend-api.sh
```

## What This Does

1. ✅ Validates all PHP files for syntax errors
2. ✅ Uploads all API files from `backend/api/` to `/Apps/Printing/api/` on noyanov.com
3. ✅ Excludes sensitive files (config.php, test files, etc.)
4. ✅ Provides deployment status and API URL

## Test First (Dry Run)

Before deploying, test what will be uploaded:

```bash
./deploy-backend-api.sh --dry-run
```

## Configuration

The deployment configuration is stored in `deploy-config.json` (gitignored for security).

**Current Settings:**
- Server: `91.236.136.126`
- Remote Directory: `/Apps/Printing/api`
- Local Directory: `backend/api`

## Troubleshooting

### Script Not Executable
```bash
chmod +x deploy-backend-api.sh
```

### Missing Tools
The script will automatically install `lftp` and `jq` if missing.

### PHP Syntax Errors
Fix any PHP errors before deploying. The script will show which files have issues.

## Files Deployed

All files from `backend/api/` including:
- `shop.php` (new Shop API endpoint)
- `orders.php`
- `users.php`
- `colors.php`
- `materials.php`
- `index.php` (API router)
- All other API endpoints
- `.htaccess` files

## Security

✅ `deploy-config.json` is gitignored and will NOT be committed
✅ Sensitive files (config.php, test files) are excluded from deployment
✅ FTP credentials are stored locally only

## After Deployment

Verify the API is working:

```bash
curl https://noyanov.com/Apps/Printing/api/health.php
```

For more details, see [DEPLOYMENT_BACKEND_API.md](./DEPLOYMENT_BACKEND_API.md)
