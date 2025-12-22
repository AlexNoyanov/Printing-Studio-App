# Backend API Deployment - Rating System Update

## Files Changed
- ✅ `backend/api/users.php` - Added rating support (rating, rating_count fields)

## Deployment Steps

### Option 1: Manual FTP Upload

1. **Connect to your FTP server:**
   - Server: Your FTP server address
   - Path: `/Apps/Printing/backend/api/`

2. **Upload the updated file:**
   - Upload `backend/api/users.php` to replace the existing file on the server

### Option 2: Using deploy-backend.sh Script

1. **Set FTP credentials as environment variables:**
   ```bash
   export FTP_SERVER="your-ftp-server.com"
   export FTP_USER="your-ftp-username"
   export FTP_PASS="your-ftp-password"
   ```

2. **Run the deployment script:**
   ```bash
   ./deploy-backend.sh
   ```

### Option 3: Using FTP Client (GUI)

1. Open your FTP client (FileZilla, Cyberduck, etc.)
2. Connect to your server
3. Navigate to `/Apps/Printing/backend/api/`
4. Upload `backend/api/users.php` (overwrite existing)

## Database Migration Required

⚠️ **IMPORTANT:** Before the rating system will work, you must run the database migration:

```sql
-- Run this SQL on your database:
ALTER TABLE users 
ADD COLUMN rating DECIMAL(3,1) DEFAULT NULL,
ADD COLUMN rating_count INT DEFAULT 0;

CREATE INDEX idx_rating ON users(rating);
```

Or use the migration file:
- `database/add_user_rating.sql`

## Verification After Deployment

1. **Test the API endpoint:**
   ```bash
   curl https://noyanov.com/Apps/Printing/api/users.php?id=USER_ID
   ```
   
   Should now include `rating` and `ratingCount` fields in the response.

2. **Test updating a rating:**
   ```bash
   curl -X PUT https://noyanov.com/Apps/Printing/api/users.php?id=USER_ID \
     -H "Content-Type: application/json" \
     -d '{"rating": 8.5}'
   ```

## What Changed in users.php

### GET /users.php?id={userId}
- Now returns `rating` and `ratingCount` fields

### PUT /users.php?id={userId}
- Added support for `rating` field in request body
- Calculates running average when rating is updated
- Validates rating is between 0-10
- Increments rating_count automatically

### GET /users.php (all users)
- Now includes `rating` and `ratingCount` in user objects

## Frontend Compatibility

The frontend (Dashboard.vue) is already updated to:
- Display user ratings in order headers
- Show rating input section for completed orders
- Save ratings via the updated API

Once the backend is deployed and database migration is run, the rating system will be fully functional.

