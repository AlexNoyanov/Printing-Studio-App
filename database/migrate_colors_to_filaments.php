<?php
/**
 * Migration Script: Convert Colors to Filaments (Materials)
 * 
 * This script migrates all existing colors from the colors table to the materials table.
 * Run this once via browser or command line:
 * 
 * Via browser: https://noyanov.com/Apps/Printing/database/migrate_colors_to_filaments.php
 * Via command line: php database/migrate_colors_to_filaments.php
 */

// Include database configuration
require_once __DIR__ . '/../backend/config.php';

// Set headers for browser access
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Colors to Filaments Migration</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#1a1a1a;color:#e0e0e0;}";
echo ".success{color:#51cf66;background:rgba(81,207,102,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo ".error{color:#ff6b6b;background:rgba(255,107,107,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo ".info{color:#87CEEB;background:rgba(135,206,235,0.1);padding:10px;border-radius:5px;margin:10px 0;}";
echo "pre{background:#2a2a2a;padding:10px;border-radius:5px;overflow-x:auto;}";
echo "table{border-collapse:collapse;width:100%;margin:10px 0;}";
echo "th,td{border:1px solid #3a3a3a;padding:8px;text-align:left;}";
echo "th{background:#2a2a2a;color:#87CEEB;}";
echo "</style></head><body>";
echo "<h1>Migration: Convert Colors to Filaments</h1>";

try {
    $pdo = getDBConnection();
    
    echo "<div class='info'>✓ Connected to database successfully</div>";
    
    // Check if materials table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'materials'");
    $materialsExists = $stmt->rowCount() > 0;
    
    if (!$materialsExists) {
        echo "<div class='info'>Creating materials table...</div>";
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS materials (
            id VARCHAR(50) PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(7) NOT NULL,
            material_type VARCHAR(50) NOT NULL,
            shop_link TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_material_type (material_type),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<div class='success'>✓ Materials table created</div>";
    }
    
    // Count existing colors
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM colors");
    $colorCount = $stmt->fetch()['count'];
    
    echo "<div class='info'>Found $colorCount colors in the database</div>";
    
    if ($colorCount > 0) {
        // Count existing materials
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM materials");
        $materialCount = $stmt->fetch()['count'];
        
        echo "<div class='info'>Found $materialCount existing materials</div>";
        
        // Migrate colors to materials
        echo "<div class='info'>Migrating colors to filaments...</div>";
        
        $stmt = $pdo->prepare("INSERT INTO materials (id, user_id, name, color, material_type, shop_link, created_at, updated_at)
                               SELECT 
                                 CONCAT('filament_', c.id) as id,
                                 c.user_id,
                                 c.name,
                                 c.value as color,
                                 'PLA' as material_type,
                                 c.filament_link as shop_link,
                                 c.created_at,
                                 c.updated_at
                               FROM colors c
                               WHERE NOT EXISTS (
                                 SELECT 1 FROM materials m 
                                 WHERE m.user_id = c.user_id 
                                 AND m.name = c.name 
                                 AND m.color = c.value
                               )");
        $stmt->execute();
        $migrated = $stmt->rowCount();
        
        echo "<div class='success'>✓ Migrated $migrated colors to filaments</div>";
        
        // Show summary
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM materials");
        $totalMaterials = $stmt->fetch()['count'];
        
        echo "<div class='info'><strong>Migration Summary:</strong><br>";
        echo "Total colors in database: $colorCount<br>";
        echo "Total materials after migration: $totalMaterials<br>";
        echo "Newly migrated: $migrated</div>";
        
        // Show sample migrated filaments
        $stmt = $pdo->query("SELECT name, color, material_type, shop_link FROM materials ORDER BY created_at DESC LIMIT 10");
        $samples = $stmt->fetchAll();
        
        if (!empty($samples)) {
            echo "<div class='info'><strong>Sample Migrated Filaments:</strong></div>";
            echo "<table>";
            echo "<tr><th>Name</th><th>Color</th><th>Material Type</th><th>Shop Link</th></tr>";
            foreach ($samples as $sample) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($sample['name']) . "</td>";
                echo "<td><span style='display:inline-block;width:20px;height:20px;background:" . htmlspecialchars($sample['color']) . ";border:1px solid #ddd;border-radius:50%;'></span> " . htmlspecialchars($sample['color']) . "</td>";
                echo "<td>" . htmlspecialchars($sample['material_type']) . "</td>";
                echo "<td>" . ($sample['shop_link'] ? htmlspecialchars($sample['shop_link']) : 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<div class='success'><strong>✓ Migration completed successfully!</strong></div>";
        echo "<p>All colors have been converted to filaments with default material type 'PLA'.</p>";
        echo "<p>You can now use the unified Filaments page to manage all your filaments.</p>";
    } else {
        echo "<div class='info'>No colors found to migrate.</div>";
    }
    
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

