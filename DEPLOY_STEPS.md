# Пошаговая инструкция по деплою

## ✅ Все готово к деплою

- ✅ Сборка завершена
- ✅ Файлы в `dist/` готовы
- ✅ `firebase.json` настроен с сайтом `print-electrozavodskaya`
- ✅ Сайт существует в Firebase проекте

## 🚀 Выполните деплой

### Команда для деплоя:

```bash
firebase deploy --only hosting --project d-print-electrozavodskaya
```

Или если проект уже настроен:

```bash
firebase use d-print-electrozavodskaya
firebase deploy --only hosting
```

## 📋 Что происходит при деплое

1. Firebase CLI загрузит файлы из папки `dist/`
2. Задеплоит их на сайт `print-electrozavodskaya`
3. После успешного деплоя приложение будет доступно на https://print-electrozavodskaya.web.app

## ⚠️ Если возникли проблемы

### Проблема: "Invalid project selection"

Выполните:
```bash
firebase use --add
```
Затем выберите проект `d-print-electrozavodskaya` из списка.

### Проблема: "Not logged in"

Выполните:
```bash
firebase login
```

### Проблема: "Permission denied"

Проверьте доступ к проекту в Firebase Console:
https://console.firebase.google.com/project/d-print-electrozavodskaya

## ✨ После успешного деплоя

Вы увидите сообщение:
```
✔  Deploy complete!

Hosting URL: https://print-electrozavodskaya.web.app
```

Откройте этот URL - должна открыться русская версия приложения!

