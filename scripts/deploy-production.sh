#!/bin/bash
# Deploy to production Firebase project (printing-studio-app-4e0e6)
# English/original version

echo "🚀 Deploying to PRODUCTION project (printing-studio-app-4e0e6)..."

# Switch to production project
firebase use production

# Copy production firebase.json
cp firebase.json.production firebase.json

# Build with production config
npm run build

# Deploy
firebase deploy --only hosting

echo "✅ Deployment to PRODUCTION complete!"
echo "🌐 App available at: https://printing-studio-app-4e0e6.web.app"

