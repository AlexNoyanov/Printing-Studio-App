-- Migration: Add support for multiple links per order
-- This creates a new table to store multiple model links for each order

CREATE TABLE IF NOT EXISTS order_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50) NOT NULL,
  link_url TEXT NOT NULL,
  link_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order_id (order_id),
  INDEX idx_link_order (link_order),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing model_link data to order_links table
-- This preserves existing single links
INSERT INTO order_links (order_id, link_url, link_order)
SELECT id, model_link, 0
FROM orders
WHERE model_link IS NOT NULL AND model_link != '';

-- Note: We keep the model_link column for backward compatibility
-- You can optionally drop it later after verifying the migration worked:
-- ALTER TABLE orders DROP COLUMN model_link;

