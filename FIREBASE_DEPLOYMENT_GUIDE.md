# Руководство по деплою на Firebase проекты

Этот проект поддерживает деплой на два разных Firebase проекта:
1. **Production** - `printing-studio-app-4e0e6` (оригинальная английская версия)
2. **Russian** - `d-print-electrozavodskaya` (новая русская версия)

## 📁 Структура конфигураций

### Firebase конфигурации
- `firebase-config.production.js` - конфигурация для production проекта
- `firebase-config.russian.js` - конфигурация для русского проекта
- `firebase.json.production` - конфигурация Firebase Hosting для production
- `firebase.json.russian` - конфигурация Firebase Hosting для русского проекта
- `.firebaserc` - содержит алиасы для обоих проектов

### Vite конфигурации
- `vite.config.js` - стандартная конфигурация (для серверного деплоя)
- `vite.config.production.js` - конфигурация для production Firebase
- `vite.config.firebase.js` - конфигурация для русского Firebase проекта

### Точки входа
- `index.html` - стандартная точка входа (используется для production)
- `index.firebase.html` - точка входа для русского проекта
- `src/main.js` - стандартная точка входа (использует `src/router/index.js`)
- `src/main.firebase.js` - точка входа для Firebase (использует `src/router/index.firebase.js`)

### Роутеры
- `src/router/index.js` - стандартный роутер (для серверного деплоя)
- `src/router/index.firebase.js` - роутер для Firebase (корневой путь `/`)

## 🚀 Быстрый деплой

### Деплой на Production проект (английская версия)

```bash
npm run deploy:production
```

Или вручную:
```bash
firebase use production
cp firebase.json.production firebase.json
npm run build:production
firebase deploy --only hosting
```

**URL после деплоя:**
- `https://printing-studio-app-4e0e6.web.app`
- `https://printing-studio-app-4e0e6.firebaseapp.com`

### Деплой на Russian проект (русская версия)

```bash
npm run deploy:russian
```

Или вручную:
```bash
firebase use russian
cp firebase.json.russian firebase.json
npm run build:firebase
firebase deploy --only hosting
```

**URL после деплоя:**
- `https://d-print-electrozavodskaya.web.app`
- `https://d-print-electrozavodskaya.firebaseapp.com`

## 🔄 Переключение между проектами

### Переключение на Production

```bash
npm run switch:production
```

Или:
```bash
firebase use production
cp firebase.json.production firebase.json
```

### Переключение на Russian

```bash
npm run switch:russian
```

Или:
```bash
firebase use russian
cp firebase.json.russian firebase.json
```

## 📋 Доступные npm скрипты

| Скрипт | Описание |
|--------|----------|
| `npm run build` | Стандартная сборка (для серверного деплоя) |
| `npm run build:production` | Сборка для production Firebase проекта |
| `npm run build:firebase` | Сборка для русского Firebase проекта |
| `npm run deploy:production` | Полный деплой на production проект |
| `npm run deploy:russian` | Полный деплой на русский проект |
| `npm run switch:production` | Переключение на production проект |
| `npm run switch:russian` | Переключение на русский проект |

## 🔍 Проверка текущего проекта

```bash
firebase projects:list
firebase use
```

## 📝 Различия между проектами

### Production проект
- Использует стандартный роутер (`src/router/index.js`)
- Использует стандартную точку входа (`src/main.js`)
- Использует `index.html`
- Редирект с `/` на `/login`
- Английская версия по умолчанию

### Russian проект
- Использует Firebase роутер (`src/router/index.firebase.js`)
- Использует Firebase точку входа (`src/main.firebase.js`)
- Использует `index.firebase.html` (копируется в `index.html`)
- Редирект с `/` на `/client-login`
- Русская версия по умолчанию
- Включает новые клиентские страницы (ClientLogin, ClientRegister, ClientHome, ClientCreateOrder)

## 🛠️ Ручная настройка

Если нужно настроить вручную:

### 1. Выберите проект
```bash
firebase use production  # или firebase use russian
```

### 2. Скопируйте правильный firebase.json
```bash
cp firebase.json.production firebase.json  # для production
# или
cp firebase.json.russian firebase.json    # для russian
```

### 3. Соберите проект
```bash
npm run build:production  # для production
# или
npm run build:firebase    # для russian
```

### 4. Деплой
```bash
firebase deploy --only hosting
```

## ⚠️ Важные замечания

1. **Всегда проверяйте текущий проект** перед деплоем:
   ```bash
   firebase use
   ```

2. **Убедитесь, что правильный firebase.json активен**:
   ```bash
   cat firebase.json
   ```

3. **Проверьте, что правильная конфигурация используется** для сборки

4. **API Endpoint**: Убедитесь, что `src/utils/storage.js` правильно настроен для каждого проекта

5. **CORS**: Убедитесь, что бэкенд настроен для работы с обоими доменами Firebase

## 🐛 Устранение проблем

### Ошибка: "Project not found"
```bash
firebase projects:list
firebase use production  # или firebase use russian
```

### Ошибка: "Wrong firebase.json"
```bash
# Проверьте текущий проект
firebase use

# Скопируйте правильный файл
cp firebase.json.production firebase.json  # или firebase.json.russian
```

### Ошибка сборки
Убедитесь, что используете правильную конфигурацию Vite:
- Production: `vite.config.production.js`
- Russian: `vite.config.firebase.js`

## 📚 Дополнительная документация

- `DEPLOY_FIREBASE_RUSSIAN.md` - подробная инструкция для русского проекта
- `QUICK_DEPLOY_RUSSIAN.md` - быстрая инструкция для русского проекта
- `DEPLOYMENT_SUMMARY.md` - общая сводка

