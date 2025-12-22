-- Add rating column to users table
-- Rating is a decimal(3,1) to allow values from 0.0 to 10.0

ALTER TABLE users 
ADD COLUMN rating DECIMAL(3,1) DEFAULT NULL,
ADD COLUMN rating_count INT DEFAULT 0;

-- Add index for faster queries on rating
CREATE INDEX idx_rating ON users(rating);

