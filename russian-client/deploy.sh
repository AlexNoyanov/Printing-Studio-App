#!/bin/bash
# Deploy Russian client to Firebase

echo "🚀 Deploying Russian client to Firebase..."

cd "$(dirname "$0")"

# Build
echo "📦 Building project..."
npm run build

# Deploy
echo "🚀 Deploying to Firebase..."
firebase deploy --only hosting --project d-print-electrozavodskaya

echo "✅ Deployment complete!"
echo "🌐 App available at: https://print-electrozavodskaya.web.app"

