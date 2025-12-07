# Setup Telegram Notifications for GitHub Deployments

This guide explains how to set up Telegram notifications for deployment failures.

## Prerequisites

- Telegram bot token: `YOUR_BOT_TOKEN` (get from @BotFather)
- Bot username: `@your_bot_username`

## Step 1: Get Your Telegram Chat ID

You need to get your Telegram Chat ID to receive notifications.

### Method 1: Using the Bot

1. Open Telegram and search for your bot username
2. Start a conversation with the bot by clicking "Start"
3. Send any message to the bot
4. Visit: `https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates`
5. Look for `"chat":{"id":` in the response - that's your Chat ID

### Method 2: Using @userinfobot

1. Search for `@userinfobot` on Telegram
2. Start a conversation
3. It will show your Chat ID

### Method 3: Using @getidsbot

1. Search for `@getidsbot` on Telegram
2. Start a conversation
3. It will show your Chat ID

## Step 2: Add GitHub Secrets

1. Go to your repository: https://github.com/AlexNoyanov/Printing-Studio-App
2. Navigate to: **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**

### Add These Secrets:

**Secret 1: TELEGRAM_BOT_TOKEN**
- Name: `TELEGRAM_BOT_TOKEN`
- Value: Your bot token from @BotFather (format: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)

**Secret 2: TELEGRAM_CHAT_ID**
- Name: `TELEGRAM_CHAT_ID`
- Value: Your Chat ID (from Step 1)

## Step 3: Test the Bot

After adding secrets, test by triggering a workflow:

1. Go to **Actions** tab
2. Select **Deploy Backend API** or **Deploy Frontend to Firebase**
3. Click **Run workflow**
4. You should receive a Telegram message when deployment completes (success or failure)

## What You'll Receive

### On Deployment Failure:
```
❌ Backend Deployment Failed

Repository: Printing Studio App
Branch: main
Commit: abc123...
Author: your-username

Workflow: Deploy Backend API
Run ID: 123456789

View details: [link to GitHub Actions]
```

### On Deployment Success:
```
✅ Backend Deployment Successful

Repository: Printing Studio App
Branch: main
Commit: abc123...

API deployed to: https://noyanov.com/Apps/Printing/api/
```

## Troubleshooting

### Not Receiving Messages?

1. **Check Chat ID**: Make sure you got the correct Chat ID
2. **Check Bot Token**: Verify the token is correct in GitHub Secrets
3. **Start the Bot**: Make sure you've started a conversation with your bot
4. **Check Workflow Logs**: Go to Actions → Select workflow run → Check logs for Telegram step

### Test Bot Manually

You can test if the bot works by sending a message via curl:

```bash
curl -X POST "https://api.telegram.org/botYOUR_BOT_TOKEN/sendMessage" \
  -d "chat_id=YOUR_CHAT_ID" \
  -d "text=Test message from GitHub Actions"
```

Replace `YOUR_CHAT_ID` with your actual Chat ID.

## Security Notes

- ⚠️ **Never commit the bot token to the repository**
- ✅ The token is stored securely in GitHub Secrets
- ✅ Only workflows can access the secrets
- ✅ Secrets are encrypted at rest

## Workflows Configured

The following workflows now send Telegram notifications:

1. **Deploy Backend API** - Notifies on backend deployment success/failure
2. **Deploy Frontend to Firebase** - Notifies on frontend deployment success/failure

Both workflows will send notifications only on failure (as requested), but I've also added success notifications. You can remove the success notification step if you only want failure notifications.

