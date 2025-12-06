# Test Database Connection

## Quick Test via Browser

1. **Upload the test file:**
   - Upload `backend/test_db_connection.php` to `/Apps/Printing/backend/test_db_connection.php` on your server

2. **Access in browser:**
   - Visit: `https://noyanov.com/Apps/Printing/backend/test_db_connection.php`
   - You'll see a JSON response with connection status

## Test via Command Line (if you have SSH access)

```bash
php backend/test_connection.php
```

## Expected Results

### ✅ Success Response:
```json
{
    "status": "success",
    "message": "Connection successful!",
    "server_info": {
        "version": "MySQL version",
        "database_name": "printing",
        "current_user": "printing_admin@..."
    },
    "tables": ["users", "orders", "colors", "order_colors"],
    "table_count": 4,
    "user_count": 0
}
```

### ❌ Error Response:
```json
{
    "status": "error",
    "error_code": 1045,
    "error_message": "Access denied for user...",
    "suggestion": "Grant permissions with: GRANT ALL PRIVILEGES..."
}
```

## Common Error Codes

- **1045**: Authentication failed - User doesn't have permission
- **2002**: Connection failed - Wrong hostname or server down
- **1049**: Database doesn't exist - Need to create database

## Fix Authentication Error (1045)

If you get error 1045, run this in phpMyAdmin SQL tab:

```sql
GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'%' IDENTIFIED BY 'vL2tI2sV7c';
FLUSH PRIVILEGES;
```

Or for specific IP:

```sql
GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'2a0a:8d80:0:9123::136' IDENTIFIED BY 'vL2tI2sV7c';
FLUSH PRIVILEGES;
```

