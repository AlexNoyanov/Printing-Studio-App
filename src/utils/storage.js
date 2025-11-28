// Storage utility using localStorage
// Note: For production, this should be replaced with a backend API that writes to text files

export const storage = {
  // Users storage
  getUsers() {
    try {
      const usersJson = localStorage.getItem('users')
      if (usersJson) {
        return JSON.parse(usersJson)
      }
    } catch (e) {
      console.error('Error reading users:', e)
    }
    return []
  },

  saveUsers(users) {
    try {
      localStorage.setItem('users', JSON.stringify(users, null, 2))
      // Also save to a downloadable format for text file export
      this.exportUsersToText(users)
    } catch (e) {
      console.error('Error saving users:', e)
    }
  },

  // Orders storage
  getOrders() {
    try {
      const ordersJson = localStorage.getItem('orders')
      if (ordersJson) {
        return JSON.parse(ordersJson)
      }
    } catch (e) {
      console.error('Error reading orders:', e)
    }
    return []
  },

  saveOrders(orders) {
    try {
      localStorage.setItem('orders', JSON.stringify(orders, null, 2))
      // Also save to a downloadable format for text file export
      this.exportOrdersToText(orders)
    } catch (e) {
      console.error('Error saving orders:', e)
    }
  },

  // Export functions for text file format
  exportUsersToText(users) {
    const text = users.map(u => 
      `${u.username}|${u.email}|${u.password}|${u.role}`
    ).join('\n')
    localStorage.setItem('users_text', text)
  },

  exportOrdersToText(orders) {
    const text = orders.map(o => 
      `${o.id}|${o.userId}|${o.userName}|${o.modelLink}|${o.colors.join(',')}|${o.comment}|${o.status}|${o.createdAt}`
    ).join('\n')
    localStorage.setItem('orders_text', text)
  },

  // Colors storage
  getColors() {
    try {
      const colorsJson = localStorage.getItem('colors')
      if (colorsJson) {
        return JSON.parse(colorsJson)
      }
    } catch (e) {
      console.error('Error reading colors:', e)
    }
    return []
  },

  saveColors(colors) {
    try {
      localStorage.setItem('colors', JSON.stringify(colors, null, 2))
      this.exportColorsToText(colors)
    } catch (e) {
      console.error('Error saving colors:', e)
    }
  },

  exportColorsToText(colors) {
    const text = colors.map(c => 
      `${c.id}|${c.userId}|${c.name}|${c.value}`
    ).join('\n')
    localStorage.setItem('colors_text', text)
  }
}

