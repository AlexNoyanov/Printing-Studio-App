#!/bin/bash
# Automated deployment script for Backend API to noyanov.com
# Usage: ./deploy-backend-api.sh [--dry-run]
#
# Setup:
# 1. Copy deploy-config.example.json to deploy-config.json
# 2. Fill in your FTP credentials in deploy-config.json
# 3. Make script executable: chmod +x deploy-backend-api.sh
# 4. Run: ./deploy-backend-api.sh

set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check for dry-run flag
DRY_RUN=false
if [[ "$1" == "--dry-run" ]]; then
    DRY_RUN=true
    echo -e "${YELLOW}🔍 DRY RUN MODE - No files will be uploaded${NC}\n"
fi

# Configuration file
CONFIG_FILE="deploy-config.json"

# Check if config file exists
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}❌ Configuration file not found: $CONFIG_FILE${NC}"
    echo -e "${YELLOW}📝 Please copy deploy-config.example.json to deploy-config.json and fill in your credentials${NC}"
    exit 1
fi

# Load configuration
echo -e "${BLUE}📋 Loading configuration...${NC}"
FTP_SERVER=$(jq -r '.ftp.server' "$CONFIG_FILE")
FTP_USER=$(jq -r '.ftp.username' "$CONFIG_FILE")
FTP_PASS=$(jq -r '.ftp.password' "$CONFIG_FILE")
REMOTE_DIR=$(jq -r '.ftp.remoteDir' "$CONFIG_FILE")
BACKEND_DIR=$(jq -r '.localPaths.backendDir' "$CONFIG_FILE")

# Validate configuration
if [ "$FTP_SERVER" == "null" ] || [ "$FTP_USER" == "null" ] || [ "$FTP_PASS" == "null" ]; then
    echo -e "${RED}❌ Invalid configuration. Please check deploy-config.json${NC}"
    exit 1
fi

if [ "$FTP_SERVER" == "your-ftp-server.com" ] || [ "$FTP_USER" == "your-ftp-username" ] || [ "$FTP_PASS" == "your-ftp-password" ]; then
    echo -e "${RED}❌ Please update deploy-config.json with your actual FTP credentials${NC}"
    exit 1
fi

# Check if jq is installed
if ! command -v jq &> /dev/null; then
    echo -e "${YELLOW}⚠️  jq not found. Installing...${NC}"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install jq
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install -y jq
    else
        echo -e "${RED}❌ Please install jq manually: https://stedolan.github.io/jq/download/${NC}"
        exit 1
    fi
fi

# Check if backend directory exists
if [ ! -d "$BACKEND_DIR" ]; then
    echo -e "${RED}❌ Backend directory not found: $BACKEND_DIR${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Configuration loaded${NC}"
echo -e "${BLUE}   Server: $FTP_SERVER${NC}"
echo -e "${BLUE}   User: $FTP_USER${NC}"
echo -e "${BLUE}   Remote Dir: $REMOTE_DIR${NC}"
echo -e "${BLUE}   Local Dir: $BACKEND_DIR${NC}\n"

# Validate PHP files (entire backend, like original script)
echo -e "${BLUE}📝 Validating PHP files...${NC}"
PHP_ERRORS=0
while IFS= read -r -d '' file; do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}❌ Syntax error in: $file${NC}"
        PHP_ERRORS=$((PHP_ERRORS + 1))
    fi
done < <(find "$BACKEND_DIR" -name "*.php" -type f -print0)

if [ $PHP_ERRORS -gt 0 ]; then
    echo -e "${RED}❌ Found $PHP_ERRORS PHP syntax error(s)!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ All PHP files are valid${NC}\n"

# Check if lftp is installed
if ! command -v lftp &> /dev/null; then
    echo -e "${YELLOW}⚠️  lftp not found. Installing...${NC}"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install lftp
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install -y lftp
    else
        echo -e "${RED}❌ Please install lftp manually${NC}"
        exit 1
    fi
fi

# Build exclude patterns for lftp
EXCLUDE_PATTERNS=""
while IFS= read -r pattern; do
    if [ -n "$pattern" ]; then
        # Convert glob pattern to lftp exclude format
        EXCLUDE_PATTERNS="$EXCLUDE_PATTERNS --exclude-glob='$pattern'"
    fi
done < <(jq -r '.exclude[]' "$CONFIG_FILE")

# Deploy using lftp
if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}🔍 DRY RUN: Would upload files from $BACKEND_DIR to $REMOTE_DIR${NC}"
    echo -e "${YELLOW}   Exclude patterns:${NC}"
    jq -r '.exclude[]' "$CONFIG_FILE" | while read -r pattern; do
        echo -e "${YELLOW}     - $pattern${NC}"
    done
    echo -e "\n${BLUE}📋 Files that would be uploaded (first 20 PHP files):${NC}"
    find "$BACKEND_DIR" -type f -name "*.php" | head -20
    echo -e "${BLUE}   ... (showing first 20 files)${NC}"
else
    echo -e "${BLUE}📤 Uploading files to server...${NC}"
    
    # Create lftp script
    LFTP_SCRIPT=$(mktemp)
    cat > "$LFTP_SCRIPT" <<EOF
set ftp:ssl-allow no
set ftp:passive-mode yes
set ftp:list-options -a
set mirror:set-permissions false
set mirror:use-pget-n 5
open -u $FTP_USER,$FTP_PASS $FTP_SERVER
cd $REMOTE_DIR
mirror -R --delete --verbose --exclude-glob='*.git*' --exclude-glob='node_modules/*' --exclude-glob='test_*.php' --exclude-glob='config.php' --exclude-glob='config.example.php' --exclude-glob='cors-test.php' --exclude-glob='*.log' $BACKEND_DIR .
bye
EOF

    # Execute lftp
    if lftp -f "$LFTP_SCRIPT"; then
        echo -e "\n${GREEN}✅ Deployment successful!${NC}"
        echo -e "${GREEN}🌐 Backend API is available at: https://noyanov.com$REMOTE_DIR${NC}"
        
        # Clean up
        rm -f "$LFTP_SCRIPT"
    else
        echo -e "\n${RED}❌ Deployment failed!${NC}"
        rm -f "$LFTP_SCRIPT"
        exit 1
    fi
fi

echo -e "\n${GREEN}✨ Done!${NC}"
