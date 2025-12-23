-- Add language column to users table
-- Default language is 'en' (English)

ALTER TABLE users
ADD COLUMN language VARCHAR(10) NOT NULL DEFAULT 'en' AFTER role;

-- Add index for language if needed for queries
CREATE INDEX idx_language ON users(language);

-- Update existing users to have English as default language (if column was just added)
UPDATE users SET language = 'en' WHERE language IS NULL OR language = '';

