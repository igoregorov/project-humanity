<?php
declare(strict_types=1);
// container.php

require_once __DIR__ . '/vendor/autoload.php';

use App\Application\LocalizedContentService;
use App\Application\EventsService;
use App\Application\NewsService;
use App\Infrastructure\RepositoryFactory;
use App\Infrastructure\SimpleContainer;
use App\Services\LanguageService;
use App\Http\Router;
use App\Application\CsrfService;
use App\Application\FlashMessageService;
use App\Application\LivingBookService;
use App\Domain\LivingBook\BookRepositoryInterface;
use App\Http\Controllers\LivingBookController;
use App\Infrastructure\Database\DatabaseBookRepository;
use App\Infrastructure\Security\BookPhotoUploadService;


// Загружаем конфигурацию
$config = require_once __DIR__ . '/config.php';

// Создаём контейнер
$container = new SimpleContainer();

// Регистрируем конфигурацию как СЕРВИС в контейнере
$container->set('config', $config);

// Сервисы для локализации
$container->singleton('language_service', function ($c) {
    $config = $c->get('config')['app'];
    return new LanguageService(
        $config['allowed_languages'],
        $config['languages']
    );
});

// Менеджер подключения к БД
$container->singleton('database.connection_manager', function ($c) {
    $config = $c->get('config');
    return new App\Infrastructure\Database\DatabaseConnectionManager($config['database']);
});

// PDO соединение (ленивая инициализация)
$container->singleton('pdo', function ($c) {
    $connectionManager = $c->get('database.connection_manager');
    return $connectionManager->getConnection();
});

// Репозитории
$storageDriver = $config['app']['storage_driver'] ?? 'json';
$dbIsActive = (bool) ($config['database']['is_active'] ?? false);
if ($storageDriver === 'database' && !$dbIsActive) {
    $storageDriver = 'json';
}

$container->singleton('content_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createContentRepository($storageDriver, $locales['main'], $pdo);
});

$container->singleton('events_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createEventsRepository($storageDriver, $locales['events'], $pdo);
});

$container->singleton('news_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createNewsRepository($storageDriver, $locales['news'], $pdo);
});

// Репозитории для виджетов
$container->singleton('timeline_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createTimelineRepository($storageDriver, $locales['timeline'] ?? './locales/timeline', $pdo);
});

$container->singleton('social_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createSocialRepository($storageDriver, $locales['social'] ?? './locales/social', $pdo);
});

$container->singleton('telegram_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createTelegramRepository($storageDriver, $locales['telegram'] ?? './locales/telegram', $pdo);
});

// Раздел О Проекте
$container->singleton('about_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createContentRepository($storageDriver, $locales['about'], $pdo);
});

$container->singleton('about_service', fn($c) => new LocalizedContentService(
    $c->get('about_repository'),
    $c->get('language_service')
));

//Раздел Принципы
$container->singleton('principles_repository', function ($c) use ($storageDriver) {
    $locales = $c->get('config')['app']['locales'];
    $pdo = $storageDriver === 'database' ? $c->get('pdo') : null;
    return RepositoryFactory::createContentRepository($storageDriver, $locales['principles'], $pdo);
});

$container->singleton('principles_service', fn($c) => new LocalizedContentService(
    $c->get('principles_repository'),
    $c->get('language_service')
));

// Сервисы для панелей
$container->singleton('localized_content', fn($c) => new LocalizedContentService(
    $c->get('content_repository'),
    $c->get('language_service')
));
$container->singleton('event_service', fn($c) => new EventsService($c->get('events_repository')));
$container->singleton('news_service', fn($c) => new NewsService($c->get('news_repository')));

// контроллеры
$container->singleton(App\Http\Controllers\AboutController::class, function ($c) {
    return new App\Http\Controllers\AboutController($c);
});
$container->singleton(App\Http\Controllers\HomeController::class, function ($c) {
    return new App\Http\Controllers\HomeController($c);
});
$container->singleton(App\Http\Controllers\PrinciplesController::class, function ($c) {
    return new App\Http\Controllers\PrinciplesController($c);
});

// Сервис для определения языка запроса
$container->set('request_lang', $_GET['lang'] ?? 'ru');

