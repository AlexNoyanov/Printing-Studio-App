<template>
  <div class="client-login-container">
    <div class="background-decoration">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
    </div>
    
    <div class="login-content">
      <div class="login-card">
        <div class="card-header">
          <h2>Вход в систему</h2>
          <p>Войдите, чтобы создать заказ</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label for="email">
              <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              Имя пользователя или Email
            </label>
            <input
              id="email"
              v-model="email"
              type="text"
              required
              placeholder="Введите имя пользователя или email"
              class="modern-input"
            />
          </div>

          <div class="form-group">
            <label for="password">
              <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              Пароль
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              placeholder="Введите пароль"
              class="modern-input"
            />
          </div>

          <div v-if="error" class="error-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            {{ error }}
          </div>

          <button type="submit" class="submit-button" :disabled="isLoading">
            <span v-if="!isLoading">Войти</span>
            <span v-else class="loading-spinner"></span>
          </button>
        </form>

        <div class="card-footer">
          <p>Нет аккаунта? <router-link to="/register" class="link">Зарегистрироваться</router-link></p>
        </div>
      </div>

      <div class="features-preview">
        <div class="feature-item">
          <div class="feature-icon">🚀</div>
          <p>Быстрая печать</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">🎨</div>
          <p>Широкий выбор цветов</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">✨</div>
          <p>Высокое качество</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from '../utils/storage'
import { setUserLanguage } from '../utils/i18n'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const isLoading = ref(false)

const handleLogin = async () => {
  error.value = ''
  isLoading.value = true
  
  try {
    const users = await storage.getUsers()
    const loginInput = email.value.trim()
    
    const user = users.find(u => 
      (u.email === loginInput || u.username === loginInput) && 
      u.password === password.value
    )
    
    if (user) {
      const userData = {
        id: user.id,
        email: user.email,
        username: user.username,
        role: user.role,
        language: user.language || 'ru'
      }
      localStorage.setItem('currentUser', JSON.stringify(userData))
      
      if (user.language) {
        setUserLanguage(user.language)
      } else {
        setUserLanguage('ru')
      }
      
      if (user.role === 'printer') {
        router.push('/dashboard')
      } else {
        router.push('/home')
      }
    } else {
      error.value = 'Неверное имя пользователя/email или пароль'
    }
  } catch (e) {
    error.value = 'Не удалось подключиться к серверу. Попробуйте еще раз.'
    console.error('Login error:', e)
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.client-login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
  position: relative;
  overflow: hidden;
}

.background-decoration {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.3;
  animation: float 20s infinite ease-in-out;
}

.orb-1 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, #87CEEB 0%, transparent 70%);
  top: -200px;
  left: -200px;
  animation-delay: 0s;
}

.orb-2 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, #6bb6d6 0%, transparent 70%);
  bottom: -150px;
  right: -150px;
  animation-delay: 7s;
}

.orb-3 {
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, #4da6c2 0%, transparent 70%);
  top: 50%;
  right: 10%;
  animation-delay: 14s;
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -30px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

.login-content {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 480px;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.login-card {
  background: rgba(26, 26, 42, 0.8);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2.5rem;
  border: 1px solid rgba(135, 206, 235, 0.2);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(135, 206, 235, 0.1);
}

.card-header {
  text-align: center;
  margin-bottom: 2rem;
}

.card-header h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 0.5rem;
}

.card-header p {
  color: #a0d4e8;
  margin: 0;
  font-size: 0.95rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #a0d4e8;
  font-weight: 500;
  font-size: 0.95rem;
}

.input-icon {
  color: #87CEEB;
  flex-shrink: 0;
}

.modern-input {
  width: 100%;
  padding: 1rem 1.25rem;
  background: rgba(10, 10, 10, 0.6);
  border: 2px solid rgba(135, 206, 235, 0.2);
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  transition: all 0.3s ease;
  font-family: inherit;
}

.modern-input:focus {
  outline: none;
  border-color: #87CEEB;
  background: rgba(10, 10, 10, 0.8);
  box-shadow: 0 0 0 4px rgba(135, 206, 235, 0.1), 0 0 20px rgba(135, 206, 235, 0.2);
}

.modern-input::placeholder {
  color: #666;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  border-radius: 12px;
  color: #ff6b6b;
  font-size: 0.9rem;
}

.submit-button {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 100%);
  color: #000;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(135, 206, 235, 0.3);
  margin-top: 0.5rem;
  position: relative;
  overflow: hidden;
}

.submit-button::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.submit-button:hover:not(:disabled)::before {
  width: 300px;
  height: 300px;
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(135, 206, 235, 0.4);
}

.submit-button:active:not(:disabled) {
  transform: translateY(0);
}

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(0, 0, 0, 0.3);
  border-top-color: #000;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.card-footer {
  margin-top: 1.5rem;
  text-align: center;
  color: #999;
  font-size: 0.9rem;
}

.card-footer .link {
  color: #87CEEB;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s;
}

.card-footer .link:hover {
  color: #a0d4e8;
  text-decoration: underline;
}

.features-preview {
  display: flex;
  justify-content: space-around;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(26, 26, 42, 0.5);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(135, 206, 235, 0.1);
}

.feature-item {
  text-align: center;
  flex: 1;
}

.feature-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  filter: drop-shadow(0 0 10px rgba(135, 206, 235, 0.3));
}

.feature-item p {
  color: #a0d4e8;
  font-size: 0.85rem;
  margin: 0;
  font-weight: 500;
}

@media (max-width: 640px) {
  .client-login-container {
    padding: 1rem;
  }

  .login-card {
    padding: 2rem 1.5rem;
  }

  .features-preview {
    flex-direction: column;
    gap: 1rem;
  }

  .feature-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    text-align: left;
  }

  .feature-icon {
    margin-bottom: 0;
    font-size: 1.5rem;
  }
}
</style>

