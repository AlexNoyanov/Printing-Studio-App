-- Migration: Add materials/filaments table
-- This creates a table to store filament materials with color, shop link, and material type

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

-- Migrate existing colors to materials (optional - for backward compatibility)
-- This creates materials from existing colors if they have filament_link
-- INSERT INTO materials (id, user_id, name, color, material_type, shop_link)
-- SELECT 
--   CONCAT('mat_', c.id) as id,
--   c.user_id,
--   c.name,
--   c.value as color,
--   'PLA' as material_type, -- Default material type
--   c.filament_link as shop_link
-- FROM colors c
-- WHERE c.filament_link IS NOT NULL AND c.filament_link != ''
-- ON DUPLICATE KEY UPDATE shop_link = c.filament_link;

