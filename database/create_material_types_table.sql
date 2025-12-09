-- Create material_types table to store available material types
CREATE TABLE IF NOT EXISTS material_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default material types
INSERT IGNORE INTO material_types (name) VALUES
  ('PLA'),
  ('PETG'),
  ('ABS'),
  ('TPU'),
  ('ASA'),
  ('PC'),
  ('Nylon'),
  ('Wood'),
  ('Metal'),
  ('Other');

