#!/bin/bash
# Manual deployment script for backend API
# Usage: ./deploy-backend.sh

set -e

echo "🚀 Deploying Backend API to noyanov.com..."

# Configuration (you can also set these as environment variables)
FTP_SERVER="${FTP_SERVER:-your-ftp-server.com}"
FTP_USER="${FTP_USER:-your-ftp-user}"
FTP_PASS="${FTP_PASS:-your-ftp-password}"
REMOTE_DIR="/Apps/Printing/backend/"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Validate PHP files
echo "📝 Validating PHP files..."
find backend -name "*.php" -exec php -l {} \; || {
    echo -e "${RED}❌ PHP syntax errors found!${NC}"
    exit 1
}

echo -e "${GREEN}✅ All PHP files are valid${NC}"

# Check if lftp is installed
if ! command -v lftp &> /dev/null; then
    echo -e "${YELLOW}⚠️  lftp not found. Installing...${NC}"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install lftp
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install -y lftp
    fi
fi

# Deploy using lftp
echo "📤 Uploading files..."
lftp -c "
set ftp:ssl-allow no
set ftp:passive-mode yes
open -u $FTP_USER,$FTP_PASS $FTP_SERVER
cd $REMOTE_DIR
mirror -R --delete --verbose --exclude-glob='*.git*' --exclude-glob='node_modules/*' --exclude-glob='test_*.php' --exclude-glob='config.php' backend/ .
bye
"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Deployment successful!${NC}"
    echo "🌐 Backend API is available at: https://noyanov.com/Apps/Printing/api/"
else
    echo -e "${RED}❌ Deployment failed!${NC}"
    exit 1
fi

