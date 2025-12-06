# Printing App Backend Setup

## Database Configuration

The backend uses MySQL database with the following configuration:
- **Host**: YOUR_DB_HOST
- **Database**: printing
- **User**: YOUR_DB_USER
- **Password**: YOUR_PASSWORD

## Initial Setup

### 1. Install Dependencies

```bash
npm install
```

### 2. Initialize Database

Run the SQL schema to create tables:

```bash
npm run db:init
```

Or manually:
```bash
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -pYOUR_PASSWORD printing < database/schema.sql
```

### 3. Start Backend Server

```bash
npm run server
```

For development with auto-reload:
```bash
npm run server:dev
```

The server will run on port 3001 by default.

## API Endpoints

All API endpoints are prefixed with `/Apps/Printing/api`

### Users
- `GET /Apps/Printing/api/users` - Get all users
- `GET /Apps/Printing/api/users/:id` - Get user by ID
- `POST /Apps/Printing/api/users` - Create new user
- `PUT /Apps/Printing/api/users/:id` - Update user
- `DELETE /Apps/Printing/api/users/:id` - Delete user

### Colors
- `GET /Apps/Printing/api/colors` - Get all colors (optional `?userId=xxx`)
- `GET /Apps/Printing/api/colors/:id` - Get color by ID
- `POST /Apps/Printing/api/colors` - Create new color
- `PUT /Apps/Printing/api/colors/:id` - Update color
- `DELETE /Apps/Printing/api/colors/:id` - Delete color

### Orders
- `GET /Apps/Printing/api/orders` - Get all orders (optional `?userId=xxx&status=xxx`)
- `GET /Apps/Printing/api/orders/:id` - Get order by ID
- `POST /Apps/Printing/api/orders` - Create new order
- `PUT /Apps/Printing/api/orders/:id` - Update order
- `DELETE /Apps/Printing/api/orders/:id` - Delete order

### Health Check
- `GET /Apps/Printing/api/health` - Check server and database status

## Deployment

### Frontend Deployment

The frontend is configured to be deployed at `/Apps/Printing/` path.

1. Build the frontend:
```bash
npm run build
```

2. Deploy the `dist` folder to your web server at the `/Apps/Printing/` location.

### Backend Deployment

The backend server needs to be running and accessible. Options:

1. **PM2** (Recommended for production):
```bash
npm install -g pm2
pm2 start backend/server.js --name printing-app
pm2 save
pm2 startup
```

2. **Systemd Service** (Linux):
Create a service file at `/etc/systemd/system/printing-app.service`:

```ini
[Unit]
Description=Printing App Backend
After=network.target

[Service]
Type=simple
User=your-user
WorkingDirectory=/path/to/Printing-App
ExecStart=/usr/bin/node backend/server.js
Restart=always

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl enable printing-app
sudo systemctl start printing-app
```

3. **Reverse Proxy** (Nginx example):

```nginx
location /Apps/Printing/api {
    proxy_pass http://localhost:3001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

## Environment Variables

Create a `.env` file in the root directory:

```
PORT=3001
DB_HOST=your-database-host
DB_USER=your-database-user
DB_PASSWORD=your-database-password
DB_NAME=printing
API_BASE_PATH=/Apps/Printing/api
```

## Database Schema

The database includes the following tables:
- `users` - User accounts
- `colors` - Color/filament definitions
- `orders` - Printing orders
- `order_colors` - Junction table for order-color relationships

See `database/schema.sql` for full schema details.

## Troubleshooting

### Database Connection Issues
- Verify MySQL server is accessible from your server
- Check firewall rules allow connections to port 3306
- Verify credentials are correct

### API Not Responding
- Check if backend server is running: `curl http://localhost:3001/Apps/Printing/api/health`
- Check server logs for errors
- Verify database connection in logs

### Frontend Can't Connect to API
- Ensure backend is running and accessible
- Check CORS settings if accessing from different domain
- Verify API base path matches deployment location

