<?php
// Test database connection with alexn_printing_admin username
// Usage: Access via browser or run: php test_connection_alexn.php

// Database credentials
$host = 's136.webhost1.ru';
$user = 'alexn_printing_admin';
$pass = 'vL2tI2sV7c';
$db = 'printing';
$charset = 'utf8mb4';

echo "Testing database connection...\n";
echo "Host: $host\n";
echo "User: $user\n";
echo "Database: $db\n";
echo "---\n\n";

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    echo "Attempting connection...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "✅ Connection successful!\n\n";
    
    // Test query
    echo "Testing query...\n";
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as database_name, USER() as current_user");
    $result = $stmt->fetch();
    
    echo "MySQL Version: " . $result['version'] . "\n";
    echo "Current Database: " . $result['database_name'] . "\n";
    echo "Current User: " . $result['current_user'] . "\n\n";
    
    // Check if tables exist
    echo "Checking tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️  No tables found in database.\n";
        echo "You may need to run: mysql -h $host -u $user -p$pass $db < database/schema.sql\n";
    } else {
        echo "✅ Found " . count($tables) . " table(s):\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    }
    
    // Check user permissions
    echo "\nChecking user permissions...\n";
    $stmt = $pdo->query("SELECT user, host FROM mysql.user WHERE user = '$user'");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "⚠️  User '$user' not found in mysql.user table.\n";
    } else {
        echo "✅ User '$user' found with the following host permissions:\n";
        foreach ($users as $u) {
            echo "  - " . $u['user'] . "@" . $u['host'] . "\n";
        }
    }
    
    // Check grants
    echo "\nChecking grants...\n";
    try {
        $stmt = $pdo->query("SHOW GRANTS FOR '$user'@'%'");
        $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($grants)) {
            echo "✅ Grants for '$user'@'%':\n";
            foreach ($grants as $grant) {
                echo "  - $grant\n";
            }
        }
    } catch (PDOException $e) {
        echo "⚠️  Could not check grants for '$user'@'%': " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ All tests passed! Database connection is working.\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n\n";
    
    if ($e->getCode() == 1045) {
        echo "This is an authentication error. Possible causes:\n";
        echo "1. Wrong username (trying: $user)\n";
        echo "2. Wrong password\n";
        echo "3. User doesn't have permission to connect from this IP\n";
        echo "4. User doesn't exist\n\n";
        echo "To fix, run as MySQL root:\n";
        echo "GRANT ALL PRIVILEGES ON printing.* TO '$user'@'%' IDENTIFIED BY 'vL2tI2sV7c';\n";
        echo "FLUSH PRIVILEGES;\n";
    } elseif ($e->getCode() == 2002) {
        echo "This is a connection error. Possible causes:\n";
        echo "1. Wrong hostname\n";
        echo "2. Server is down\n";
        echo "3. Firewall blocking connection\n";
    } elseif ($e->getCode() == 1049) {
        echo "Database 'printing' doesn't exist.\n";
        echo "Create it with: CREATE DATABASE printing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    }
    
    exit(1);
}

