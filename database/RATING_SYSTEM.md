# User Rating System

## Overview
The rating system allows printer users to rate customer users (out of 10) when an order is completed.

## Database Changes

Run the following SQL migration to add rating columns to the users table:

```sql
-- Run: database/add_user_rating.sql
ALTER TABLE users 
ADD COLUMN rating DECIMAL(3,1) DEFAULT NULL,
ADD COLUMN rating_count INT DEFAULT 0;

CREATE INDEX idx_rating ON users(rating);
```

## How It Works

1. **Rating Display**: When an order status is set to "Done", a rating section appears in the order details
2. **Rating Input**: Printer users can enter a rating (0-10) for the customer user
3. **Average Calculation**: The system calculates an average rating when multiple ratings are given
   - Formula: (old_rating × old_count + new_rating) / (old_count + 1)
4. **User Rating Display**: The user's current average rating is displayed next to their name in order headers (e.g., "⭐ 8.5/10")

## API Endpoints

- `GET /users.php?id={userId}` - Get user including rating
- `PUT /users.php?id={userId}` - Update user (can include rating field)

## Frontend Features

- Rating input section appears only for orders with status "Done"
- Rating preview shows stars and numeric value
- Save button is disabled until a valid rating (0-10) is entered
- User's current average rating is displayed in order headers

