# Русская версия студии 3D-печати

Отдельная русская версия приложения для студии 3D-печати.

## Структура

- `/` - Лендинг с описанием студии и информацией о доставке/самовывозе
- `/login` - Вход
- `/register` - Регистрация
- `/home` - Домашняя страница (после входа)
- `/orders` - Страница заказов
- `/create-order` - Создание заказа

## Установка

```bash
cd russian-client
npm install
```

## Разработка

```bash
npm run dev
```

Приложение будет доступно на http://localhost:3001

## Сборка

```bash
npm run build
```

## Деплой на Firebase

### Автоматический деплой:

```bash
./deploy.sh
```

### Ручной деплой:

```bash
# 1. Соберите проект
npm run build

# 2. Задеплойте
firebase deploy --only hosting --project d-print-electrozavodskaya
```

## После деплоя

Приложение будет доступно на: https://print-electrozavodskaya.web.app

**Важно:** После деплоя очистите кеш браузера (Cmd+Shift+R или Ctrl+Shift+R)

