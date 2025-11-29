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

// Сервис для определения языка запроса
$container->set('request_lang', $_GET['lang'] ?? 'ru');

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

return $container;