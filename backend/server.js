// Backend server for Printing App
// Deployed at: noyanov.com/Apps/Printing/
// MySQL Database: printing (configure via environment variables)

import express from 'express';
import cors from 'cors';
import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Load environment variables
dotenv.config({ path: join(__dirname, '../.env') });

const app = express();
const PORT = process.env.PORT || 3001;

// Database configuration
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASS || '',
  database: 'printing',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  charset: 'utf8mb4'
};

// Create connection pool
const pool = mysql.createPool(dbConfig);

// Middleware
app.use(cors());
app.use(express.json());

// Base path for API
const API_BASE = '/Apps/Printing/api';

// Test database connection
pool.getConnection()
  .then(connection => {
    console.log('✅ Database connected successfully');
    connection.release();
  })
  .catch(err => {
    console.error('❌ Database connection failed:', err.message);
  });

// ==================== USERS API ====================

// Get all users
app.get(`${API_BASE}/users`, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, username, email, password, role, created_at, updated_at FROM users ORDER BY created_at DESC'
    );
    res.json(rows.map(row => ({
      id: row.id,
      username: row.username,
      email: row.email,
      password: row.password,
      role: row.role,
      createdAt: row.created_at,
      updatedAt: row.updated_at
    })));
  } catch (error) {
    console.error('Error fetching users:', error);
    res.status(500).json({ error: 'Failed to fetch users' });
  }
});

// Get user by ID
app.get(`${API_BASE}/users/:id`, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, username, email, password, role, created_at, updated_at FROM users WHERE id = ?',
      [req.params.id]
    );
    if (rows.length === 0) {
      return res.status(404).json({ error: 'User not found' });
    }
    const row = rows[0];
    res.json({
      id: row.id,
      username: row.username,
      email: row.email,
      password: row.password,
      role: row.role,
      createdAt: row.created_at,
      updatedAt: row.updated_at
    });
  } catch (error) {
    console.error('Error fetching user:', error);
    res.status(500).json({ error: 'Failed to fetch user' });
  }
});

// Create new user
app.post(`${API_BASE}/users`, async (req, res) => {
  try {
    const { id, username, email, password, role } = req.body;
    
    if (!id || !username || !email || !password) {
      return res.status(400).json({ error: 'Missing required fields' });
    }

    await pool.execute(
      'INSERT INTO users (id, username, email, password, role) VALUES (?, ?, ?, ?, ?)',
      [id, username, email, password, role || 'user']
    );
    
    res.json({ success: true, id });
  } catch (error) {
    if (error.code === 'ER_DUP_ENTRY') {
      return res.status(409).json({ error: 'Username or email already exists' });
    }
    console.error('Error creating user:', error);
    res.status(500).json({ error: 'Failed to create user' });
  }
});

// Update user
app.put(`${API_BASE}/users/:id`, async (req, res) => {
  try {
    const { username, email, password, role } = req.body;
    const updates = [];
    const values = [];

    if (username) {
      updates.push('username = ?');
      values.push(username);
    }
    if (email) {
      updates.push('email = ?');
      values.push(email);
    }
    if (password) {
      updates.push('password = ?');
      values.push(password);
    }
    if (role) {
      updates.push('role = ?');
      values.push(role);
    }

    if (updates.length === 0) {
      return res.status(400).json({ error: 'No fields to update' });
    }

    values.push(req.params.id);

    await pool.execute(
      `UPDATE users SET ${updates.join(', ')} WHERE id = ?`,
      values
    );

    res.json({ success: true });
  } catch (error) {
    console.error('Error updating user:', error);
    res.status(500).json({ error: 'Failed to update user' });
  }
});

// Delete user
app.delete(`${API_BASE}/users/:id`, async (req, res) => {
  try {
    const [result] = await pool.execute('DELETE FROM users WHERE id = ?', [req.params.id]);
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'User not found' });
    }
    res.json({ success: true });
  } catch (error) {
    console.error('Error deleting user:', error);
    res.status(500).json({ error: 'Failed to delete user' });
  }
});

// ==================== COLORS API ====================

// Get all colors (optionally filtered by user_id)
app.get(`${API_BASE}/colors`, async (req, res) => {
  try {
    let query = 'SELECT id, user_id, name, value, filament_link, created_at, updated_at FROM colors';
    let params = [];

    if (req.query.userId) {
      query += ' WHERE user_id = ?';
      params.push(req.query.userId);
    }

    query += ' ORDER BY created_at DESC';

    const [rows] = await pool.execute(query, params);
    res.json(rows.map(row => ({
      id: row.id,
      userId: row.user_id,
      name: row.name,
      value: row.value,
      hex: row.value,
      filamentLink: row.filament_link,
      createdAt: row.created_at,
      updatedAt: row.updated_at
    })));
  } catch (error) {
    console.error('Error fetching colors:', error);
    res.status(500).json({ error: 'Failed to fetch colors' });
  }
});

