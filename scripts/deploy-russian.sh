#!/bin/bash
# Deploy to Russian Firebase project (d-print-electrozavodskaya)
# Russian version with new client pages

echo "🚀 Deploying to RUSSIAN project (d-print-electrozavodskaya)..."

# Switch to Russian project
firebase use russian

# Copy Russian firebase.json
cp firebase.json.russian firebase.json

# Build with Russian/Firebase config
npm run build:firebase

# Deploy
firebase deploy --only hosting

echo "✅ Deployment to RUSSIAN project complete!"
echo "🌐 App available at: https://d-print-electrozavodskaya.web.app"

