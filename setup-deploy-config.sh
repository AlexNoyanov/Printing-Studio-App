#!/bin/bash
# Setup script for deployment configuration
# This script helps you create deploy-config.json from the example file

set -e

CONFIG_FILE="deploy-config.json"
EXAMPLE_FILE="deploy-config.example.json"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔧 Setting up deployment configuration...${NC}\n"

# Check if config already exists
if [ -f "$CONFIG_FILE" ]; then
    echo -e "${YELLOW}⚠️  $CONFIG_FILE already exists${NC}"
    read -p "Do you want to overwrite it? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Setup cancelled."
        exit 0
    fi
fi

# Copy example file
if [ ! -f "$EXAMPLE_FILE" ]; then
    echo -e "${RED}❌ $EXAMPLE_FILE not found!${NC}"
    exit 1
fi

cp "$EXAMPLE_FILE" "$CONFIG_FILE"
echo -e "${GREEN}✅ Created $CONFIG_FILE from example${NC}\n"

# Check if jq is installed
if ! command -v jq &> /dev/null; then
    echo -e "${YELLOW}⚠️  jq not found. Please install it to use this script interactively.${NC}"
    echo -e "${YELLOW}   Or manually edit $CONFIG_FILE with your credentials${NC}"
    exit 0
fi

# Interactive setup
echo -e "${BLUE}📝 Please provide your FTP credentials:${NC}\n"

read -p "FTP Server [91.236.136.126]: " FTP_SERVER
FTP_SERVER=${FTP_SERVER:-91.236.136.126}

read -p "FTP Username [alex_com]: " FTP_USER
FTP_USER=${FTP_USER:-alex_com}

read -sp "FTP Password: " FTP_PASS
echo

read -p "Remote Directory [/Apps/Printing/api]: " REMOTE_DIR
REMOTE_DIR=${REMOTE_DIR:-/Apps/Printing/api}

# Update config file
jq --arg server "$FTP_SERVER" \
   --arg user "$FTP_USER" \
   --arg pass "$FTP_PASS" \
   --arg dir "$REMOTE_DIR" \
   '.ftp.server = $server | .ftp.username = $user | .ftp.password = $pass | .ftp.remoteDir = $dir' \
   "$CONFIG_FILE" > "$CONFIG_FILE.tmp" && mv "$CONFIG_FILE.tmp" "$CONFIG_FILE"

echo -e "\n${GREEN}✅ Configuration saved to $CONFIG_FILE${NC}"
echo -e "${GREEN}✨ Setup complete! You can now run: ./deploy-backend-api.sh${NC}\n"

# Verify gitignore
if grep -q "deploy-config.json" .gitignore 2>/dev/null; then
    echo -e "${GREEN}✅ $CONFIG_FILE is already in .gitignore${NC}"
else
    echo -e "${YELLOW}⚠️  Warning: $CONFIG_FILE might not be in .gitignore${NC}"
fi
