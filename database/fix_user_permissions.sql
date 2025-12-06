-- Fix MySQL User Permissions for printing_admin
-- Run this as MySQL root user to grant access from the server IP

-- Option 1: Grant access from the specific IPv6 address (most secure)
-- Replace '2a0a:8d80:0:9123::136' with your actual server IP if different
GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'2a0a:8d80:0:9123::136' IDENTIFIED BY 'vL2tI2sV7c';
FLUSH PRIVILEGES;

-- Option 2: Grant access from any host (less secure, but easier)
-- Uncomment the line below if Option 1 doesn't work
-- GRANT ALL PRIVILEGES ON printing.* TO 'printing_admin'@'%' IDENTIFIED BY 'vL2tI2sV7c';
-- FLUSH PRIVILEGES;

-- Verify the grants
SHOW GRANTS FOR 'printing_admin'@'2a0a:8d80:0:9123::136';
-- Or if using wildcard:
-- SHOW GRANTS FOR 'printing_admin'@'%';

-- Check current user hosts
SELECT user, host FROM mysql.user WHERE user = 'printing_admin';

