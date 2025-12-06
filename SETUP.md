# Quick Setup Guide

This guide will help you get the Printing Studio App running on your local environment.

## Prerequisites Checklist

- [ ] Node.js (v16+) installed
- [ ] PHP (v7.4+) installed with PDO MySQL extension
- [ ] MySQL/MariaDB installed and running
- [ ] Web server (Apache/Nginx) or PHP built-in server

## Step-by-Step Setup

### 1. Install Dependencies

```bash
npm install
```

### 2. Database Setup

1. **Create the database:**
```sql
CREATE DATABASE printing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Import the schema:**
```bash
mysql -u root -p printing < database/schema.sql
```

Or use your MySQL client to run the `database/schema.sql` file.

### 3. Backend Configuration

1. **Copy the example config:**
```bash
cp backend/config.example.php backend/config.php
```

2. **Edit `backend/config.php`** with your database credentials:
```php
define('DB_HOST', 'localhost');  // or your MySQL host
define('DB_USER', 'root');        // your MySQL user
define('DB_PASS', 'yourpassword'); // your MySQL password
define('DB_NAME', 'printing');    // database name
```

### 4. Start Development Server

**Frontend:**
```bash
npm run dev
```
Access at: `http://localhost:5173`

**Backend (PHP built-in server for testing):**
```bash
cd backend
php -S localhost:8000
```

**Or use Apache/Nginx** (recommended for production-like testing)

### 5. Configure API Base URL

If your backend is on a different port, update `src/utils/storage.js`:

```javascript
const API_BASE = 'http://localhost:8000/api'  // Adjust to your backend URL
```

## Testing the Setup

1. **Test Database Connection:**
   - Create a test file or use the health endpoint
   - Visit: `http://localhost:8000/api/health` (adjust port as needed)

2. **Test Frontend:**
   - Open `http://localhost:5173`
   - Try registering a new user
   - Login and create an order

## Common Issues

### "Database connection failed"
- Check your credentials in `backend/config.php`
- Ensure MySQL is running
- Verify database exists

### "CORS error"
- Ensure CORS headers are set in `backend/cors.php`
- Check API base URL in frontend code

### "Cannot find module"
- Run `npm install` again
- Delete `node_modules` and `package-lock.json`, then reinstall

## Next Steps

- Read the full [README.md](README.md) for detailed documentation
- Check [DEPLOYMENT.md](DEPLOYMENT.md) for production deployment guide
- Review API endpoints in the README

