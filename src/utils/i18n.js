// Internationalization utility
// Supports multiple languages with fallback to English

// Cache for current language state
let currentLanguageState = null

const translations = {
  en: {
    // Login page
    login: 'Login',
    usernameOrEmail: 'Username or Email',
    enterUsernameOrEmail: 'Enter your username or email',
    password: 'Password',
    enterPassword: 'Enter your password',
    loginButton: 'Login',
    noAccount: "Don't have an account?",
    register: 'Register',
    invalidCredentials: 'Invalid username/email or password',
    connectionError: 'Failed to connect to server. Please try again.',
    
    // Register page
    registerTitle: 'Register',
    username: 'Username',
    enterUsername: 'Enter your username',
    email: 'Email',
    enterEmail: 'Enter your email',
    accountType: 'Account Type',
    regularUser: 'Regular User',
    printerOwner: 'Printer Owner',
    language: 'Language',
    registerButton: 'Register',
    alreadyHaveAccount: 'Already have an account?',
    registrationSuccessful: 'Registration successful! Redirecting to login...',
    emailOrUsernameExists: 'Email or username already registered',
    registrationFailed: 'Registration failed',
    pleaseTryAgain: 'Please try again.',
    
    // Common
    english: 'English',
    russian: 'Russian',
    spanish: 'Spanish',
    french: 'French',
    german: 'German',
    italian: 'Italian',
    portuguese: 'Portuguese',
    chinese: 'Chinese',
    japanese: 'Japanese',
    korean: 'Korean'
  },
  ru: {
    // Login page
    login: 'Вход',
    usernameOrEmail: 'Имя пользователя или Email',
    enterUsernameOrEmail: 'Введите имя пользователя или email',
    password: 'Пароль',
    enterPassword: 'Введите пароль',
    loginButton: 'Войти',
    noAccount: 'Нет аккаунта?',
    register: 'Регистрация',
    invalidCredentials: 'Неверное имя пользователя/email или пароль',
    connectionError: 'Не удалось подключиться к серверу. Попробуйте еще раз.',
    
    // Register page
    registerTitle: 'Регистрация',
    username: 'Имя пользователя',
    enterUsername: 'Введите имя пользователя',
    email: 'Email',
    enterEmail: 'Введите email',
    accountType: 'Тип аккаунта',
    regularUser: 'Обычный пользователь',
    printerOwner: 'Владелец принтера',
    language: 'Язык',
    registerButton: 'Зарегистрироваться',
    alreadyHaveAccount: 'Уже есть аккаунт?',
    registrationSuccessful: 'Регистрация успешна! Перенаправление на страницу входа...',
    emailOrUsernameExists: 'Email или имя пользователя уже зарегистрированы',
    registrationFailed: 'Ошибка регистрации',
    pleaseTryAgain: 'Попробуйте еще раз.',
    
    // Common
    english: 'Английский',
    russian: 'Русский',
    spanish: 'Испанский',
    french: 'Французский',
    german: 'Немецкий',
    italian: 'Итальянский',
    portuguese: 'Португальский',
    chinese: 'Китайский',
    japanese: 'Японский',
    korean: 'Корейский'
  }
}

// Get browser language
function getBrowserLanguage() {
  // Try to get language from browser
  const browserLang = navigator.language || navigator.userLanguage || 'en'
  // Extract language code (e.g., 'ru-RU' -> 'ru')
  const langCode = browserLang.split('-')[0].toLowerCase()
  
  // Check if we support this language
  if (translations[langCode]) {
    return langCode
  }
  
  // Fallback to English
  return 'en'
}

// Get user's saved language preference
function getUserLanguage() {
  try {
    const currentUserStr = localStorage.getItem('currentUser')
    if (currentUserStr) {
      const currentUser = JSON.parse(currentUserStr)
      if (currentUser.language) {
        return currentUser.language
      }
    }
  } catch (e) {
    console.error('Error getting user language:', e)
  }
  return null
}

// Get current language (saved preference > user account > browser language > English)
function getCurrentLanguage() {
  // Use cached state if available
  if (currentLanguageState !== null) {
    return currentLanguageState
  }
  
  // First check saved language preference (persists after logout)
  const savedLang = getSavedLanguage()
  if (savedLang && translations[savedLang]) {
    currentLanguageState = savedLang
    return savedLang
  }
  
  // Then check user account language
  const userLang = getUserLanguage()
  if (userLang && translations[userLang]) {
    currentLanguageState = userLang
    return userLang
  }
  
  // Fallback to browser language
  const browserLang = getBrowserLanguage()
  currentLanguageState = browserLang
  return browserLang
}

// Translate function
export function t(key, lang = null) {
  const currentLang = lang || getCurrentLanguage()
  const translation = translations[currentLang]?.[key]
  
  if (translation) {
    return translation
  }
  
  // Fallback to English if translation not found
  if (currentLang !== 'en') {
    return translations.en[key] || key
  }
  
  return key
}

// Get all translations for a language
export function getTranslations(lang = null) {
  const currentLang = lang || getCurrentLanguage()
  return translations[currentLang] || translations.en
}

// Set user language preference
export function setUserLanguage(language) {
  try {
    // Store language preference separately so it persists after logout
    localStorage.setItem('userLanguage', language)
    
    // Also update in currentUser if logged in
    const currentUserStr = localStorage.getItem('currentUser')
    if (currentUserStr) {
      const currentUser = JSON.parse(currentUserStr)
      currentUser.language = language
      localStorage.setItem('currentUser', JSON.stringify(currentUser))
    }
  } catch (e) {
    console.error('Error setting user language:', e)
  }
}

// Get saved language preference (from localStorage, not just user account)
export function getSavedLanguage() {
  try {
    return localStorage.getItem('userLanguage')
  } catch (e) {
    return null
  }
}

// Get current language code
export function getLanguage() {
  return getCurrentLanguage()
}

// Set current language (updates cache)
export function setCurrentLanguage(language) {
  if (translations[language]) {
    currentLanguageState = language
    setUserLanguage(language)
  }
}

// Initialize current language (loads from saved preference or browser)
export function initCurrentLanguage() {
  currentLanguageState = getCurrentLanguage()
}

export default {
  t,
  getTranslations,
  setUserLanguage,
  getLanguage,
  getBrowserLanguage,
  getUserLanguage,
  getSavedLanguage,
  setCurrentLanguage,
  initCurrentLanguage
}

