# Деплой русской версии на Firebase

## Подготовка к деплою

### 1. Установка Firebase CLI (если еще не установлен)

```bash
npm install -g firebase-tools
```

### 2. Вход в Firebase

```bash
firebase login
```

### 3. Инициализация проекта (если нужно)

```bash
firebase init hosting
```

Выберите проект: `d-print-electrozavodskaya`

### 4. Переключение на новый проект

```bash
firebase use d-print-electrozavodskaya
```

Или используйте алиас:

```bash
firebase use russian
```

## Конфигурация

Проект уже настроен:
- `.firebaserc` - содержит конфигурацию проектов
- `firebase.json` - конфигурация хостинга
- `vite.config.firebase.js` - конфигурация сборки для Firebase
- `src/router/index.firebase.js` - роутер для Firebase (использует корневой путь)

## Сборка и деплой

### Вариант 1: Использование npm скрипта (рекомендуется)

```bash
npm run deploy:firebase
```

Это выполнит:
1. `npm run build:firebase` - сборка проекта с конфигурацией для Firebase
   - Использует `vite.config.firebase.js` для сборки
   - Использует `index.firebase.html` как точку входа
   - Использует `src/main.firebase.js` который импортирует `src/router/index.firebase.js`
   - Автоматически копирует `index.firebase.html` в `index.html` в dist
2. `firebase deploy --only hosting` - деплой на Firebase Hosting

### Вариант 2: Ручная сборка и деплой

```bash
# Сборка проекта
npm run build:firebase

# Деплой на Firebase
firebase deploy --only hosting
```

### Вариант 3: Деплой с указанием проекта

```bash
firebase deploy --only hosting --project d-print-electrozavodskaya
```

## Проверка после деплоя

После успешного деплоя приложение будет доступно по адресу:
- `https://d-print-electrozavodskaya.web.app`
- `https://d-print-electrozavodskaya.firebaseapp.com`

## Важные замечания

1. **API Endpoint**: Убедитесь, что в `src/utils/storage.js` правильно настроен `API_BASE` для работы с вашим бэкендом.

2. **Роутинг**: Firebase версия использует `index.firebase.js` роутер, который настроен на корневой путь `/`.

3. **База данных**: Убедитесь, что бэкенд API доступен и CORS настроен правильно для нового домена.

4. **Переменные окружения**: Если используются переменные окружения, их нужно настроить в Firebase Console или использовать в коде напрямую.

## Структура маршрутов

Русская версия использует следующие маршруты:
- `/` → редирект на `/client-login`
- `/client-login` - страница входа для клиентов
- `/client-register` - страница регистрации для клиентов
- `/client-home` - главная страница клиента
- `/client-create-order` - создание заказа для клиентов
- `/orders` - список заказов
- `/dashboard` - панель управления для принтеров

## Откат деплоя

Если нужно откатить изменения:

```bash
firebase hosting:rollback
```

## Просмотр истории деплоев

```bash
firebase hosting:channel:list
```

## Дополнительные команды

```bash
# Просмотр текущего проекта
firebase projects:list

# Переключение между проектами
firebase use production  # старый проект
firebase use russian     # новый русский проект

# Просмотр конфигурации
firebase hosting:channel:list
```

