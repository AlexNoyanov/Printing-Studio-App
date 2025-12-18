// Storage utility using backend API
// API Base URL - adjust based on environment
// For Firebase deployment, use absolute URL to noyanov.com
// For server deployment, use relative path
const API_BASE = import.meta.env.VITE_API_BASE || (() => {
  // Always use production backend at noyanov.com
  if (window.location.hostname.includes('web.app') || window.location.hostname.includes('firebaseapp.com')) {
    return 'https://noyanov.com/Apps/Printing/api'
  }
  // For local development, also use production backend
  if (import.meta.env.DEV) {
    return 'https://noyanov.com/Apps/Printing/api'
  }
  return '/Apps/Printing/api'
})()

// Helper function for API calls
async function apiCall(endpoint, options = {}) {
  try {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      },
      ...options
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Request failed' }))
      throw new Error(error.error || `HTTP error! status: ${response.status}`)
    }

    return await response.json()
  } catch (error) {
    console.error(`API call failed for ${endpoint}:`, error)
    throw error
  }
}

export const storage = {
  // Users storage
  async getUsers() {
    try {
      const users = await apiCall('/users.php')
      // Clear localStorage cache when we successfully get from API
      localStorage.removeItem('users')
      return users
    } catch (e) {
      console.error('Error reading users from API:', e)
      // Don't fallback to localStorage - force API usage only
      throw new Error('Failed to connect to server. Please check your connection.')
    }
  },

  async saveUsers(users) {
    try {
      // Save all users (for bulk operations)
      // Note: This is less efficient, but maintains compatibility
      for (const user of users) {
        try {
          await apiCall(`/users/${user.id}`, {
            method: 'PUT',
            body: JSON.stringify(user)
          })
        } catch {
          // If user doesn't exist, create it
          await apiCall('/users.php', {
            method: 'POST',
            body: JSON.stringify(user)
          })
        }
      }
      // Also cache in localStorage as backup
      localStorage.setItem('users', JSON.stringify(users, null, 2))
      return { success: true }
    } catch (e) {
      console.error('Error saving users:', e)
      // Fallback to localStorage
      try {
        localStorage.setItem('users', JSON.stringify(users, null, 2))
      } catch {}
      throw e
    }
  },

  async createUser(user) {
    try {
      const result = await apiCall('/users.php', {
        method: 'POST',
        body: JSON.stringify(user)
      })
      return result
    } catch (e) {
      console.error('Error creating user:', e)
      throw e
    }
  },

  // Orders storage
  async getOrders(userId = null) {
    try {
      const endpoint = userId ? `/orders.php?userId=${userId}` : '/orders.php'
      return await apiCall(endpoint)
    } catch (e) {
      console.error('Error reading orders:', e)
      // Fallback to localStorage
      try {
        const ordersJson = localStorage.getItem('orders')
        if (ordersJson) {
          const orders = JSON.parse(ordersJson)
          return userId ? orders.filter(o => o.userId === userId) : orders
        }
      } catch {}
      return []
    }
  },

  async saveOrders(orders) {
    try {
      // For bulk save, we'd need to update each order
      // This is a simplified version - in practice, you might want to batch
      for (const order of orders) {
        try {
          await apiCall(`/orders.php?id=${order.id}`, {
            method: 'PUT',
            body: JSON.stringify(order)
          })
        } catch {
          // If order doesn't exist, create it
          await apiCall('/orders.php', {
            method: 'POST',
            body: JSON.stringify(order)
          })
        }
      }
      // Also cache in localStorage as backup
      localStorage.setItem('orders', JSON.stringify(orders, null, 2))
      return { success: true }
    } catch (e) {
      console.error('Error saving orders:', e)
      // Fallback to localStorage
      try {
        localStorage.setItem('orders', JSON.stringify(orders, null, 2))
      } catch {}
      throw e
    }
  },

  async createOrder(order) {
    try {
      const result = await apiCall('/orders.php', {
        method: 'POST',
        body: JSON.stringify(order)
      })
      return result
    } catch (e) {
      console.error('Error creating order:', e)
      throw e
    }
  },

  async updateOrder(orderId, updates) {
    try {
      const result = await apiCall(`/orders.php?id=${orderId}`, {
        method: 'PUT',
        body: JSON.stringify(updates)
      })
      return result
    } catch (e) {
      console.error('Error updating order:', e)
      throw e
    }
  },

  // Update printed status of an order link
  async updateLinkPrintedStatus(linkId, printed) {
    try {
      const result = await apiCall('/orders.php', {
        method: 'PATCH',
        body: JSON.stringify({ linkId, printed })
      })
      return result
    } catch (e) {
      console.error('Error updating link printed status:', e)
      throw e
    }
  },

  // Colors storage
  async getColors(userId = null) {
    try {
      const endpoint = userId ? `/colors.php?userId=${userId}` : '/colors.php'
      const colors = await apiCall(endpoint)
      // Map hex to value for compatibility
      return colors.map(c => ({
        ...c,
        hex: c.value || c.hex
      }))
    } catch (e) {
      console.error('Error reading colors:', e)
      // Fallback to localStorage
      try {
        const colorsJson = localStorage.getItem('colors')
        if (colorsJson) {
          const colors = JSON.parse(colorsJson)
          return userId ? colors.filter(c => c.userId === userId) : colors
        }
      } catch {}
      return []
    }
  },

  async saveColors(colors) {
    try {
      // Save all colors (for bulk operations)
      for (const color of colors) {
        try {
          await apiCall(`/colors.php?id=${color.id}`, {
            method: 'PUT',
            body: JSON.stringify({
              id: color.id,
              name: color.name,
              value: color.value || color.hex,
              filamentLink: color.filamentLink
            })
          })
        } catch {
          // If color doesn't exist, create it
          await apiCall('/colors.php', {
            method: 'POST',
            body: JSON.stringify({
              id: color.id,
              userId: color.userId,
              name: color.name,
              value: color.value || color.hex,
              filamentLink: color.filamentLink
            })
          })
        }
      }
      // Also cache in localStorage as backup
      localStorage.setItem('colors', JSON.stringify(colors, null, 2))
      return { success: true }
    } catch (e) {
      console.error('Error saving colors:', e)
      // Fallback to localStorage
      try {
        localStorage.setItem('colors', JSON.stringify(colors, null, 2))
      } catch {}
      throw e
    }
  },

  async createColor(color) {
    try {
      const result = await apiCall('/colors.php', {
        method: 'POST',
        body: JSON.stringify({
          id: color.id,
          userId: color.userId,
          name: color.name,
          value: color.value || color.hex,
          filamentLink: color.filamentLink
        })
      })
      return result
    } catch (e) {
      console.error('Error creating color:', e)
      throw e
    }
  },

  async updateColor(colorId, updates) {
    try {
      const result = await apiCall(`/colors.php?id=${colorId}`, {
        method: 'PUT',
        body: JSON.stringify({
          id: colorId,
          name: updates.name,
          value: updates.value || updates.hex,
          filamentLink: updates.filamentLink
        })
      })
      return result
    } catch (e) {
      console.error('Error updating color:', e)
      throw e
    }
  },

  async deleteColor(colorId) {
    try {
      const result = await apiCall(`/colors.php?id=${colorId}`, {
        method: 'DELETE'
      })
      return result
    } catch (e) {
      console.error('Error deleting color:', e)
      throw e
    }
  },

  // Export functions for text file format (kept for compatibility)
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

  exportColorsToText(colors) {
    const text = colors.map(c => 
      `${c.id}|${c.userId}|${c.name}|${c.value || c.hex}|${c.filamentLink || ''}`
    ).join('\n')
    localStorage.setItem('colors_text', text)
  },

  // Materials storage
  async getMaterials(userId = null) {
    try {
      const endpoint = userId ? `/materials.php?userId=${userId}` : '/materials.php'
      return await apiCall(endpoint)
    } catch (e) {
      console.error('Error reading materials:', e)
      return []
    }
  },

  async createMaterial(material) {
    try {
      const result = await apiCall('/materials.php', {
        method: 'POST',
        body: JSON.stringify({
          id: material.id,
          userId: material.userId,
          name: material.name,
          color: material.color,
          materialType: material.materialType,
          shopLink: material.shopLink
        })
      })
      return result
    } catch (e) {
      console.error('Error creating material:', e)
      throw e
    }
  },

  async updateMaterial(materialId, updates) {
    try {
      const result = await apiCall(`/materials.php?id=${materialId}`, {
        method: 'PUT',
        body: JSON.stringify(updates)
      })
      return result
    } catch (e) {
      console.error('Error updating material:', e)
      throw e
    }
  },

  async deleteMaterial(materialId) {
    try {
      const result = await apiCall(`/materials.php?id=${materialId}`, {
        method: 'DELETE'
      })
      return result
    } catch (e) {
      console.error('Error deleting material:', e)
      throw e
    }
  },

  // Migrate colors to filaments (migrates all users if userId is null)
  async migrateColorsToFilaments(userId = null) {
    try {
      const result = await apiCall('/migrate_colors.php', {
        method: 'POST',
        body: JSON.stringify(userId ? { userId } : {})
      })
      return result
    } catch (e) {
      console.error('Error migrating colors to filaments:', e)
      throw e
    }
  },

  // Material Types management
  async getMaterialTypes() {
    try {
      return await apiCall('/material_types.php')
    } catch (e) {
      console.error('Error reading material types:', e)
      return []
    }
  },

  async createMaterialType(name) {
    try {
      const result = await apiCall('/material_types.php', {
        method: 'POST',
        body: JSON.stringify({ name })
      })
      return result
    } catch (e) {
      console.error('Error creating material type:', e)
      throw e
    }
  },

  async deleteMaterialType(typeId) {
    try {
      const result = await apiCall(`/material_types.php?id=${typeId}`, {
        method: 'DELETE'
      })
      return result
    } catch (e) {
      console.error('Error deleting material type:', e)
      throw e
    }
  },

  // User Filaments (client-owned filament spools)
  async getUserFilaments(userId) {
    try {
      console.log('Calling getUserFilaments for userId:', userId)
      const result = await apiCall(`/user_filaments.php?userId=${userId}`)
      console.log('getUserFilaments result:', result)
      return result
    } catch (e) {
      console.error('Error reading user filaments:', e)
      console.error('Error details:', e.message)
      return []
    }
  },

  async assignFilamentToUser(userId, materialId, quantity = 1) {
    try {
      const result = await apiCall('/user_filaments.php', {
        method: 'POST',
        body: JSON.stringify({
          userId,
          materialId,
          quantity
        })
      })
      return result
    } catch (e) {
      console.error('Error assigning filament to user:', e)
      throw e
    }
  },

  async updateUserFilamentQuantity(userFilamentId, quantity) {
    try {
      const result = await apiCall(`/user_filaments.php?id=${userFilamentId}`, {
        method: 'PUT',
        body: JSON.stringify({ quantity })
      })
      return result
    } catch (e) {
      console.error('Error updating user filament quantity:', e)
      throw e
    }
  },

  async removeUserFilament(userFilamentId) {
    try {
      const result = await apiCall(`/user_filaments.php?id=${userFilamentId}`, {
        method: 'DELETE'
      })
      return result
    } catch (e) {
      console.error('Error removing user filament:', e)
      throw e
    }
  },

  // Shop - Get all printed models from makerworld.com
  async getShopModels() {
    try {
      return await apiCall('/shop.php')
    } catch (e) {
      console.error('Error loading shop models:', e)
      return []
    }
  },

  // Shop - Fetch model data from makerworld.com
  async fetchMakerWorldData(url) {
    try {
      const result = await apiCall('/shop.php', {
        method: 'POST',
        body: JSON.stringify({ url })
      })
      return result
    } catch (e) {
      console.error('Error fetching makerworld data:', e)
      throw e
    }
  }
}

