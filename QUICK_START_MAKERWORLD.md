# Quick Start: Display MakerWorld Models

## Option 1: Use Puppeteer Service (Recommended - Shows Actual Images)

1. **Install Puppeteer:**
   ```bash
   npm install puppeteer
   ```

2. **Start the MakerWorld fetcher service:**
   ```bash
   npm run makerworld-fetcher
   ```

3. **The service will:**
   - Render MakerWorld pages in headless Chrome
   - Extract Open Graph data (titles, descriptions, images)
   - Take screenshots if images aren't available
   - Return all data including base64-encoded screenshots

4. **The PHP backend automatically uses this service** when it's running on port 3002

## Option 2: Use Iframe Embed (Works Immediately)

The frontend now tries to embed MakerWorld pages directly using iframes. This works immediately without any setup, though MakerWorld may block it with X-Frame-Options.

If iframes are blocked, you'll see a styled placeholder with a link to view on MakerWorld.

## Option 3: Current Fallback (Works Now)

The current implementation:
- Shows model titles extracted from URLs
- Shows model IDs
- Provides styled placeholder cards
- Links directly to MakerWorld to view models

## Testing the Puppeteer Service

1. Start the service:
   ```bash
   npm run makerworld-fetcher
   ```

2. Test it:
   ```bash
   curl -X POST http://localhost:3002/fetch \
     -H "Content-Type: application/json" \
     -d '{"url": "https://makerworld.com/en/models/80387-christmas-ball-set-3"}'
   ```

3. Check the Shop page - models should now show screenshots or images!

## Troubleshooting

### Puppeteer Not Installing?
- Make sure you have enough disk space (Chromium is ~300MB)
- On Linux servers, install dependencies (see MAKERWORLD_SETUP.md)

### Service Not Starting?
- Check if port 3002 is already in use
- Check Node.js version (needs v18+)
- See error messages in console

### Still No Images?
- The service takes 2-5 seconds per model
- Check browser console for errors
- Verify the service is running: `curl http://localhost:3002/health`

## Best Solution

**Use Puppeteer Service** - It provides:
- ✅ Real images from MakerWorld
- ✅ Screenshots as fallback
- ✅ Complete model data
- ✅ Works with JavaScript-rendered content

The service runs continuously and the PHP backend uses it automatically when available.

