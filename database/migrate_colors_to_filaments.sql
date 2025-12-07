-- Migration: Convert existing colors to filaments (materials)
-- This migrates all colors from the colors table to the materials table
-- Each color becomes a filament with default material type 'PLA'

-- First, ensure materials table exists
CREATE TABLE IF NOT EXISTS materials (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate colors to materials
-- Only migrate colors that don't already exist in materials table
INSERT INTO materials (id, user_id, name, color, material_type, shop_link, created_at, updated_at)
SELECT 
  CONCAT('filament_', c.id) as id,
  c.user_id,
  c.name,
  c.value as color,
  'PLA' as material_type, -- Default material type
  c.filament_link as shop_link,
  c.created_at,
  c.updated_at
FROM colors c
WHERE NOT EXISTS (
  SELECT 1 FROM materials m 
  WHERE m.user_id = c.user_id 
  AND m.name = c.name 
  AND m.color = c.value
)
ON DUPLICATE KEY UPDATE 
  shop_link = COALESCE(VALUES(shop_link), materials.shop_link),
  updated_at = CURRENT_TIMESTAMP;

-- Note: Colors table is kept for backward compatibility
-- You can optionally drop it later after verifying the migration worked:
-- ALTER TABLE order_colors DROP FOREIGN KEY order_colors_ibfk_2;
-- DROP TABLE colors;

