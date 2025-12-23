# Backend API Deployment - Shop/MakerWorld Update

## Files Changed
- ✅ `backend/api/shop.php` - Improved MakerWorld model data extraction using Open Graph tags

## What Changed

### Improved MakerWorld Data Extraction

The shop.php file now uses a **much more reliable method** to extract model data from MakerWorld.com:

1. **Primary Method: Open Graph Tags**
   - Extracts `og:title`, `og:description`, `og:image` from meta tags
   - These tags are always present in the initial HTML (even with JavaScript)
   - Uses fast regex parsing instead of slow DOM parsing

2. **Fallback Methods:**
   - JSON-LD structured data extraction
   - DOM/XPath parsing for meta tags
   - URL-based title generation

3. **Better Error Handling:**
   - Always returns usable data (prevents "Failed to load" errors)
   - Generates basic model info from URL if scraping fails

## Deployment Steps

### Option 1: Manual FTP Upload (Recommended)

1. **Connect to your FTP server:**
   - Server: Your FTP server address
   - Path: `/Apps/Printing/backend/api/`

2. **Upload the updated file:**
   - Upload `backend/api/shop.php` to replace the existing file on the server

### Option 2: Using deploy-backend.sh Script

1. **Set FTP credentials as environment variables:**
   ```bash
   export FTP_SERVER="your-ftp-server.com"
   export FTP_USER="your-ftp-username"
   export FTP_PASS="your-ftp-password"
   ```

2. **Run the deployment script:**
   ```bash
   chmod +x deploy-backend.sh
   ./deploy-backend.sh
   ```

   Note: The script will validate PHP syntax before deploying.

### Option 3: Using FTP Client (GUI)

1. Open your FTP client (FileZilla, Cyberduck, etc.)
2. Connect to your server
3. Navigate to `/Apps/Printing/backend/api/`
4. Upload `backend/api/shop.php` (overwrite existing)

## Verification After Deployment

1. **Test the shop endpoint:**
   ```bash
   curl https://noyanov.com/Apps/Printing/api/shop.php
   ```
   
   Should return a list of printed MakerWorld models.

2. **Test fetching model data:**
   ```bash
   curl -X POST https://noyanov.com/Apps/Printing/api/shop.php \
     -H "Content-Type: application/json" \
     -d '{"url": "https://makerworld.com/en/models/123456-example-model"}'
   ```
   
   Should return model data with:
   - `title` - Model title (from og:title)
   - `description` - Model description (from og:description)
   - `image` - Preview image URL (from og:image)
   - `author` - Author name (if available)
   - `likes`, `downloads`, `views` - Statistics (if available)

## Expected Results

After deployment, the Shop page should now:
- ✅ Successfully load model data for MakerWorld URLs
- ✅ Display model titles, descriptions, and images
- ✅ Show proper preview cards instead of "Failed to load" errors
- ✅ Work reliably even if MakerWorld uses JavaScript (Open Graph tags are in initial HTML)

## Technical Details

### New Functions Added:
- `extractMakerWorldModelId()` - Extracts model ID from URL
- `extractOpenGraphData()` - Fast regex-based Open Graph extraction
- `createBasicModelData()` - Generates fallback data from URL

### Performance Improvements:
- Faster extraction using regex instead of DOM parsing for Open Graph tags
- Reduced timeout (15s request, 8s connect) for quicker failures
- Updated User-Agent for better compatibility

### Reliability Improvements:
- Multiple fallback methods ensure data is always returned
- Graceful degradation if scraping fails completely
- Better error logging for debugging

## No Database Changes Required

⚠️ **No database migration needed** - This is a code-only update.

## Testing in Production

After deployment, test the Shop page:
1. Navigate to `/shop` route
2. Verify that MakerWorld models display correctly
3. Check that images, titles, and descriptions are shown
4. Verify that "Retry" buttons work if some models fail to load

