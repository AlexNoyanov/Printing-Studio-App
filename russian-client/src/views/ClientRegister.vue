<template>
  <div class="client-register-container">
    <div class="background-decoration">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
    </div>
    
    <div class="register-content">
      <div class="logo-section">
        <div class="logo-circle">
          <svg viewBox="0 0 100 100" class="logo-icon">
            <path d="M50 10 L90 30 L90 70 L50 90 L10 70 L10 30 Z" fill="none" stroke="currentColor" stroke-width="3"/>
            <circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" stroke-width="2"/>
            <circle cx="50" cy="50" r="8" fill="currentColor"/>
          </svg>
        </div>
        <h1 class="studio-title">Регистрация</h1>
        <p class="studio-subtitle">Создайте аккаунт для заказа 3D-печати</p>
      </div>

      <div class="register-card">
        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-group">
            <label for="username">
              <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              Имя пользователя
            </label>
            <input
              id="username"
              v-model="username"
              type="text"
              required
              placeholder="Введите имя пользователя"
              class="modern-input"
            />
          </div>

          <div class="form-group">
            <label for="email">
              <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              placeholder="Введите email"
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
              placeholder="Введите пароль (минимум 6 символов)"
              minlength="6"
              class="modern-input"
            />
          </div>

          <div class="form-group">
            <label for="role">
              <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
              Тип аккаунта
            </label>
            <select id="role" v-model="role" required class="modern-input">
              <option value="user">Клиент</option>
              <option value="printer">Владелец принтера</option>
            </select>
          </div>

          <div v-if="error" class="error-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            {{ error }}
          </div>

          <div v-if="success" class="success-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            {{ success }}
          </div>

          <button type="submit" class="submit-button" :disabled="isLoading">
            <span v-if="!isLoading">Зарегистрироваться</span>
            <span v-else class="loading-spinner"></span>
          </button>
        </form>

        <div class="card-footer">
          <p>Уже есть аккаунт? <router-link to="/login" class="link">Войти</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { storage } from '../utils/storage'
import { getBrowserLanguage, setUserLanguage } from '../utils/i18n'

const router = useRouter()
const username = ref('')
const email = ref('')
const password = ref('')
const role = ref('user')
const error = ref('')
const success = ref('')
const isLoading = ref(false)

onMounted(() => {
  const browserLang = getBrowserLanguage()
  if (browserLang && browserLang !== 'en') {
    setUserLanguage(browserLang)
  } else {
    setUserLanguage('ru')
  }
})

const handleRegister = async () => {
  error.value = ''
  success.value = ''
  isLoading.value = true
  
  try {
    const newUser = {
      id: Date.now().toString(),
      username: username.value,
      email: email.value,
      password: password.value,
      role: role.value,
      language: 'ru'
    }
    
    await storage.createUser(newUser)
    
    setUserLanguage('ru')
    
    success.value = 'Регистрация успешна! Перенаправление на страницу входа...'
    
    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (e) {
    if (e.message && (e.message.includes('already exists') || e.message.includes('409'))) {
      error.value = 'Email или имя пользователя уже зарегистрированы'
    } else {
      error.value = 'Ошибка регистрации: ' + (e.message || 'Попробуйте еще раз.')
    }
    console.error('Registration error:', e)
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.client-register-container {
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

.register-content {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 500px;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.logo-section {
  text-align: center;
  color: #fff;
}

.logo-circle {
  width: 100px;
  height: 100px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, rgba(135, 206, 235, 0.2) 0%, rgba(107, 182, 214, 0.1) 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(135, 206, 235, 0.3);
  box-shadow: 0 0 40px rgba(135, 206, 235, 0.2), inset 0 0 40px rgba(135, 206, 235, 0.1);
}

.logo-icon {
  width: 60px;
  height: 60px;
  color: #87CEEB;
  filter: drop-shadow(0 0 10px rgba(135, 206, 235, 0.5));
}

.studio-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem;
  background: linear-gradient(135deg, #87CEEB 0%, #a0d4e8 50%, #87CEEB 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 0 30px rgba(135, 206, 235, 0.3);
  letter-spacing: -0.5px;
}

.studio-subtitle {
  font-size: 1.1rem;
  color: #a0d4e8;
  margin: 0;
  opacity: 0.9;
}

.register-card {
  background: rgba(26, 26, 42, 0.8);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2.5rem;
  border: 1px solid rgba(135, 206, 235, 0.2);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(135, 206, 235, 0.1);
}

.register-form {
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

.modern-input option {
  background: #1a1a1a;
  color: #fff;
}

.error-message,
.success-message {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 12px;
  font-size: 0.9rem;
}

.error-message {
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  color: #ff6b6b;
}

.success-message {
  background: rgba(81, 207, 102, 0.1);
  border: 1px solid rgba(81, 207, 102, 0.3);
  color: #51cf66;
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

@media (max-width: 968px) {
  .register-card {
    max-width: 500px;
  }
}

@media (max-width: 640px) {
  .client-register-container {
    padding: 1rem;
  }

  .register-card {
    padding: 2rem 1.5rem;
  }

  .card-header h2 {
    font-size: 1.75rem;
  }
  
  .studio-title {
    font-size: 1.75rem;
  }
  
  .form-group label {
    font-size: 0.9rem;
  }
  
  .form-group input {
    padding: 0.875rem;
    font-size: 0.95rem;
  }
  
  .submit-btn {
    padding: 0.875rem;
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .client-register-container {
    padding: 0.75rem;
  }

  .register-card {
    padding: 1.75rem 1.25rem;
  }
  
  .card-header h2 {
    font-size: 1.5rem;
  }
  
  .studio-title {
    font-size: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1.25rem;
  }
  
  .form-group label {
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
  }
  
  .form-group input {
    padding: 0.8rem;
    font-size: 0.9rem;
  }
  
  .submit-btn {
    padding: 0.8rem;
    font-size: 0.95rem;
  }
  
  .login-link {
    font-size: 0.9rem;
  }
}
</style>

