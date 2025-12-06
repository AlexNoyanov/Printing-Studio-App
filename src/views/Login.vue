<template>
  <div class="auth-container">
    <div class="auth-card">
      <h1>Login</h1>
      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            placeholder="Enter your email"
          />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            placeholder="Enter your password"
          />
        </div>
        <div v-if="error" class="error-message">{{ error }}</div>
        <button type="submit" class="submit-btn">Login</button>
        <p class="auth-link">
          Don't have an account? <router-link to="/register">Register</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from '../utils/storage'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

const handleLogin = async () => {
  error.value = ''
  try {
    const users = await storage.getUsers()
    const user = users.find(u => u.email === email.value && u.password === password.value)
    
    if (user) {
      localStorage.setItem('currentUser', JSON.stringify({
        id: user.id,
        email: user.email,
        username: user.username,
        role: user.role
      }))
      
      if (user.role === 'printer') {
        router.push('/dashboard')
      } else {
        router.push('/orders')
      }
    } else {
      error.value = 'Invalid email or password'
    }
  } catch (e) {
    error.value = 'Failed to connect to server. Please try again.'
    console.error('Login error:', e)
  }
}
</script>

<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
}

.auth-card {
  background: #2a2a2a;
  border-radius: 10px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  width: 100%;
  max-width: 400px;
  border: 1px solid #3a3a3a;
}

.auth-card h1 {
  color: #87CEEB;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 2rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #a0d4e8;
  font-weight: 500;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
  background: #1a1a1a;
  color: #b8dce8;
}

.form-group input:focus {
  outline: none;
  border-color: #87CEEB;
}

.form-group input::placeholder {
  color: #666;
}

.error-message {
  color: #ff6b6b;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: rgba(255, 107, 107, 0.1);
  border-radius: 5px;
  font-size: 0.9rem;
  border: 1px solid rgba(255, 107, 107, 0.3);
}

.submit-btn {
  background: #87CEEB;
  color: #000;
  border: none;
  padding: 0.75rem;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
  margin-top: 0.5rem;
}

.submit-btn:hover {
  background: #6bb6d6;
}

.auth-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #999;
}

.auth-link a {
  color: #87CEEB;
  text-decoration: none;
  font-weight: 500;
}

.auth-link a:hover {
  text-decoration: underline;
}
</style>