// Get color by ID
app.get(`${API_BASE}/colors/:id`, async (req, res) => {
  try {
    const [rows] = await pool.execute(
      'SELECT id, user_id, name, value, filament_link, created_at, updated_at FROM colors WHERE id = ?',
      [req.params.id]
    );
    if (rows.length === 0) {
      return res.status(404).json({ error: 'Color not found' });
    }
    const row = rows[0];
    res.json({
      id: row.id,
      userId: row.user_id,
      name: row.name,
      value: row.value,
      hex: row.value,
      filamentLink: row.filament_link,
      createdAt: row.created_at,
      updatedAt: row.updated_at
    });
  } catch (error) {
    console.error('Error fetching color:', error);
    res.status(500).json({ error: 'Failed to fetch color' });
  }
});

// Create new color
app.post(`${API_BASE}/colors`, async (req, res) => {
  try {
    const { id, userId, name, value, filamentLink } = req.body;
    
    if (!id || !userId || !name || !value) {
      return res.status(400).json({ error: 'Missing required fields' });
    }

    await pool.execute(
      'INSERT INTO colors (id, user_id, name, value, filament_link) VALUES (?, ?, ?, ?, ?)',
      [id, userId, name, value, filamentLink || null]
    );
    
    res.json({ success: true, id });
  } catch (error) {
    console.error('Error creating color:', error);
    res.status(500).json({ error: 'Failed to create color' });
  }
});

// Update color
app.put(`${API_BASE}/colors/:id`, async (req, res) => {
  try {
    const { name, value, filamentLink } = req.body;
    const updates = [];
    const values = [];

    if (name) {
      updates.push('name = ?');
      values.push(name);
    }
    if (value) {
      updates.push('value = ?');
      values.push(value);
    }
    if (filamentLink !== undefined) {
      updates.push('filament_link = ?');
      values.push(filamentLink || null);
    }

    if (updates.length === 0) {
      return res.status(400).json({ error: 'No fields to update' });
    }

    values.push(req.params.id);

    const [result] = await pool.execute(
      `UPDATE colors SET ${updates.join(', ')} WHERE id = ?`,
      values
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Color not found' });
    }

    res.json({ success: true });
  } catch (error) {
    console.error('Error updating color:', error);
    res.status(500).json({ error: 'Failed to update color' });
  }
});

// Delete color
app.delete(`${API_BASE}/colors/:id`, async (req, res) => {
  try {
    const [result] = await pool.execute('DELETE FROM colors WHERE id = ?', [req.params.id]);
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Color not found' });
    }
    res.json({ success: true });
  } catch (error) {
    console.error('Error deleting color:', error);
    res.status(500).json({ error: 'Failed to delete color' });
  }
});

// ==================== ORDERS API ====================

// Get all orders (optionally filtered by user_id or status)
app.get(`${API_BASE}/orders`, async (req, res) => {
  try {
    let query = `
      SELECT o.id, o.user_id, o.user_name, o.model_link, o.comment, o.status, 
             o.created_at, o.updated_at,
             GROUP_CONCAT(oc.color_name ORDER BY oc.color_name SEPARATOR ',') as colors
      FROM orders o
      LEFT JOIN order_colors oc ON o.id = oc.order_id
    `;
    const conditions = [];
    const params = [];

    if (req.query.userId) {
      conditions.push('o.user_id = ?');
      params.push(req.query.userId);
    }
    if (req.query.status) {
      conditions.push('o.status = ?');
      params.push(req.query.status);
    }

    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }

    query += ' GROUP BY o.id ORDER BY o.created_at DESC';

    const [rows] = await pool.execute(query, params);
    res.json(rows.map(row => ({
      id: row.id,
      userId: row.user_id,
      userName: row.user_name,
      modelLink: row.model_link,
      comment: row.comment,
      status: row.status,
      colors: row.colors ? row.colors.split(',') : [],
      createdAt: row.created_at,
      updatedAt: row.updated_at
    })));
  } catch (error) {
    console.error('Error fetching orders:', error);
    res.status(500).json({ error: 'Failed to fetch orders' });
  }
});

