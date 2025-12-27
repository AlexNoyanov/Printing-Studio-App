import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index'
import { setCurrentLanguage, setUserLanguage } from './utils/i18n'

// Force Russian language for Russian client version
setCurrentLanguage('ru')
setUserLanguage('ru')
localStorage.setItem('userLanguage', 'ru')

createApp(App).use(router).mount('#app')

