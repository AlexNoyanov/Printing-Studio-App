# Backend API Deployment Guide

This guide explains how to deploy the Backend API to `noyanov.com` using the automated deployment script.

## Prerequisites

1. **FTP Access**: You need FTP credentials for `noyanov.com`
2. **Required Tools**:
   - `lftp` - FTP client (will be installed automatically if missing)
   - `jq` - JSON processor (will be installed automatically if missing)
   - `php` - For syntax validation

## Quick Start

### 1. Setup Configuration

Copy the example configuration file and fill in your credentials:

```bash
cp deploy-config.example.json deploy-config.json
```

Edit `deploy-config.json` and update the FTP credentials:

```json
{
  "ftp": {
    "server": "91.236.136.126",
    "username": "alex_com",
    "password": "your-actual-password",
    "remoteDir": "/Apps/Printing/api"
  }
}
```

**⚠️ Important**: The `deploy-config.json` file is gitignored and will NOT be committed to the repository.

### 2. Make Script Executable

```bash
chmod +x deploy-backend-api.sh
```

### 3. Deploy

```bash
./deploy-backend-api.sh
```

## Deployment Options

### Dry Run (Test Without Uploading)

Test the deployment without actually uploading files:

```bash
./deploy-backend-api.sh --dry-run
```

This will:
- Validate configuration
- Check PHP syntax
- Show what files would be uploaded
- **NOT** upload any files

### Full Deployment

Deploy all API files to the server:

```bash
./deploy-backend-api.sh
```

## What Gets Deployed

The script deploys all files from `backend/api/` to `/Apps/Printing/api/` on the server.

### Included Files
- All PHP files in `backend/api/`
- `.htaccess` files
- Other necessary configuration files

### Excluded Files (Not Deployed)
- `.git*` - Git files and directories
- `node_modules/` - Node.js dependencies
- `test_*.php` - Test files
- `config.php` - Database configuration (should exist on server)
- `config.example.php` - Example configuration
- `cors-test.php` - Test files
- `*.log` - Log files

## Deployment Process

The script performs the following steps:

1. **Configuration Validation**
   - Checks if `deploy-config.json` exists
   - Validates FTP credentials
   - Verifies required tools are installed

2. **PHP Syntax Validation**
   - Validates all PHP files before deployment
   - Stops deployment if syntax errors are found

3. **File Upload**
   - Uses `lftp` to upload files via FTP
   - Uses mirror mode to sync files
   - Deletes remote files that don't exist locally (cleanup)

4. **Verification**
   - Confirms successful deployment
   - Provides URL to access the API

## Configuration File Structure

```json
{
  "ftp": {
    "server": "91.236.136.126",
    "username": "alex_com",
    "password": "your-password",
    "remoteDir": "/Apps/Printing/api"
  },
  "localPaths": {
    "apiDir": "backend/api",
    "backendDir": "backend"
  },
  "exclude": [
    "**/.git*",
    "**/node_modules/**",
    "**/test_*.php",
    "**/config.php"
  ]
}
```

### Configuration Fields

- **ftp.server**: FTP server IP or hostname
- **ftp.username**: FTP username
- **ftp.password**: FTP password
- **ftp.remoteDir**: Remote directory path on server
- **localPaths.apiDir**: Local API directory to deploy
- **exclude**: Array of file patterns to exclude from deployment

## Troubleshooting

### "Configuration file not found"

Make sure you've copied `deploy-config.example.json` to `deploy-config.json`:

```bash
cp deploy-config.example.json deploy-config.json
```

### "Invalid configuration"

Check that you've updated the placeholder values in `deploy-config.json`:
- Replace `your-ftp-username` with actual username
- Replace `your-ftp-password` with actual password
- Replace `your-ftp-server.com` with actual server

### "PHP syntax errors found"

Fix any PHP syntax errors before deploying. The script will show which files have errors.

### "lftp not found"

The script will attempt to install `lftp` automatically. If it fails, install manually:

**macOS:**
```bash
brew install lftp
```

**Linux:**
```bash
sudo apt-get update && sudo apt-get install -y lftp
```

### "jq not found"

The script will attempt to install `jq` automatically. If it fails, install manually:

**macOS:**
```bash
brew install jq
```

**Linux:**
```bash
sudo apt-get update && sudo apt-get install -y jq
```

### Connection Timeout

If you experience connection timeouts:
1. Check your internet connection
2. Verify the FTP server is accessible
3. Check if firewall is blocking FTP connections
4. Try using passive mode (already enabled in script)

### Permission Denied

If you get permission errors:
1. Verify FTP credentials are correct
2. Check that the remote directory path is correct
3. Ensure the FTP user has write permissions to the target directory

## Security Notes

1. **Never commit `deploy-config.json`** - It contains sensitive credentials
2. **Use strong passwords** for FTP access
3. **Restrict file permissions** on the server
4. **Keep credentials secure** - Don't share the config file

## Server Requirements

The server should have:
- PHP 7.4 or higher
- MySQL/MariaDB database
- FTP server running
- Proper file permissions for the API directory

## Post-Deployment

After deployment, verify the API is working:

1. **Health Check:**
   ```bash
   curl https://noyanov.com/Apps/Printing/api/health.php
   ```

2. **Test Endpoint:**
   ```bash
   curl https://noyanov.com/Apps/Printing/api/shop.php
   ```

## Automated Deployment (GitHub Actions)

For automated deployment on git push, see `.github/workflows/deploy-backend.yml`.

The GitHub Actions workflow uses repository secrets:
- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`

Configure these in: Repository Settings → Secrets and variables → Actions

## Manual Deployment Alternative

If the script doesn't work, you can manually upload files using any FTP client:

1. Connect to `91.236.136.126` with credentials `alex_com` / `kT4oD0gV6n`
2. Navigate to `/Apps/Printing/api/`
3. Upload all files from `backend/api/` directory
4. Exclude the files listed in the "Excluded Files" section above

## Support

If you encounter issues:
1. Check the error messages - they usually indicate the problem
2. Run with `--dry-run` to test without uploading
3. Verify FTP credentials and server accessibility
4. Check server logs for PHP errors
