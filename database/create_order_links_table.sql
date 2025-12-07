-- Create order_links table with copies support
-- This table stores multiple model links for each order

CREATE TABLE IF NOT EXISTS order_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50) NOT NULL,
  link_url TEXT NOT NULL,
  copies INT NOT NULL DEFAULT 1,
  link_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order_id (order_id),
  INDEX idx_link_order (link_order),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing model_link data to order_links table
-- This preserves existing single links with default 1 copy
INSERT IGNORE INTO order_links (order_id, link_url, copies, link_order)
SELECT id, model_link, 1, 0
FROM orders
WHERE model_link IS NOT NULL AND model_link != ''
AND id NOT IN (SELECT DISTINCT order_id FROM order_links);

