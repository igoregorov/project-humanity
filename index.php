<?php
declare(strict_types=1);
// index.php

// ——— Безопасность среды ———
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    // ——— Заголовки безопасности ———
    header("Content-Type: text/html; charset=UTF-8");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

// Безопасная инициализация сессии
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
    session_regenerate_id(true);
}

use App\View\NavData;
use App\View\PageAboutData;
use App\View\PagePrinciplesData;
use App\View\TemplateDataInterface;
use App\View\FooterData;
use App\View\MainContentData;
use App\View\SidebarLeftData;
use App\View\SidebarRightData;

$container = require_once 'container.php';

if (!isset($container)) {
    header('Content-Type: text/plain; charset=utf-8');
    die('Application configuration error');
}
require_once 'includes/helpers.php';

$config = $container->get('config');
$debugMode = $config['app']['debug'];

function renderTemplate(string $templateFile, TemplateDataInterface $data): void
{
    $allowedPaths = ['includes/'];
    $isSafe = false;

    foreach ($allowedPaths as $path) {
        if (str_starts_with($templateFile, $path)) {
            $isSafe = true;
            break;
        }
    }

    $realTemplateFile = realpath($templateFile);
    $realBasePath = realpath(__DIR__ . '/includes/');

    if (!$isSafe || !$realTemplateFile || !$realBasePath || !str_starts_with($realTemplateFile, $realBasePath)) {
        throw new InvalidArgumentException("Invalid template path");
    }

    include $templateFile;
}

set_exception_handler(function (Throwable $e) use ($debugMode) {
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);

    if ($debugMode) {
        echo "<pre>Exception: " . htmlspecialchars($e->getMessage()) . "</pre>";
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Система временно недоступна.";
    }
    exit(1);
});

$page = $_GET['page'] ?? 'home';
$allowedPages = ['home', 'about', 'principles'];
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

$siteData = $container->get('site_data');
$translator = $siteData['translator'];
$lang_code = $siteData['lang_code'];
$upcoming_events = $siteData['events_data'];
$news_items = $siteData['news_data'];
$current_lang = $siteData['lang_code'];
$page_title_map = [
    'home' => $translator->translate($current_lang, 'page_title'),
    'about' => $translator->translate($current_lang, 'about_title'),
    'principles' => $translator->translate($current_lang, 'principles_title'),
];
$site_title = $siteData['site_title'];
$page_title = $siteData['page_title'];
$description = $siteData['description'];
$version = $siteData['version'];

?>

<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — <?= htmlspecialchars($site_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <header>
        <h1><?= htmlspecialchars($site_title) ?></h1>
        <p class="tagline"><?= htmlspecialchars($description) ?></p>
        <p><small><?= htmlspecialchars($version) ?></small></p>

        <?php renderTemplate('includes/nav.php', new NavData(
            current_lang: $current_lang,
            current_page: $page,
            translator: $translator
        )); ?>

        <div class="lang-switch">
            <a href="?page=<?= $page ?>&lang=ru"<?= $current_lang === 'ru' ? ' style="opacity: 1;"' : '' ?>>RU</a>
            <a href="?page=<?= $page ?>&lang=en"<?= $current_lang === 'en' ? ' style="opacity: 1;"' : '' ?>>EN</a>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar sidebar-left">
            <?php renderTemplate('includes/sidebar_left.php', new SidebarLeftData(
                translator: $translator,
                upcoming_events: $upcoming_events,
                lang_code: $current_lang
            )); ?>
        </aside>

        <main class="main-content">
            <?php
            switch ($page) {
                case 'about':
                    $about_service = $container->get('about_service');
                    renderTemplate('includes/page_about.php', new PageAboutData(
                        translator: $about_service,
                        lang_code: $current_lang
                    ));
                    break;
                case 'principles':
                    $principles_service = $container->get('principles_service');
                    renderTemplate('includes/page_principles.php', new PagePrinciplesData(
                        translator: $principles_service,
                        lang_code: $current_lang
                    ));
                    break;
                default:
                    renderTemplate('includes/main_content.php', new MainContentData(
                        translator: $translator,
                        lang_code: $current_lang
                    ));
            }
            ?>
        </main>

        <aside class="sidebar sidebar-right">
            <?php renderTemplate('includes/sidebar_right.php', new SidebarRightData(
                translator: $translator,
                news_items: $news_items,
                lang_code: $current_lang
            )); ?>
        </aside>
    </div>

    <?php renderTemplate('includes/footer.php', new FooterData(
        site_title: $site_title,
        translator: $translator,
        lang_code: $current_lang
    )); ?>

</div>

</body>
</html>