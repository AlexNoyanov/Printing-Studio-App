# 3D Printing Studio App

A full-stack Vue.js application for managing 3D printing orders with separate interfaces for customers and printer owners. The app allows customers to place orders with specific color/material requirements, and printer owners to manage and track order progress through a complete workflow.

## 🚀 Features

### Customer Features
- **User Authentication**: Secure login and registration system
- **Create Orders**: Submit printing orders with:
  - Model link (URL to 3D model file)
  - Color/material selection from available options
  - Optional comments and special requirements
- **View Orders**: Track all your orders with real-time status updates

### Printer Owner Features
- **Dashboard**: View all orders from all customers
- **Status Management**: Update order status through the complete workflow:
  - Created → Reviewed → Printing → Printed → Delivery → Done
- **Color Management**: Add and manage available printing colors/materials
- **Filament Management**: Detailed filament information for each color
- **Filtering**: Filter orders by user or status
- **Statistics**: View total orders and pending orders count

## 🛠️ Tech Stack

### Frontend
- **Vue 3** (Composition API)
- **Vue Router 4** for navigation
- **Vite** for build tooling
- Modern CSS with gradients and animations

### Backend
- **PHP** with MySQL database
- RESTful API architecture
- CORS support for cross-origin requests

### Database
- **MySQL** with InnoDB engine
- Normalized schema with foreign key constraints

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- **Node.js** (v16 or higher)
- **npm** or **yarn**
- **PHP** (v7.4 or higher) with PDO MySQL extension
- **MySQL** (v5.7 or higher) or **MariaDB**
- **Web Server** (Apache or Nginx) - for production deployment

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/AlexNoyanov/Printing-Studio-App.git
cd Printing-Studio-App
```

### 2. Install Frontend Dependencies

```bash
npm install
```

### 3. Set Up Backend Configuration

1. Copy the example configuration file:
```bash
cp backend/config.example.php backend/config.php
```

2. Edit `backend/config.php` and fill in your database credentials:
```php
define('DB_HOST', 'your-database-host');
define('DB_USER', 'your-database-user');
define('DB_PASS', 'your-database-password');
define('DB_NAME', 'your-database-name');
```

### 4. Set Up Database

1. Create a MySQL database:
```sql
CREATE DATABASE printing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -h YOUR_HOST -u YOUR_USER -pYOUR_PASSWORD YOUR_DATABASE < database/schema.sql
```

Or manually run the SQL file in your MySQL client:
```bash
mysql -u root -p printing < database/schema.sql
```

### 5. Configure Web Server

#### For Apache (using `.htaccess`)

The project includes `.htaccess` files for Apache configuration. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### For Nginx

See `nginx.conf` for Nginx configuration example. You'll need to:
1. Add the location block to your Nginx server configuration
2. Ensure PHP-FPM is configured correctly
3. Restart Nginx

## 🚀 Running the Application

### Development Mode

1. **Start the frontend development server:**
```bash
npm run dev
```

The app will be available at `http://localhost:5173` (or the port Vite assigns)

2. **Set up the backend:**

For local development, you can either:

**Option A: Use PHP built-in server** (for testing only):
```bash
cd backend
php -S localhost:8000
```

**Option B: Use Apache/Nginx** (recommended):
- Configure your web server to serve the `backend` directory
- Access API at `http://localhost/Apps/Printing/api`

### Production Build

1. **Build the frontend:**
```bash
npm run build
```

2. **Deploy:**
   - Copy the `dist` folder contents to your web server
   - Ensure the `backend` directory is accessible
   - Configure your web server to route API requests correctly

### Firebase Deployment

For Firebase Hosting deployment:

```bash
npm run build:firebase
firebase deploy --only hosting
```

## 📖 Usage Guide

### First Time Setup

1. **Register an Account:**
   - Navigate to the registration page
   - Fill in username, email, and password
   - Select account type:
     - **User**: For customers placing orders
     - **Printer**: For printer owners managing orders

