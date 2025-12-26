# Настройка конфигурации Firebase

⚠️ **ВАЖНО**: Файлы с реальными конфигурациями Firebase **НЕ** должны попадать в git репозиторий!

## Файлы, которые НЕ должны быть в git

Следующие файлы добавлены в `.gitignore` и не будут закоммичены:

- `.firebaserc` - содержит ID проектов Firebase
- `firebase.json` - конфигурация Firebase Hosting
- `firebase-config.js` - конфигурация Firebase SDK (содержит API ключи)
- `firebase-config.production.js` - конфигурация для production проекта
- `firebase-config.russian.js` - конфигурация для русского проекта
- `.firebase/` - директория с кешем Firebase

## Настройка для нового разработчика

### 1. Скопируйте примеры конфигураций

```bash
# Основная конфигурация
cp firebase-config.example.js firebase-config.js

# Конфигурации для проектов
cp firebase-config.production.example.js firebase-config.production.js
cp firebase-config.russian.example.js firebase-config.russian.js

# Конфигурация Firebase CLI
cp .firebaserc.example .firebaserc

# Конфигурация Firebase Hosting
cp firebase.json.example firebase.json
```

### 2. Заполните реальные данные

#### firebase-config.production.js
Замените значения в `firebaseConfig` на реальные данные из Firebase Console:
- Project: `printing-studio-app-4e0e6`
- Firebase Console: https://console.firebase.google.com/project/printing-studio-app-4e0e6/settings/general

#### firebase-config.russian.js
Замените значения в `firebaseConfig` на реальные данные из Firebase Console:
- Project: `d-print-electrozavodskaya`
- Firebase Console: https://console.firebase.google.com/project/d-print-electrozavodskaya/settings/general

#### .firebaserc
Замените ID проектов на реальные:
```json
{
  "projects": {
    "default": "d-print-electrozavodskaya",
    "production": "printing-studio-app-4e0e6",
    "russian": "d-print-electrozavodskaya"
  }
}
```

### 3. Получение конфигурации из Firebase Console

1. Откройте [Firebase Console](https://console.firebase.google.com/)
2. Выберите нужный проект
3. Перейдите в **Project Settings** (⚙️ → Project settings)
4. Прокрутите до раздела **Your apps**
5. Выберите веб-приложение или создайте новое
6. Скопируйте конфигурацию из раздела **Firebase SDK snippet**
7. Вставьте значения в соответствующий файл конфигурации

## Проверка перед коммитом

Перед коммитом в git убедитесь, что:

```bash
# Проверьте, что файлы игнорируются
git status

# Убедитесь, что реальные конфигурации не показываются
# Должны быть видны только .example файлы
```

Если видите реальные конфигурационные файлы в `git status`, они не будут закоммичены благодаря `.gitignore`, но лучше убедиться, что они не были добавлены ранее:

```bash
# Проверьте, не были ли они добавлены ранее
git check-ignore firebase-config.js
git check-ignore .firebaserc

# Если файлы были добавлены ранее, удалите их из индекса
git rm --cached firebase-config.js
git rm --cached .firebaserc
git rm --cached firebase-config.production.js
git rm --cached firebase-config.russian.js
```

## Безопасность

⚠️ **Никогда не коммитьте**:
- API ключи Firebase
- Project IDs
- Messaging Sender IDs
- App IDs
- Measurement IDs

Эти данные позволяют получить доступ к вашему Firebase проекту и должны храниться в секрете.

## Примеры файлов

В репозитории хранятся только `.example` файлы, которые можно безопасно коммитить:
- `firebase-config.example.js`
- `firebase-config.production.example.js`
- `firebase-config.russian.example.js`
- `.firebaserc.example`
- `firebase.json.example`

