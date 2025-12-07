-- Migration: Add copies field to order_links table
-- This allows each model link to have a number of copies

ALTER TABLE order_links 
ADD COLUMN copies INT NOT NULL DEFAULT 1 AFTER link_url;

