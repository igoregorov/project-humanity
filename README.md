# 🌍 Project Humanity

Сайт-визитка международного проекта `projecthumanity.space`. Самописный, без CMS, без фреймворков — чистый PHP с претензией на архитектуру.

> *«Мы не используем WordPress, потому что можем».*

## 📋 О проекте

Сайт рассказывает о миссии проекта, его принципах, показывает новости и события. Поддерживает несколько языков, имеет авторизацию пользователей и систему виджетов в сайдбарах.

**Миссия сайта** — не просто визитка, а живая платформа с контентом, который можно редактировать через JSON-файлы или (в перспективе) через базу данных.

## 🛠 Технологический стек

| Компонент | Версия / Технология |
|-----------|---------------------|
| Язык | PHP 8.1 (strict_types everywhere) |
| Веб-сервер | Apache 2.4 |
| СУБД | MariaDB 10.x |
| ОС | Ubuntu 22.04 |
| Шрифты | TTF (arial.ttf) для капчи |
| Локализация | JSON-файлы в `locales/` |

**Никаких внешних фреймворков.** Весь DI-контейнер, роутер, ORM-подобные репозитории — написаны с нуля.

## 🏗 Архитектура

Проект построен по принципу слоёв, напоминающему упрощённую версию Clean Architecture:

```
┌─────────────────────────────────────────┐
│  Http (Controllers, Router, Request)    │  ← точка входа запроса
├─────────────────────────────────────────┤
│  Application (Services, Managers)       │  ← бизнес-логика
├─────────────────────────────────────────┤
│  Domain (Interfaces, Entities)          │  ← ядро, не знает о внешнем мире
├─────────────────────────────────────────┤
│  Infrastructure (DB, JSON, Auth, ...)   │  ← конкретные реализации
├─────────────────────────────────────────┤
│  View (Templates, Data DTO)             │  ← рендеринг HTML
└─────────────────────────────────────────┘
```

### Ключевые компоненты

- **`SimpleContainer`** — собственный DI-контейнер с поддержкой `singleton`, `factory` и `set`
- **`Router`** — простой маршрутизатор на основе GET-параметров (`?page=home&lang=ru`)
- **`RepositoryFactory`** — фабрика репозиториев, выбирающая драйвер (JSON / Database) по конфигу
- **`DatabaseConnectionManager`** — ленивое подключение к БД с health-check
- **`SidebarWidgetFactory`** — фабрика виджетов для сайдбаров

## 📁 Структура проекта

```
projecthumanity.space/
├── index.php                      # Точка входа
├── captcha.php                    # Генератор капчи
├── run_migrations.php             # CLI-скрипт миграций БД
├── check_db_status.php            # Диагностика подключения к БД
│
├── app/
│   └── bootstrap.php              # Инициализация, хендлеры ошибок, сессии
│
├── config/
│   ├── .env.production            # Переменные окружения (продакшн)
│   ├── languages.json             # Поддерживаемые языки
│   └── .settings.config           # Флаги фич (DB_IS_ACTIVE, и т.д.)
│
├── .env.local                     # Локальные переменные окружения
├── container.php                  # Регистрация всех сервисов в DI
├── config.php                     # Загрузка конфигурации
│
├── src/
│   ├── Application/               # Сервисы приложения
│   │   ├── ContentManager.php     # Работа с новостями и событиями
│   │   ├── EventsService.php
│   │   ├── NewsService.php
│   │   ├── LocalizedContentService.php
│   │   ├── NavService.php
│   │   ├── PageDataService.php
│   │   └── SidebarManager.php
│   │
│   ├── Domain/                    # Ядро (интерфейсы и сущности)
│   │   ├── Auth/                  # User, UserRepositoryInterface, AuthServiceInterface
│   │   ├── Sidebar/               # Виджеты и их интерфейсы
│   │   ├── ContentRepositoryInterface.php
│   │   ├── EventsRepositoryInterface.php
│   │   ├── NewsRepositoryInterface.php
│   │   ├── TimelineRepositoryInterface.php
│   │   ├── SocialRepositoryInterface.php
│   │   └── TelegramRepositoryInterface.php
│   │
│   ├── Http/                      # HTTP-слой
│   │   ├── Controllers/           # Контроллеры страниц
│   │   ├── Router.php
│   │   ├── RequestHandler.php
│   │   ├── ViewRenderer.php
│   │   └── functions.php          # sendCleanErrorPage()
│   │
│   ├── Infrastructure/            # Реализации
│   │   ├── Auth/                  # SessionAuthService, UserRepositories
│   │   ├── Database/              # ConnectionManager, HealthCheck
│   │   ├── Security/              # CaptchaService
│   │   ├── ConfigLoader.php
│   │   ├── DotEnv.php
│   │   ├── EnvironmentDetector.php
│   │   ├── SimpleContainer.php
│   │   ├── RepositoryFactory.php
│   │   ├── Json*Repository.php    # JSON-реализации
│   │   └── Database*Repository.php # DB-реализации (в процессе)
│   │
│   ├── Services/
│   │   └── LanguageService.php
│   │
│   └── View/                      # DTO для шаблонов
│       ├── AuthData.php
│       ├── FooterData.php
│       ├── NavData.php
│       ├── MainContentData.php
│       ├── PageAboutData.php
│       ├── PagePrinciplesData.php
│       ├── SidebarData.php
│       └── TemplateDataInterface.php
│
├── includes/                      # PHP-шаблоны
│   ├── layout.php
│   ├── nav.php
│   ├── footer.php
│   ├── sidebar.php
│   ├── main_content.php
│   ├── auth_form.php
│   ├── auth_profile.php
│   ├── page_about.php
│   ├── page_principles.php
│   └── helpers.php                # sanitizeHtml()
│
├── locales/                       # JSON с переводами и контентом
│   ├── main/
│   ├── about/
│   ├── events/
│   ├── news/
│   ├── principles/
│   ├── timeline/
│   ├── social/
│   └── telegram/
│
├── migrations/                    # SQL-файлы миграций
├── storage/                       # Файловое хранилище (users.json)
└── vendor/                        # Composer (только автозагрузка)
```

