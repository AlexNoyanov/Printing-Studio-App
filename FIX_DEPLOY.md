# Исправление проблемы "Site Not Found"

## Проблема

Сайт `print-electrozavodskaya.web.app` показывает "Site Not Found" потому что на него еще не был выполнен деплой.

## Решение

### Шаг 1: Убедитесь, что вы вошли в Firebase

```bash
firebase login
```

### Шаг 2: Пересоберите проект (если нужно)

```bash
npm run build:firebase
```

### Шаг 3: Проверьте конфигурацию

Убедитесь, что `firebase.json` содержит:

```json
{
  "hosting": {
    "site": "print-electrozavodskaya",
    "public": "dist",
    ...
  }
}
```

### Шаг 4: Выполните деплой

**Вариант A: С указанием проекта**

```bash
firebase deploy --only hosting --project d-print-electrozavodskaya
```

**Вариант B: После переключения на проект**

```bash
# Сначала добавьте проект (если его нет)
firebase use --add
# Выберите: d-print-electrozavodskaya

# Затем переключитесь
firebase use d-print-electrozavodskaya

# И выполните деплой
firebase deploy --only hosting
```

**Вариант C: Использование скрипта**

```bash
npm run deploy:russian
```

## Проверка после деплоя

После успешного деплоя вы должны увидеть что-то вроде:

```
✔  Deploy complete!

Project Console: https://console.firebase.google.com/project/d-print-electrozavodskaya/overview
Hosting URL: https://print-electrozavodskaya.web.app
```

## Важные замечания

1. **Два разных сайта в проекте:**
   - `d-print-electrozavodskaya` - дефолтный сайт проекта
   - `print-electrozavodskaya` - дополнительный сайт (на него нужно деплоить)

2. **Конфигурация правильная:** В `firebase.json` указан `"site": "print-electrozavodskaya"`, что правильно.

3. **Файлы готовы:** Все файлы собраны в `dist/` и готовы к деплою.

## Если деплой не работает

### Ошибка: "Invalid project selection"

```bash
# Добавьте проект вручную
firebase use --add
# Выберите проект из списка: d-print-electrozavodskaya
```

### Ошибка: "Permission denied"

```bash
# Войдите в Firebase
firebase login

# Проверьте доступ в Firebase Console
# https://console.firebase.google.com/project/d-print-electrozavodskaya
```

### Ошибка: "Site not found"

Сайт существует, просто на него нужно задеплоить. Выполните деплой командой выше.

## После успешного деплоя

Откройте https://print-electrozavodskaya.web.app - должна открыться русская версия приложения с страницей входа.

