# Запуск русской версии приложения

## Быстрый запуск

```bash
npm run dev:russian
```

Приложение будет доступно по адресу: **http://localhost:3000**

## Что происходит при запуске

1. Используется конфигурация `vite.config.russian.dev.js`
2. Точка входа: `index.russian.dev.html`
3. Используется `src/main.firebase.js` (Firebase версия)
4. Используется роутер `src/router/index.firebase.js` (корневой путь `/`)
5. Редирект с `/` на `/client-login` (русская версия)

## Доступные страницы

После запуска вы сможете открыть:

- **http://localhost:3000/** → редирект на `/client-login`
- **http://localhost:3000/client-login** - страница входа (русская)
- **http://localhost:3000/client-register** - страница регистрации (русская)
- **http://localhost:3000/client-home** - главная страница клиента (после входа)
- **http://localhost:3000/client-create-order** - создание заказа (после входа)
- **http://localhost:3000/orders** - список заказов (после входа)

## Отличия от обычной версии

### Обычная версия (`npm run dev`)
- Использует `index.html` → `src/main.js` → `src/router/index.js`
- Базовый путь: `/Apps/Printing/`
- Редирект на `/login` (английская версия)

### Русская версия (`npm run dev:russian`)
- Использует `index.russian.dev.html` → `src/main.firebase.js` → `src/router/index.firebase.js`
- Базовый путь: `/` (корневой)
- Редирект на `/client-login` (русская версия)
- Включает все новые клиентские страницы

## API Endpoint

В dev режиме русской версии настроен proxy для API:
- Запросы к `/Apps/Printing/api` проксируются на `https://noyanov.com/Apps/Printing/api`

## Остановка сервера

Нажмите `Ctrl+C` в терминале, где запущен сервер.

## Устранение проблем

### Порт 3000 занят

Измените порт в `vite.config.russian.dev.js`:
```js
server: {
  port: 3001, // или другой свободный порт
}
```

### Ошибка при загрузке модулей

Убедитесь, что все зависимости установлены:
```bash
npm install
```

### Страница не открывается

1. Проверьте, что сервер запущен (должно быть сообщение в терминале)
2. Откройте браузер и перейдите на http://localhost:3000
3. Проверьте консоль браузера на наличие ошибок

### API запросы не работают

Убедитесь, что:
1. Бэкенд API доступен по адресу `https://noyanov.com/Apps/Printing/api`
2. CORS настроен правильно для localhost:3000

## Сборка для продакшена

Для сборки русской версии для деплоя на Firebase:

```bash
npm run build:firebase
```

Это создаст оптимизированную сборку в папке `dist/` для деплоя на Firebase Hosting.

