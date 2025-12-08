-- Add printed status column to order_links table
-- This tracks which models have been printed

ALTER TABLE order_links 
ADD COLUMN printed BOOLEAN NOT NULL DEFAULT FALSE AFTER copies;

-- Add index for faster queries
CREATE INDEX idx_printed ON order_links(printed);

