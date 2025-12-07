# Quick Setup: Telegram Notifications

## The Error You're Seeing

```
missing telegram token or user list
```

This means GitHub Secrets for Telegram are not configured yet.

## Quick Fix (2 Steps)

### Step 1: Get Your Telegram Chat ID

**Easiest method:**
1. Open Telegram
2. Search for `@userinfobot` or `@getidsbot`
3. Start a conversation
4. It will show your Chat ID (a number like `123456789`)

### Step 2: Add GitHub Secrets

1. Go to: https://github.com/AlexNoyanov/Printing-Studio-App/settings/secrets/actions
2. Click **"New repository secret"**

**Add Secret 1:**
- Name: `TELEGRAM_BOT_TOKEN`
- Value: Get from @BotFather (send `/newbot` to create one, or `/mybots` to see existing)

**Add Secret 2:**
- Name: `TELEGRAM_CHAT_ID`
- Value: Your Chat ID from Step 1

## Create a New Bot Token (If Needed)

1. Open Telegram, search for `@BotFather`
2. Send `/newbot`
3. Follow instructions to create a bot
4. BotFather will give you a token like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`
5. Use that token in GitHub Secrets

## Test

After adding secrets:
1. Go to **Actions** tab
2. Run any workflow manually
3. If it fails, you should receive a Telegram message
4. If secrets are missing, the workflow will skip Telegram notification (no error)

## That's It!

Once secrets are configured, notifications will work automatically. The workflow now handles missing secrets gracefully and won't fail if they're not set.

