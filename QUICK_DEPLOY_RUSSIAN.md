# Быстрый деплой русской версии на Firebase

## Шаги деплоя

### 1. Убедитесь, что Firebase CLI установлен

```bash
firebase --version
```

Если нет, установите:
```bash
npm install -g firebase-tools
```

### 2. Войдите в Firebase (если еще не вошли)

```bash
firebase login
```

### 3. Переключитесь на русский проект

```bash
firebase use russian
```

Или напрямую:
```bash
firebase use d-print-electrozavodskaya
```

### 4. Выполните деплой

```bash
npm run deploy:firebase
```

## Что происходит при деплое

1. **Сборка проекта** (`npm run build:firebase`):
   - Используется `vite.config.firebase.js`
   - Точка входа: `index.firebase.html` → `src/main.firebase.js`
   - Роутер: `src/router/index.firebase.js` (использует корневой путь `/`)
   - Результат: папка `dist/` с `index.html`

2. **Деплой на Firebase**:
   - Файлы из `dist/` загружаются на Firebase Hosting
   - Доступны по адресам:
     - `https://d-print-electrozavodskaya.web.app`
     - `https://d-print-electrozavodskaya.firebaseapp.com`

## Проверка после деплоя

1. Откройте `https://d-print-electrozavodskaya.web.app`
2. Должна открыться страница `/client-login` (русская версия)
3. Проверьте, что все страницы работают:
   - `/client-login` - вход
   - `/client-register` - регистрация
   - `/client-home` - главная (после входа)
   - `/client-create-order` - создание заказа

## Откат изменений

Если что-то пошло не так:

```bash
firebase hosting:rollback
```

## Переключение между проектами

```bash
# Русский проект (новый)
firebase use russian

# Старый проект (production)
firebase use production
```

## Устранение проблем

### Ошибка: "Project not found"
```bash
firebase projects:list
firebase use d-print-electrozavodskaya
```

### Ошибка: "Permission denied"
Убедитесь, что вы вошли в правильный аккаунт:
```bash
firebase login
```

### Ошибка сборки
Проверьте, что все зависимости установлены:
```bash
npm install
```

### Проблемы с роутингом
Убедитесь, что используется правильный роутер:
- Firebase версия использует `src/router/index.firebase.js`
- Обычная версия использует `src/router/index.js`