// Регистрация роутера ---
$container->singleton('router', function ($c) {
    $router = new Router($c);

    // Регистрируем маршруты
    $router->addRoute('home', App\Http\Controllers\HomeController::class);
    $router->addRoute('about', App\Http\Controllers\AboutController::class);
    $router->addRoute('principles', App\Http\Controllers\PrinciplesController::class);
    $router->addRoute('living_book', LivingBookController::class);
    $router->addRoute('auth', App\Http\Controllers\AuthController::class);
    // Пример добавления новой страницы:
    // $router->addRoute('contacts', App\Http\Controllers\ContactsController::class);

    return $router;
});

// Сервис данных сайта
$container->singleton('site_data', function ($c) {
    $lang = $c->get('request_lang');
    return [
        'lang_code' => $lang,
        'translator' => $c->get('localized_content'),
        'events_data' => $c->get('event_service')->getAll($lang),
        'news_data' => $c->get('news_service')->getAll($lang),
        'site_title' => $c->get('localized_content')->translate($lang, 'site_title'),
        'page_title' => $c->get('localized_content')->translate($lang, 'page_title'),
        'description' => $c->get('localized_content')->translate($lang, 'description'),
        'version' => $c->get('localized_content')->translate($lang, 'version'),
    ];
});

// Сервис для подготовки данных страницы
$container->singleton('page_data_service', function ($c) {
    return new App\Application\PageDataService($c);
});

// ContentManager для работы с событиями и новостями
$container->singleton('content_manager', function ($c) {
    return new App\Application\ContentManager(
        $c->get('events_repository'),
        $c->get('news_repository')
    );
});

// Сервисы панелей
$container->singleton('sidebar_widget_factory', function ($c) {
    return new App\Infrastructure\SidebarWidgetFactory($c);
});

$container->singleton('sidebar_manager', function ($c) {
    $config = $c->get('config');
    return new App\Application\SidebarManager(
        $config['sidebars'] ?? [],
        $c->get('sidebar_widget_factory')
    );
});

// Репозиторий пользователей
$container->singleton('user_repository', function ($c) {
    $config = $c->get('config');
    $dbConfig = $config['database'];

    // Если БД активна, пробуем использовать её
    if ($dbConfig['is_active']) {
        try {
            $pdo = $c->get('pdo');
            return new App\Infrastructure\Auth\DatabaseUserRepository($pdo);
        } catch (RuntimeException $e) {
            // Если БД недоступна, используем файловое хранилище
            error_log("БД недоступна, используется файловое хранилище: " . $e->getMessage());
        }
    }

    // Если БД неактивна или недоступна, используем файловое хранилище
    return new App\Infrastructure\Auth\FileBasedUserRepository();
});

// Сервис аутентификации
$container->singleton('auth_service', function ($c) {
    $userRepository = $c->get('user_repository');
    return new App\Infrastructure\Auth\SessionAuthService($userRepository);
});

// Сервис CAPTCHA
$container->singleton('captcha_service', function ($c) {
    return new App\Infrastructure\Security\CaptchaService();
});

// Контроллер аутентификации
$container->singleton(App\Http\Controllers\AuthController::class, function ($c) {
    return new App\Http\Controllers\AuthController($c);
});

// Сервис навигации
$container->singleton('nav_service', function ($c) {
    return new App\Application\NavService($c);
});

// Health check для БД
$container->singleton('database.health_check', function ($c) {
    $connectionManager = $c->get('database.connection_manager');
    return new App\Infrastructure\Database\DatabaseHealthCheck($connectionManager);
});

// Сервис загрузки аватаров
$container->singleton('avatar_service', function ($c) {
    $uploadDir = __DIR__ . '/storage/avatars';

    // Веб-путь: через скрипт-прокладку для безопасности
    $publicUrlPrefix = '/public/get_avatar.php?file=';

    return new App\Infrastructure\Security\AvatarUploadService($uploadDir, $publicUrlPrefix);
});

$container->singleton('csrf_service', static fn (): CsrfService => new CsrfService());
$container->singleton('flash_message_service', static fn (): FlashMessageService => new FlashMessageService());
$container->singleton('book_photo_service', static fn (): BookPhotoUploadService => new BookPhotoUploadService());

$container->singleton('book_repository', static function ($c): BookRepositoryInterface {
    return new DatabaseBookRepository($c->get('pdo'));
});

$container->singleton('living_book_service', static function ($c): LivingBookService {
    return new LivingBookService(
        $c->get('book_repository'),
        $c->get('book_photo_service')
    );
});

$container->singleton(LivingBookController::class, static function ($c): LivingBookController {
    return new LivingBookController($c);
});

return $container;