// Get order by ID
app.get(`${API_BASE}/orders/:id`, async (req, res) => {
  try {
    const [orderRows] = await pool.execute(
      'SELECT id, user_id, user_name, model_link, comment, status, created_at, updated_at FROM orders WHERE id = ?',
      [req.params.id]
    );
    
    if (orderRows.length === 0) {
      return res.status(404).json({ error: 'Order not found' });
    }

    const [colorRows] = await pool.execute(
      'SELECT color_name FROM order_colors WHERE order_id = ? ORDER BY color_name',
      [req.params.id]
    );

    const order = orderRows[0];
    res.json({
      id: order.id,
      userId: order.user_id,
      userName: order.user_name,
      modelLink: order.model_link,
      comment: order.comment,
      status: order.status,
      colors: colorRows.map(row => row.color_name),
      createdAt: order.created_at,
      updatedAt: order.updated_at
    });
  } catch (error) {
    console.error('Error fetching order:', error);
    res.status(500).json({ error: 'Failed to fetch order' });
  }
});

// Create new order
app.post(`${API_BASE}/orders`, async (req, res) => {
  try {
    const { id, userId, userName, modelLink, colors, comment, status } = req.body;
    
    if (!id || !userId || !userName || !modelLink || !colors || !Array.isArray(colors) || colors.length === 0) {
      return res.status(400).json({ error: 'Missing required fields' });
    }

    // Start transaction
    const connection = await pool.getConnection();
    await connection.beginTransaction();

    try {
      // Insert order
      await connection.execute(
        'INSERT INTO orders (id, user_id, user_name, model_link, comment, status) VALUES (?, ?, ?, ?, ?, ?)',
        [id, userId, userName, modelLink, comment || null, status || 'Created']
      );

      // Insert order colors
      // First, get color IDs for the color names
      const colorNames = colors;
      for (const colorName of colorNames) {
        // Try to find color by name for this user, or use a placeholder
        const [colorRows] = await connection.execute(
          'SELECT id FROM colors WHERE name = ? AND user_id = ? LIMIT 1',
          [colorName, userId]
        );
        
        const colorId = colorRows.length > 0 ? colorRows[0].id : null;
        
        await connection.execute(
          'INSERT INTO order_colors (order_id, color_id, color_name) VALUES (?, ?, ?)',
          [id, colorId, colorName]
        );
      }

      await connection.commit();
      res.json({ success: true, id });
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  } catch (error) {
    console.error('Error creating order:', error);
    res.status(500).json({ error: 'Failed to create order' });
  }
});

// Update order
app.put(`${API_BASE}/orders/:id`, async (req, res) => {
  try {
    const { modelLink, colors, comment, status } = req.body;
    const connection = await pool.getConnection();
    await connection.beginTransaction();

    try {
      // Update order fields
      const updates = [];
      const values = [];

      if (modelLink) {
        updates.push('model_link = ?');
        values.push(modelLink);
      }
      if (comment !== undefined) {
        updates.push('comment = ?');
        values.push(comment || null);
      }
      if (status) {
        updates.push('status = ?');
        values.push(status);
      }

      if (updates.length > 0) {
        values.push(req.params.id);
        const [result] = await connection.execute(
          `UPDATE orders SET ${updates.join(', ')} WHERE id = ?`,
          values
        );

        if (result.affectedRows === 0) {
          await connection.rollback();
          return res.status(404).json({ error: 'Order not found' });
        }
      }

      // Update colors if provided
      if (colors && Array.isArray(colors)) {
        // Delete existing order colors
        await connection.execute('DELETE FROM order_colors WHERE order_id = ?', [req.params.id]);

        // Insert new order colors
        for (const colorName of colors) {
          await connection.execute(
            'INSERT INTO order_colors (order_id, color_id, color_name) VALUES (?, ?, ?)',
            [req.params.id, null, colorName]
          );
        }
      }

      await connection.commit();
      res.json({ success: true });
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  } catch (error) {
    console.error('Error updating order:', error);
    res.status(500).json({ error: 'Failed to update order' });
  }
});

// Delete order
app.delete(`${API_BASE}/orders/:id`, async (req, res) => {
  try {
    const [result] = await pool.execute('DELETE FROM orders WHERE id = ?', [req.params.id]);
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Order not found' });
    }
    res.json({ success: true });
  } catch (error) {
    console.error('Error deleting order:', error);
    res.status(500).json({ error: 'Failed to delete order' });
  }
});

// Health check endpoint
app.get(`${API_BASE}/health`, async (req, res) => {
  try {
    await pool.execute('SELECT 1');
    res.json({ status: 'ok', database: 'connected' });
  } catch (error) {
    res.status(500).json({ status: 'error', database: 'disconnected', error: error.message });
  }
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
  console.log(`📡 API base path: ${API_BASE}`);
  console.log(`💾 Database: printing on ${process.env.DB_HOST || 'localhost'}`);
});

