<?php
/**
 * Create order_links table script
 * Run this via browser: https://noyanov.com/Apps/Printing/database/create_order_links.php
 */

require_once __DIR__ . '/../backend/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Create order_links Table</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#1a1a1a;color:#e0e0e0;}";
echo ".success{color:#51cf66;background:rgba(81,207,102,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo ".error{color:#ff6b6b;background:rgba(255,107,107,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo ".info{color:#87CEEB;background:rgba(135,206,235,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo "</style></head><body>";
echo "<h1>Create order_links Table</h1>";

try {
    $pdo = getDBConnection();
    
    echo "<div class='info'>✓ Connected to database successfully</div>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_links'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<div class='info'>Table 'order_links' already exists.</div>";
        
        // Check if copies column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM order_links LIKE 'copies'");
        $copiesExists = $stmt->rowCount() > 0;
        
        if (!$copiesExists) {
            echo "<div class='info'>Adding 'copies' column...</div>";
            $pdo->exec("ALTER TABLE order_links ADD COLUMN copies INT NOT NULL DEFAULT 1 AFTER link_url");
            echo "<div class='success'>✓ Added 'copies' column</div>";
        } else {
            echo "<div class='info'>Table structure is correct.</div>";
        }
    } else {
        echo "<div class='info'>Creating order_links table...</div>";
        
        $pdo->exec("CREATE TABLE order_links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id VARCHAR(50) NOT NULL,
            link_url TEXT NOT NULL,
            copies INT NOT NULL DEFAULT 1,
            link_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order_id (order_id),
            INDEX idx_link_order (link_order),
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<div class='success'>✓ Table 'order_links' created successfully</div>";
        
        // Migrate existing data if any
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE model_link IS NOT NULL AND model_link != ''");
        $orderCount = $stmt->fetch()['count'];
        
        if ($orderCount > 0) {
            echo "<div class='info'>Migrating existing order links ($orderCount orders)...</div>";
            $pdo->exec("INSERT IGNORE INTO order_links (order_id, link_url, copies, link_order)
                       SELECT id, model_link, 1, 0
                       FROM orders
                       WHERE model_link IS NOT NULL AND model_link != ''
                       AND id NOT IN (SELECT DISTINCT order_id FROM order_links)");
            echo "<div class='success'>✓ Migration completed</div>";
        }
    }
    
    // Show table structure
    $stmt = $pdo->query("DESCRIBE order_links");
    $columns = $stmt->fetchAll();
    
    echo "<div class='info'><strong>Table Structure:</strong></div>";
    echo "<table style='width:100%;border-collapse:collapse;margin:10px 0;'>";
    echo "<tr style='background:#2a2a2a;'><th style='padding:8px;border:1px solid #3a3a3a;'>Column</th><th style='padding:8px;border:1px solid #3a3a3a;'>Type</th><th style='padding:8px;border:1px solid #3a3a3a;'>Null</th><th style='padding:8px;border:1px solid #3a3a3a;'>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td style='padding:8px;border:1px solid #3a3a3a;'>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td style='padding:8px;border:1px solid #3a3a3a;'>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td style='padding:8px;border:1px solid #3a3a3a;'>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td style='padding:8px;border:1px solid #3a3a3a;'>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div class='success'><strong>✓ Setup completed successfully!</strong></div>";
    
} catch (PDOException $e) {
    echo "<div class='error'><strong>✗ Error:</strong><br>";
    echo "Message: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "Code: " . $e->getCode() . "</div>";
    
    if ($e->getCode() == 1045) {
        echo "<div class='error'>Database authentication failed. Please check your config.php credentials.</div>";
    } elseif ($e->getCode() == 2002) {
        echo "<div class='error'>Cannot connect to database server. Please check your host and network.</div>";
    } elseif (strpos($e->getMessage(), 'CREATE') !== false) {
        echo "<div class='error'>Table creation failed. The database user might not have CREATE TABLE permissions.</div>";
        echo "<div class='info'>Please run the SQL manually in phpMyAdmin:</div>";
        echo "<pre style='background:#2a2a2a;padding:10px;border-radius:5px;overflow-x:auto;'>";
        echo "CREATE TABLE order_links (\n";
        echo "  id INT AUTO_INCREMENT PRIMARY KEY,\n";
        echo "  order_id VARCHAR(50) NOT NULL,\n";
        echo "  link_url TEXT NOT NULL,\n";
        echo "  copies INT NOT NULL DEFAULT 1,\n";
        echo "  link_order INT NOT NULL DEFAULT 0,\n";
        echo "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        echo "  INDEX idx_order_id (order_id),\n";
        echo "  INDEX idx_link_order (link_order),\n";
        echo "  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE\n";
        echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<div class='error'><strong>✗ Error:</strong><br>";
    echo htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</body></html>";

