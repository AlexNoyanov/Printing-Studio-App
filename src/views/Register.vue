<template>
  <div class="auth-container">
    <div class="auth-card">
      <h1>Register</h1>
      <form @submit.prevent="handleRegister" class="auth-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input
            id="username"
            v-model="username"
            type="text"
            required
            placeholder="Enter your username"
          />
        </div>
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
            minlength="6"
          />
        </div>
        <div class="form-group">
          <label for="role">Account Type</label>
          <select id="role" v-model="role" required class="role-select">
            <option value="user">Regular User</option>
            <option value="printer">Printer Owner</option>
          </select>
        </div>
        <div class="form-group">
          <label for="language">Language</label>
          <select id="language" v-model="language" required class="role-select">
            <option value="en">English</option>
            <option value="ru">Russian</option>
            <option value="es">Spanish</option>
            <option value="fr">French</option>
            <option value="de">German</option>
            <option value="it">Italian</option>
            <option value="pt">Portuguese</option>
            <option value="zh">Chinese</option>
            <option value="ja">Japanese</option>
            <option value="ko">Korean</option>
          </select>
        </div>
        <div v-if="error" class="error-message">{{ error }}</div>
        <div v-if="success" class="success-message">{{ success }}</div>
        <button type="submit" class="submit-btn">Register</button>
        <p class="auth-link">
          Already have an account? <router-link to="/login">Login</router-link>
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
const username = ref('')
const email = ref('')
const password = ref('')
const role = ref('user')
const language = ref('en') // Default to English
const error = ref('')
const success = ref('')

const handleRegister = async () => {
  error.value = ''
  success.value = ''
  
  try {
    // Create new user - backend will check for duplicates
    const newUser = {
      id: Date.now().toString(),
      username: username.value,
      email: email.value,
      password: password.value,
      role: role.value,
      language: language.value || 'en' // Default to English if not set
    }
    
    await storage.createUser(newUser)
    
    success.value = 'Registration successful! Redirecting to login...'
    
    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (e) {
    // Handle specific error messages from backend
    if (e.message && e.message.includes('already exists')) {
      error.value = 'Email or username already registered'
    } else if (e.message && e.message.includes('409')) {
      error.value = 'Email or username already registered'
    } else {
      error.value = 'Registration failed: ' + (e.message || 'Please try again.')
    }
    console.error('Registration error:', e)
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

.form-group input,
.role-select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #3a3a3a;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
  background: #1a1a1a;
  color: #b8dce8;
}

.form-group input:focus,
.role-select:focus {
  outline: none;
  border-color: #87CEEB;
}

.form-group input::placeholder {
  color: #666;
}

.role-select {
  cursor: pointer;
}

.role-select {
  cursor: pointer;
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

.success-message {
  color: #51cf66;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: rgba(81, 207, 102, 0.1);
  border-radius: 5px;
  font-size: 0.9rem;
  border: 1px solid rgba(81, 207, 102, 0.3);
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

/* Responsive Design */
@media (max-width: 768px) {
  .auth-container {
    padding: 1rem;
  }

  .auth-card {
    padding: 2rem 1.5rem;
    max-width: 100%;
  }

  .auth-card h1 {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .auth-container {
    padding: 0.75rem;
  }

  .auth-card {
    padding: 1.5rem 1rem;
  }

  .auth-card h1 {
    font-size: 1.5rem;
  }
}
</style>

