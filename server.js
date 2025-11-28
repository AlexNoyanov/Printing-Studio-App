// Simple Express server for text file storage
// Run with: node server.js
// This provides a backend API that stores data in text files

import express from 'express'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import cors from 'cors'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

const app = express()
const PORT = 3001

const DATA_DIR = path.join(__dirname, 'data')
const USERS_FILE = path.join(DATA_DIR, 'users.txt')
const ORDERS_FILE = path.join(DATA_DIR, 'orders.txt')

// Ensure data directory exists
if (!fs.existsSync(DATA_DIR)) {
  fs.mkdirSync(DATA_DIR, { recursive: true })
}

app.use(cors())
app.use(express.json())

// Helper functions to read/write text files
function readUsers() {
  try {
    if (fs.existsSync(USERS_FILE)) {
      const content = fs.readFileSync(USERS_FILE, 'utf-8')
      return content.trim() ? JSON.parse(content) : []
    }
  } catch (e) {
    console.error('Error reading users:', e)
  }
  return []
}

function saveUsers(users) {
  try {
    fs.writeFileSync(USERS_FILE, JSON.stringify(users, null, 2))
  } catch (e) {
    console.error('Error saving users:', e)
  }
}

function readOrders() {
  try {
    if (fs.existsSync(ORDERS_FILE)) {
      const content = fs.readFileSync(ORDERS_FILE, 'utf-8')
      return content.trim() ? JSON.parse(content) : []
    }
  } catch (e) {
    console.error('Error reading orders:', e)
  }
  return []
}

function saveOrders(orders) {
  try {
    fs.writeFileSync(ORDERS_FILE, JSON.stringify(orders, null, 2))
  } catch (e) {
    console.error('Error saving orders:', e)
  }
}

// API Routes
app.get('/api/users', (req, res) => {
  res.json(readUsers())
})

app.post('/api/users', (req, res) => {
  const users = readUsers()
  users.push(req.body)
  saveUsers(users)
  res.json({ success: true })
})

app.get('/api/orders', (req, res) => {
  res.json(readOrders())
})

app.post('/api/orders', (req, res) => {
  const orders = readOrders()
  orders.push(req.body)
  saveOrders(orders)
  res.json({ success: true })
})

app.put('/api/orders/:id', (req, res) => {
  const orders = readOrders()
  const index = orders.findIndex(o => o.id === req.params.id)
  if (index !== -1) {
    orders[index] = { ...orders[index], ...req.body }
    saveOrders(orders)
    res.json({ success: true })
  } else {
    res.status(404).json({ error: 'Order not found' })
  }
})

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`)
  console.log(`Users file: ${USERS_FILE}`)
  console.log(`Orders file: ${ORDERS_FILE}`)
})

