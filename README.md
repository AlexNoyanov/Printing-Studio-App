# 3D Printing Order Management App

A Vue.js application for managing 3D printing orders with separate interfaces for users and printer owners.

## Features

### User Features
- **Authentication**: Login and registration system
- **Create Orders**: Submit printing orders with:
  - Model link (URL to 3D model file)
  - Color selection from available materials
  - Optional comments
- **View Orders**: See all your orders with current status

### Printer Owner Features
- **Dashboard**: View all orders from all users
- **Status Management**: Update order status through the workflow:
  - Created → Reviewed → Printing → Printed → Delivery → Done
- **Filtering**: Filter orders by user or status
- **Statistics**: View total orders and pending orders count

## Installation

1. Install dependencies:
```bash
npm install
```

2. Start the development server:
```bash
npm run dev
```

3. Open your browser and navigate to `http://localhost:3000`

## Usage

### Registering a New Account

1. Click "Register" on the login page
2. Fill in your username, email, and password
3. Select your account type:
   - **Regular User**: For customers who want to place orders
   - **Printer Owner**: For managing and processing orders
4. Click "Register" and you'll be redirected to login

### As a Regular User

1. Login with your credentials
2. Click "Create Order" to submit a new printing request
3. Fill in:
   - Model link (URL to your 3D model)
   - Select one or more colors from available materials
   - Add any comments or special requirements
4. View your orders on the "My Orders" page

### As a Printer Owner

1. Login with your printer owner account
2. Access the Dashboard to see all orders
3. Use filters to find specific orders by user or status
4. Update order status using the dropdown menu on each order card
5. Track order progress through the complete workflow

## Data Storage

The app currently uses browser localStorage to store data. However, an optional backend server is included for text file storage.

### Option 1: Browser Storage (Default)
- Data is stored in browser localStorage
- Works immediately without any backend setup
- Data persists in the browser

### Option 2: Text File Storage (Optional Backend)
To use actual text file storage:

1. Install dependencies (if not already done):
```bash
npm install
```

2. Start the backend server:
```bash
npm run server
```

3. Update `src/utils/storage.js` to use API calls instead of localStorage (modify the functions to make HTTP requests to `http://localhost:3001/api/...`)

The server will create `data/users.txt` and `data/orders.txt` files in the project root to store all data.

## Order Status Workflow

1. **Created**: Order has been submitted by the user
2. **Reviewed**: Order has been reviewed by the printer owner
3. **Printing**: Order is currently being printed
4. **Printed**: Printing is complete
5. **Delivery**: Order is ready for delivery
6. **Done**: Order is completed and delivered

## Available Material Colors

- Red
- Blue
- Green
- Yellow
- Black
- White
- Orange
- Purple
- Pink
- Gray

## Project Structure

```
src/
├── views/
│   ├── Login.vue          # Login page
│   ├── Register.vue        # Registration page
│   ├── CreateOrder.vue     # Order creation form
│   ├── MyOrders.vue        # User's order list
│   └── Dashboard.vue       # Printer owner dashboard
├── router/
│   └── index.js            # Vue Router configuration
├── utils/
│   └── storage.js          # Storage utility functions
├── App.vue                 # Main app component
└── main.js                 # App entry point
```

## Build for Production

```bash
npm run build
```

The built files will be in the `dist` directory.

## Technologies Used

- Vue 3 (Composition API)
- Vue Router 4
- Vite
- Modern CSS with gradients and animations