2. **Login:**
   - Use your credentials to log in
   - You'll be redirected based on your account type

### As a Customer (User)

1. **Create an Order:**
   - Click "Create Order" in the navigation
   - Enter the model link (URL to your 3D model file)
   - Select one or more colors from available materials
   - Add comments or special requirements
   - Submit the order

2. **View Your Orders:**
   - Click "My Orders" to see all your orders
   - Track order status in real-time
   - Orders are automatically sorted by creation date

### As a Printer Owner

1. **Manage Orders:**
   - Access the Dashboard to see all orders
   - Use filters to find specific orders by user or status
   - Update order status using the dropdown on each order card
   - Track progress through: Created → Reviewed → Printing → Printed → Delivery → Done

2. **Manage Colors:**
   - Click "Colors" in the navigation
   - Add new colors/materials with names and hex values
   - Add filament information for each color
   - Edit or delete existing colors

## 🔐 Security Notes

- **Never commit `backend/config.php`** - it contains sensitive database credentials
- The `.gitignore` file is configured to exclude sensitive files
- Always use environment variables or secure configuration files in production
- Ensure your database user has only necessary permissions
- Use HTTPS in production environments

## 📁 Project Structure

```
Printing-Studio-App/
├── backend/
│   ├── api/              # API endpoints
│   │   ├── users.php     # User management
│   │   ├── orders.php    # Order management
│   │   ├── colors.php    # Color/material management
│   │   └── health.php    # Health check endpoint
│   ├── config.php        # Database configuration (not in git)
│   ├── config.example.php # Example configuration
│   ├── cors.php          # CORS configuration
│   └── index.php         # API router
├── database/
│   └── schema.sql        # Database schema
├── src/
│   ├── views/           # Vue components
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   ├── CreateOrder.vue
│   │   ├── MyOrders.vue
│   │   ├── Dashboard.vue
│   │   ├── Colors.vue
│   │   └── Filaments.vue
│   ├── router/          # Vue Router configuration
│   ├── utils/           # Utility functions
│   │   └── storage.js   # API communication
│   ├── App.vue          # Main app component
│   └── main.js         # App entry point
├── .gitignore          # Git ignore rules
├── package.json        # NPM dependencies
├── vite.config.js      # Vite configuration
└── README.md           # This file
```

## 🔄 Order Status Workflow

1. **Created**: Order has been submitted by the customer
2. **Reviewed**: Order has been reviewed by the printer owner
3. **Printing**: Order is currently being printed
4. **Printed**: Printing is complete
5. **Delivery**: Order is ready for delivery
6. **Done**: Order is completed and delivered

## 🌐 API Endpoints

The backend provides RESTful API endpoints:

- `GET /api/users` - Get all users
- `POST /api/users` - Create new user
- `GET /api/orders` - Get all orders (or filtered by userId)
- `POST /api/orders` - Create new order
- `PUT /api/orders/:id` - Update order
- `DELETE /api/orders/:id` - Delete order
- `GET /api/colors` - Get all colors
- `POST /api/colors` - Create new color
- `PUT /api/colors/:id` - Update color
- `DELETE /api/colors/:id` - Delete color
- `GET /api/health` - Health check

## 🐛 Troubleshooting

### Database Connection Issues

- Verify database credentials in `backend/config.php`
- Ensure MySQL server is running
- Check that the database user has proper permissions
- Verify network connectivity to the database server

### CORS Errors

- Ensure CORS headers are set in `backend/cors.php`
- Check web server configuration (Apache `.htaccess` or Nginx config)
- Verify API base URL in `src/utils/storage.js`

### Build Errors

- Clear `node_modules` and reinstall: `rm -rf node_modules && npm install`
- Check Node.js version compatibility
- Verify all dependencies are installed

## 📝 License

This project is licensed under the GPL-3.0 License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Contact

For questions or support, please open an issue on GitHub.

---

**Note**: This application is designed for managing 3D printing orders. Make sure to configure your database and web server properly before deploying to production.
