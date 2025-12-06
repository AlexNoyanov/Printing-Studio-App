-- Fix MySQL Permissions for Printing App
-- Run this on your MySQL server to grant access from your web server

-- Option 1: Grant access from any host (less secure but easier)
-- Use this if you're not sure of the exact IP
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'%' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;

-- Option 2: Grant access from specific IPv6 address (more secure)
-- Replace with your actual server IP: 2a0a:8d80:0:9123::136
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'2a0a:8d80:0:9123::136' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;

-- Option 3: Grant access from hostname (if using hostname)
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'YOUR_DB_HOST' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;

-- Option 4: Grant access from IP range (if your server has multiple IPs)
-- Adjust the subnet mask as needed
GRANT ALL PRIVILEGES ON printing.* TO 'YOUR_DB_USER'@'2a0a:8d80:0:9123::/64' IDENTIFIED BY 'YOUR_PASSWORD';
FLUSH PRIVILEGES;

-- Verify the grants
SHOW GRANTS FOR 'YOUR_DB_USER'@'%';
SHOW GRANTS FOR 'YOUR_DB_USER'@'2a0a:8d80:0:9123::136';

-- Check current user hosts
SELECT user, host FROM mysql.user WHERE user = 'YOUR_DB_USER';

