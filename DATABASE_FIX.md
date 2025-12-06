# Database Connection Fix

## Problem
Database connection is failing with error:
```
Access denied for user 'YOUR_DB_USER'@'YOUR_IP' (using password: YES)
```

This means the MySQL user `YOUR_DB_USER` doesn't have permission to connect from your server's IP address.

## Solution

### Step 1: Connect to MySQL Server

Connect to your MySQL server as root or admin:

```bash
mysql -h YOUR_DB_HOST -u root -p
```

Or via phpMyAdmin:
1. Login to phpMyAdmin
2. Go to SQL tab

### Step 2: Grant Permissions

Run one of these SQL commands (choose the most appropriate):

#### Option A: Grant from Any Host (Easiest - Recommended for testing)
```sql
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'%' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;
```

#### Option B: Grant from Specific IPv6 Address (More Secure)
```sql
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'2a0a:8d80:0:9123::136' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;
```

#### Option C: Grant from Hostname
```sql
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'YOUR_DB_HOST' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;
```

#### Option D: Grant from IP Range (If server has multiple IPs)
```sql
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'2a0a:8d80:0:9123::/64' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;
```

### Step 3: Verify Permissions

Check if the user exists and has the right permissions:

```sql
-- Check all grants for the user
SHOW GRANTS FOR 'YOUR_DB_USER'@'%';
SHOW GRANTS FOR 'YOUR_DB_USER'@'2a0a:8d80:0:9123::136';

-- Check all user accounts
SELECT user, host FROM mysql.user WHERE user = 'YOUR_DB_USER';
```

### Step 4: Test Connection

After granting permissions, test the connection:

```bash
# Test from command line
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD printing -e "SELECT 1"

# Test via API
curl "https://noyanov.com/Apps/Printing/api/health"
```

Should return: `{"status":"ok","database":"connected"}`

## If User Doesn't Exist

If the user `YOUR_DB_USER` doesn't exist, create it first:

```sql
-- Create user
CREATE USER 'YOUR_DB_USER'@'%' IDENTIFIED BY 'YOUR_PASSWORD';

-- Grant privileges
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'%';

-- Flush privileges
FLUSH PRIVILEGES;
```

## If Database Doesn't Exist

If the `printing` database doesn't exist, create it:

```sql
CREATE DATABASE IF NOT EXISTS printing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then run the schema:
```bash
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD printing < database/schema.sql
```

## Troubleshooting

### Still Getting Access Denied?

1. **Check if user exists:**
   ```sql
   SELECT user, host FROM mysql.user WHERE user = 'YOUR_DB_USER';
   ```

2. **Check current connection IP:**
   - The error shows your server's IP: `2a0a:8d80:0:9123::136`
   - Make sure you grant to this exact IP or use `%` for any host

3. **Try revoking and re-granting:**
   ```sql
   REVOKE ALL PRIVILEGES ON printing.* FROM 'YOUR_DB_USER'@'%';
   DROP USER 'YOUR_DB_USER'@'%';
   CREATE USER 'YOUR_DB_USER'@'%' IDENTIFIED BY 'YOUR_PASSWORD';
   GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'%';
   FLUSH PRIVILEGES;
   ```

4. **Check MySQL bind address:**
   - Ensure MySQL is listening on the correct interface
   - Check `/etc/mysql/my.cnf` for `bind-address` setting

### Using phpMyAdmin

1. Login to phpMyAdmin
2. Click "User accounts" tab
3. Find or create `YOUR_DB_USER`
4. Click "Edit privileges"
5. Select database `printing`
6. Grant all privileges
7. Save

## Quick Fix Script

Save this as `fix_db.sh` and run it:

```bash
#!/bin/bash
mysql -h YOUR_DB_HOST -u root -p << EOF
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'%' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;
EOF
```

