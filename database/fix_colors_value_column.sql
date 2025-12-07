-- Fix colors.value column to handle longer values
-- The value column should store hex color codes, but we need to support longer values
-- in case color names are being used temporarily

ALTER TABLE colors MODIFY COLUMN value VARCHAR(50) NOT NULL;

