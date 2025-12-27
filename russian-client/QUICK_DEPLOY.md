# Быстрый деплой русской версии

## Выполните эти команды:

```bash
cd /Users/anoyanov/Documents/Work/Printing-App/russian-client

# Установите зависимости (если еще не установлены)
npm install

# Соберите проект
npm run build

# Задеплойте
firebase deploy --only hosting --project d-print-electrozavodskaya
```

Или одной командой:

```bash
cd /Users/anoyanov/Documents/Work/Printing-App/russian-client && ./deploy.sh
```

## После деплоя

Откройте: https://print-electrozavodskaya.web.app

**Очистите кеш браузера:** Cmd+Shift+R (Mac) или Ctrl+Shift+R (Windows/Linux)

