# Rating System Troubleshooting Guide

## Common Issues and Solutions

### 1. Rating Not Saving

**Possible Causes:**
- Database columns `rating` and `rating_count` don't exist
- API endpoint not accessible
- CORS issues
- JavaScript errors in browser console

**Solutions:**

1. **Check if database migration was run:**
   ```sql
   DESCRIBE users;
   ```
   Should show `rating` and `rating_count` columns. If not, run:
   ```sql
   ALTER TABLE users 
   ADD COLUMN rating DECIMAL(3,1) DEFAULT NULL,
   ADD COLUMN rating_count INT DEFAULT 0;
   
   CREATE INDEX idx_rating ON users(rating);
   ```

2. **Check browser console for errors:**
   - Open Developer Tools (F12)
   - Go to Console tab
   - Look for errors when clicking "Save Rating"

3. **Check Network tab:**
   - Open Developer Tools → Network tab
   - Try saving a rating
   - Check if the PUT request to `/users.php?id=...` is sent
   - Check the response status and body

4. **Test API endpoint directly:**
   ```bash
   curl -X PUT "https://noyanov.com/Apps/Printing/api/users.php?id=USER_ID" \
     -H "Content-Type: application/json" \
     -d '{"rating": 8.5}'
   ```

### 2. Rating Shows Wrong Value

**Possible Causes:**
- Frontend caching old values
- Backend calculating average incorrectly
- Multiple ratings being saved

**Solutions:**

1. **Refresh the page** to reload user ratings
2. **Check database directly:**
   ```sql
   SELECT id, username, rating, rating_count FROM users WHERE id = 'USER_ID';
   ```

3. **Clear browser cache** or do hard refresh (Ctrl+Shift+R or Cmd+Shift+R)

### 3. "Failed to save rating" Error

**Check the error message in the alert dialog** - it should show the specific error.

Common errors:
- `User ID required` - userId is null or undefined
- `Rating must be between 0 and 10` - Invalid rating value
- `Database error: ...` - SQL error (check if columns exist)
- `HTTP error! status: 404` - User not found
- `HTTP error! status: 500` - Server error (check server logs)

### 4. Rating Section Not Appearing

**Possible Causes:**
- Order status is not "Done"
- JavaScript error preventing render
- Vue component not updating

**Solutions:**

1. **Ensure order status is "Done"** - Rating section only appears for completed orders
2. **Check browser console** for JavaScript errors
3. **Expand the order card** - Rating section is inside the collapsible order body

### 5. Database Column Errors

If you see errors like "Unknown column 'rating' in 'field list'":

1. **Run the migration SQL:**
   ```sql
   ALTER TABLE users 
   ADD COLUMN rating DECIMAL(3,1) DEFAULT NULL,
   ADD COLUMN rating_count INT DEFAULT 0;
   
   CREATE INDEX idx_rating ON users(rating);
   ```

2. **Verify columns exist:**
   ```sql
   SHOW COLUMNS FROM users LIKE 'rating%';
   ```

## Testing Steps

1. **Ensure database migration is complete**
2. **Open Dashboard as printer user**
3. **Find an order with status "Done"**
4. **Expand the order card**
5. **Enter a rating (0-10) in the rating input**
6. **Click "Save Rating"**
7. **Check for success message**
8. **Verify rating appears next to user's name in order header**
9. **Check database to confirm rating was saved**

## Debug Checklist

- [ ] Database migration run successfully
- [ ] Backend API file `users.php` is deployed with rating support
- [ ] Order status is "Done"
- [ ] Order card is expanded
- [ ] Rating input accepts values 0-10
- [ ] "Save Rating" button is enabled when rating is entered
- [ ] No JavaScript errors in console
- [ ] API request is sent (check Network tab)
- [ ] API returns success response
- [ ] User rating appears in order header after save

## API Endpoint Testing

Test the rating endpoint manually:

```bash
# Get user (should include rating field)
curl "https://noyanov.com/Apps/Printing/api/users.php?id=USER_ID"

# Update user rating
curl -X PUT "https://noyanov.com/Apps/Printing/api/users.php?id=USER_ID" \
  -H "Content-Type: application/json" \
  -d '{"rating": 8.5}'

# Get user again to verify rating was updated
curl "https://noyanov.com/Apps/Printing/api/users.php?id=USER_ID"
```

Expected response should include:
```json
{
  "id": "...",
  "username": "...",
  "rating": 8.5,
  "ratingCount": 1,
  ...
}
```