## ⚙️ Установка и запуск

### 1. Клонирование и зависимости

```bash
git clone <repo-url> projecthumanity.space
cd projecthumanity.space
composer install
```

### 2. Конфигурация

Создай `.env.local` (для локальной разработки) или отредактируй `config/.env.production`:

```env
APP_DEBUG=true
DB_HOST=localhost
DB_PORT=3306
DB_NAME=projecthumanity
DB_USER=root
DB_PASS=secret

LOCALES_MAIN_PATH=./locales/main
LOCALES_ABOUT_PATH=./locales/about
LOCALES_EVENTS_PATH=./locales/events
LOCALES_NEWS_PATH=./locales/news
LOCALES_PRINCIPLES_PATH=./locales/principles
LOCALES_TIMELINE_PATH=./locales/timeline
LOCALES_SOCIAL_PATH=./locales/social
LOCALES_TELEGRAM_PATH=./locales/telegram

STORAGE_DRIVER=json
SITE_VERSION=1.0
```

### 3. Настройка БД

```bash
# Создать базу данных
mysql -u root -p -e "CREATE DATABASE projecthumanity CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Применить миграции
php run_migrations.php
```

### 4. Проверка окружения

```bash
php check_db_status.php
```

### 5. Запуск локально

```bash
php -S localhost:8000
```

Или настрой виртуальный хост Apache с `DocumentRoot` на корень проекта.

## 🗄 База данных

### Таблицы (создаются миграциями)

- **`migrations`** — журнал применённых миграций
- **`users`** — пользователи (id, username, email, password_hash, role, is_active, timestamps)
- **`login_attempts`** — попытки входа (для брутфорс-защиты)
- **`sessions`** — пользовательские сессии

### Переключение драйвера хранилища

В `config/.settings.config` или через `STORAGE_DRIVER` в `.env`:

```ini
; config/.settings.config
DB_IS_ACTIVE=true
```

```env
# .env
STORAGE_DRIVER=database   # или 'json'
```

## 🚦 Текущий статус работ

### ✅ Готово

- [x] Базовая архитектура (DI, роутер, слои)
- [x] JSON-хранилище для всего контента
- [x] Система локализации (RU/EN)
- [x] Авторизация пользователей (регистрация, вход, выход, профиль)
- [x] Капча на базе GD
- [x] Защита от брутфорса (5 попыток / 15 минут)
- [x] Сайдбары с виджетами (события, новости, таймлайн, соцсети, Telegram)
- [x] Миграции БД (таблицы `users`, `sessions`, `login_attempts`)
- [x] HTTP-заголовки безопасности (CSP, X-Frame-Options, Referrer-Policy)
- [x] Красивая страница ошибки с debug-режимом

### 🚧 В процессе

- [ ] **Database-репозитории** — классы созданы, но методы бросают `Exception('not implemented')`:
  - `DatabaseContentRepository`
  - `DatabaseEventsRepository`
  - `DatabaseNewsRepository`
  - `DatabaseTimelineRepository`
  - `DatabaseSocialRepository`
  - `DatabaseTelegramRepository`
- [ ] Миграции для таблиц контента (events, news, timeline, social, telegram)
- [ ] Тестирование переключения `STORAGE_DRIVER=database` в боевом режиме

### 📋 Планы

- [ ] CSRF-защита форм (токены в сессии)
- [ ] Админ-панель для управления контентом
- [ ] Кэширование часто запрашиваемых данных
- [ ] Покрытие тестами критичных сервисов
- [ ] Рефакторинг `ContentManager::getAllNews()` — вынести сортировку на уровень БД

## 🔐 Безопасность

- Пароли хранятся через `password_hash()` (bcrypt)
- Сессии: `httponly`, `samesite=Strict`, регенерация ID при старте
- Вывод экранируется через `htmlspecialchars()` везде, кроме разрешённых HTML-тегов
- `sanitizeHtml()` валидирует `href` у ссылок (только относительные + доверенные домены)
- Капча живёт 10 минут, уничтожается после использования

**Известные уязвимости, которые нужно закрыть:**
- ⚠️ Отсутствует CSRF-защита в формах логина/регистрации
- ⚠️ Logout через GET — уязвим к CSRF-атакам

## 🧪 CLI-скрипты

| Скрипт | Назначение |
|--------|-----------|
| `php run_migrations.php` | Применение новых миграций БД |
| `php check_db_status.php` | Диагностика подключения к БД |

## 👤 Автор

Игорь, Москва. Ядерный физик по образованию, программист по призванию, фантаст по убеждению.

Проект делается в свободное время, на чистом энтузиазме и при поддержке семьи.

## 📄 Лицензия

Пока никак не помечена. Если проект когда-нибудь вырастет — стоит выбрать.

