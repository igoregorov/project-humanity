<?php
declare(strict_types=1);
// container.php
require_once 'vendor/autoload.php';

use App\Application\LocalizedContentService;
use App\Application\EventsService;
use App\Application\NewsService;
use App\Infrastructure\RepositoryFactory;
use App\Infrastructure\SimpleContainer;
use App\Services\LanguageService;
use App\Http\Router;


// Загружаем конфигурацию
$config = require_once 'config.php';

// Создаём контейнер
$container = new SimpleContainer();

// Регистрируем конфигурацию как СЕРВИС в контейнере
$container->set('config', $config);

// Сервис подключения к БД
$container->singleton('pdo', function ($c) {
    $config = $c->get('config');
    $dbConfig = $config['database'];

    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (Exception $e) {
        // Бросаем исключение вместо die()
        // Это исключение будет "всплывать" до места его перехвата
        throw new RuntimeException("Ошибка подключения к БД: " . $e->getMessage(), 0, $e); // Код 0, предыдущее исключение PDOException
    }
});

// Сервисы для локализации
$container->singleton('language_service', function ($c) {
    $config = $c->get('config')['app'];
    return new LanguageService(
        $config['allowed_languages'],
        $config['languages']
    );
});

// Репозитории
$storageDriver = $config['app']['storage_driver'];

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

// Временные заглушки для отсутствующих сервисов
$container->singleton('timeline_service', function ($c) {
    return new class {
        public function getTimeline(string $lang): array { return []; }
    };
});

$container->singleton('team_service', function ($c) {
    return new class {
        public function getTeamMembers(string $lang): array { return []; }
    };
});

$container->singleton('telegram_service', function ($c) {
    return new class {
        public function getLatestPosts(string $channelId, int $limit): array { return []; }
    };
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

return $container;