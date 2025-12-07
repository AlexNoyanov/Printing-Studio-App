# Database Migration Guide: Multiple Links Per Order

## Overview

This migration adds support for multiple model links per order by creating a new `order_links` table and migrating existing data.

## What Changed

- **New Table**: `order_links` - stores multiple links for each order
- **Backward Compatibility**: The `model_link` column in `orders` table is kept for compatibility
- **Data Migration**: All existing single links are automatically migrated to the new table

## How to Run the Migration

### Option 1: Using the PHP Migration Script (Recommended)

1. Upload `database/run_migration.php` to your server at:
   ```
   /Apps/Printing/database/run_migration.php
   ```

2. Access it via browser:
   ```
   https://noyanov.com/Apps/Printing/database/run_migration.php
   ```

3. The script will:
   - Check if the table exists
   - Create it if needed
   - Migrate existing data
   - Show a summary

4. **Important**: Delete or protect this file after running to prevent unauthorized access:
   ```bash
   # Via FTP or SSH, delete the file after migration
   rm /Apps/Printing/database/run_migration.php
   ```

### Option 2: Using phpMyAdmin

1. Log into phpMyAdmin
2. Select your `printing` database
3. Go to the "SQL" tab
4. Copy and paste the contents of `database/add_order_links.sql`
5. Click "Go" to execute

### Option 3: Using MySQL Command Line

```bash
mysql -h s136.webhost1.ru -u alexn_printing_admin -p printing < database/add_order_links.sql
```

Or connect and run manually:
```bash
mysql -h s136.webhost1.ru -u alexn_printing_admin -p printing
```

Then paste the SQL:
```sql
CREATE TABLE IF NOT EXISTS order_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50) NOT NULL,
  link_url TEXT NOT NULL,
  link_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order_id (order_id),
  INDEX idx_link_order (link_order),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO order_links (order_id, link_url, link_order)
SELECT id, model_link, 0
FROM orders
WHERE model_link IS NOT NULL AND model_link != '';
```

## Verification

After running the migration, verify it worked:

```sql
-- Check if table exists
SHOW TABLES LIKE 'order_links';

-- Count migrated links
SELECT COUNT(*) FROM order_links;

-- Check a specific order's links
SELECT * FROM order_links WHERE order_id = 'YOUR_ORDER_ID';
```

## Rollback (if needed)

If you need to rollback (not recommended after data is in use):

```sql
-- Drop the new table
DROP TABLE IF EXISTS order_links;
```

Note: This will delete all multiple links. Single links will still be in the `orders.model_link` column.

## Troubleshooting

### Error: Table already exists
- This is fine! The migration script will check and only migrate new data.

### Error: Foreign key constraint fails
- Make sure all `order_id` values in `order_links` reference existing orders
- The migration script handles this automatically

### Error: Access denied
- Check database user permissions
- Ensure the user has CREATE, INSERT, and SELECT privileges

## After Migration

1. ✅ Test creating an order with multiple links in the frontend
2. ✅ Verify printer owners can see all links in the dashboard
3. ✅ Check that existing orders still display correctly

## Support

If you encounter issues:
1. Check the error message in the migration script output
2. Verify database credentials in `backend/config.php`
3. Ensure the database user has proper permissions

