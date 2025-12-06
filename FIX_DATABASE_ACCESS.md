# Fix Database Access Error

## Problem
You're getting this error:
```
Access denied for user 'printing_admin'@'2a0a:8d80:0:9123::136' (using password: YES)
```

This means the MySQL user `printing_admin` doesn't have permission to connect from your server's IP address.

## Solution

### Step 1: Connect to MySQL as Root

Connect to your MySQL server as root user:

```bash
mysql -h s136.webhost1.ru -u root -p
```

Or via phpMyAdmin:
1. Login to phpMyAdmin
2. Go to SQL tab

### Step 2: Grant Permissions

Run one of these SQL commands:

#### Option A: Grant from Specific IP (Recommended)
```sql
GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'2a0a:8d80:0:9123::136' IDENTIFIED BY 'vL2tI2sV7c';
FLUSH PRIVILEGES;
```

#### Option B: Grant from Any Host (Easier, but less secure)
```sql
GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'%' IDENTIFIED BY 'vL2tI2sV7c';
FLUSH PRIVILEGES;
```

### Step 3: Verify

Check if the user exists and has permissions:

```sql
-- Check user hosts
SELECT user, host FROM mysql.user WHERE user = 'printing_admin';

-- Check grants
SHOW GRANTS FOR 'printing_admin'@'2a0a:8d80:0:9123::136';
-- Or if using wildcard:
SHOW GRANTS FOR 'printing_admin'@'%';
```

### Step 4: Test Connection

Test the connection from your server:

```bash
mysql -h s136.webhost1.ru -u printing_admin -pvL2tI2sV7c printing -e "SELECT 1"
```

Should return: `1`

## If User Doesn't Exist

If the user doesn't exist, create it first:

```sql
CREATE USER 'printing_admin'@'2a0a:8d80:0:9123::136' IDENTIFIED BY 'vL2tI2sV7c';
GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'2a0a:8d80:0:9123::136';
FLUSH PRIVILEGES;
```

Or for any host:

```sql
CREATE USER 'printing_admin'@'%' IDENTIFIED BY 'vL2tI2sV7c';
GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'%';
FLUSH PRIVILEGES;
```

## Quick Fix Script

You can also run the SQL file directly:

```bash
mysql -h s136.webhost1.ru -u root -p < database/fix_user_permissions.sql
```

## After Fixing

1. The API should now be able to connect to the database
2. Try registering a user again
3. The error should be resolved

## Important Notes

- The IP address `2a0a:8d80:0:9123::136` is your server's IPv6 address
- If your server has multiple IPs, you may need to grant access from all of them
- Using `%` (wildcard) allows connection from any IP, which is less secure but more flexible
- Always use strong passwords in production

