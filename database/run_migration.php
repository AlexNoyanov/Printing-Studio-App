<?php
/**
 * Database Migration Script: Add order_links table
 * 
 * This script creates the order_links table and migrates existing data.
 * Run this once via browser or command line:
 * 
 * Via browser: https://noyanov.com/Apps/Printing/database/run_migration.php
 * Via command line: php database/run_migration.php
 */

// Include database configuration
require_once __DIR__ . '/../backend/config.php';

// Set headers for browser access
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Database Migration</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#1a1a1a;color:#e0e0e0;}";
echo ".success{color:#51cf66;background:rgba(81,207,102,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo ".error{color:#ff6b6b;background:rgba(255,107,107,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo ".info{color:#87CEEB;background:rgba(135,206,235,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo "pre{background:#2a2a2a;padding:10px;border-radius:5px;overflow-x:auto;}";
echo "</style></head><body>";
echo "<h1>Database Migration: Add order_links Table</h1>";

try {
    $pdo = getDBConnection();
    
    echo "<div class='info'>✓ Connected to database successfully</div>";
    
    // Check if table already exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_links'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<div class='info'>ℹ Table 'order_links' already exists. Checking if migration is needed...</div>";
        
        // Check if there are any links in the table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM order_links");
        $result = $stmt->fetch();
        $linkCount = $result['count'];
        
        // Check if there are orders without links
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders o 
                            LEFT JOIN order_links ol ON o.id = ol.order_id 
                            WHERE ol.order_id IS NULL AND o.model_link IS NOT NULL AND o.model_link != ''");
        $result = $stmt->fetch();
        $unmigratedCount = $result['count'];
        
        if ($unmigratedCount > 0) {
            echo "<div class='info'>Found $unmigratedCount orders that need migration. Migrating...</div>";
            
            // Migrate remaining orders
            $stmt = $pdo->prepare("INSERT INTO order_links (order_id, link_url, link_order)
                                   SELECT id, model_link, 0
                                   FROM orders
                                   WHERE id NOT IN (SELECT DISTINCT order_id FROM order_links)
                                   AND model_link IS NOT NULL AND model_link != ''");
            $stmt->execute();
            $migrated = $stmt->rowCount();
            
            echo "<div class='success'>✓ Migrated $migrated additional orders</div>";
        } else {
            echo "<div class='success'>✓ Migration already complete. All orders have been migrated.</div>";
        }
    } else {
        echo "<div class='info'>Creating order_links table...</div>";
        
        // Create the table
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id VARCHAR(50) NOT NULL,
            link_url TEXT NOT NULL,
            link_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order_id (order_id),
            INDEX idx_link_order (link_order),
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<div class='success'>✓ Table 'order_links' created successfully</div>";
        
        // Migrate existing data
        echo "<div class='info'>Migrating existing model_link data...</div>";
        
        $stmt = $pdo->prepare("INSERT INTO order_links (order_id, link_url, link_order)
                               SELECT id, model_link, 0
                               FROM orders
                               WHERE model_link IS NOT NULL AND model_link != ''");
        $stmt->execute();
        $migrated = $stmt->rowCount();
        
        echo "<div class='success'>✓ Migrated $migrated orders to order_links table</div>";
    }
    
    // Show summary
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM order_links");
    $result = $stmt->fetch();
    $totalLinks = $result['count'];
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT order_id) as count FROM order_links");
    $result = $stmt->fetch();
    $ordersWithLinks = $result['count'];
    
    echo "<div class='info'><strong>Migration Summary:</strong><br>";
    echo "Total links in order_links table: $totalLinks<br>";
    echo "Orders with links: $ordersWithLinks</div>";
    
    echo "<div class='success'><strong>✓ Migration completed successfully!</strong></div>";
    echo "<p>You can now use multiple links per order. The frontend is already updated and ready to use.</p>";
    
} catch (PDOException $e) {
    echo "<div class='error'><strong>✗ Migration failed:</strong><br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "Code: " . $e->getCode() . "</div>";
    
    if ($e->getCode() == 1045) {
        echo "<div class='error'>Database authentication failed. Please check your config.php credentials.</div>";
    } elseif ($e->getCode() == 2002) {
        echo "<div class='error'>Cannot connect to database server. Please check your host and network.</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'><strong>✗ Error:</strong><br>";
    echo htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</body></html>";

