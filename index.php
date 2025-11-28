<?php
declare(strict_types=1);
// index.php

// ——— Безопасность среды ———
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', 0); // Всегда 0 в продакшене
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
use JetBrains\PhpStorm\NoReturn;

$debugMode = false;

// --- ФУНКЦИЯ ДЛЯ ОТПРАВКИ КРАСИВОЙ СТРАНИЦЫ ОШИБКИ (всегда с дебагом) ---
#[NoReturn] function sendCleanErrorPage(string $message = '', string $file = '', string $line = '', string $trace = ''): void
{
    // Очищаем буфер вывода, если что-то уже было отправлено
    if (ob_get_level()) {
        ob_clean();
    }

    // Устанавливаем заголовок 500
    http_response_code(500);

    // Устанавливаем заголовок Content-Type
    header('Content-Type: text/html; charset=utf-8');

    // --- КРАСИВАЯ СТРАНИЦА ОШИБКИ С ОТЛАДКОЙ ---
    $html = '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка сервера</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .error-container { background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; width: 90%; }
            h1 { color: #d32f2f; margin-top: 0; }
            .error-details { background-color: #f9f9f9; padding: 1rem; border-radius: 4px; margin-top: 1rem; font-family: monospace; white-space: pre-wrap; overflow-x: auto; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Произошла ошибка при генерации страницы</h1>
            <p>Пожалуйста, сообщите об этом администратору.</p>';

    // --- ВСЕГДА ПОКАЗЫВАЕМ ДЕТАЛИ ---
    $html .= '<div class="error-details">';
    $html .= "<strong>Ошибка:</strong> " . htmlspecialchars($message) . "<br/>";
    if ($file) {
        $html .= "<strong>Файл:</strong> " . htmlspecialchars($file) . "<br/>";
    }
    if ($line) {
        $html .= "<strong>Строка:</strong> " . htmlspecialchars($line) . "<br/>";
    }
    if ($trace) {
        $html .= "<strong>Трассировка стека:</strong><br/>" . htmlspecialchars($trace);
    }
    $html .= '</div>';

    $html .= '
        </div>
    </body>
    </html>';

    echo $html;
    exit(1);
}

// --- ОПРЕДЕЛЯЕМ ОБРАБОТЧИК ИСКЛЮЧЕНИЙ ---
set_exception_handler(function (Throwable $e) use (&$config) {
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());

    // Вызываем sendCleanErrorPage ВСЕГДА с деталями
    sendCleanErrorPage($e->getMessage(), $e->getFile(), (string) $e->getLine(), $e->getTraceAsString());
});

// --- ОПРЕДЕЛЯЕМ ОБРАБОТЧИК ОШИБОК PHP ---
set_error_handler(/**
 * @throws ErrorException
 */ function ($severity, $message, $file, $line) {
    error_log("PHP Error caught by set_error_handler: $severity - $message in $file:$line");
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// --- ВКЛЮЧАЕМ БУФЕРИЗАЦИЮ ВЫВОДА СРАЗУ ПОСЛЕ ЗАГОЛОВКОВ ---
ob_start();

try {
    $container = require_once 'container.php';

    if (!isset($container)) {
        throw new RuntimeException('Application configuration error: Container is not set.');
    }

    require_once 'includes/helpers.php';
    $config = $container->get('config');

    if (!is_array($config)) {
        $configType = gettype($config);
        $errorMessage = "Config file did not return an array. Returned type: $configType. Value: " . var_export($config, true);
        error_log("Critical Config Error: $errorMessage");
        throw new RuntimeException($errorMessage);
    }

    $debugMode = $config['app']['debug'] ?? false;
    $version = $config['version'] ?? 'Unknown';

    function renderTemplate(string $templateFile, TemplateDataInterface $data): void
    {
        $allowedPaths = ['includes/'];

        foreach ($allowedPaths as $path) {
            if (str_starts_with($templateFile, $path)) {
                break;
            }
            throw new InvalidArgumentException("Invalid template path");
        }

        $realTemplateFile = realpath($templateFile);
        $realBasePath = realpath(__DIR__ . '/includes/');

        if (!$realTemplateFile || !$realBasePath || !str_starts_with($realTemplateFile, $realBasePath)) {
            throw new InvalidArgumentException("Invalid template path");
        }

        include $templateFile;
    }

    $page = $_GET['page'] ?? 'home';
    $allowedPages = $config['app']['allowedPages'];
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
    // --- ГЕНЕРАЦИЯ HTML ---
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
    <?php

} catch (Throwable $e) {
    $currentDebugMode = false;
    if (isset($config) && is_array($config) && isset($config['app']['debug'])) {
        $currentDebugMode = $config['app']['debug'];
    }

    error_log("Exception in main try block (after config check): " . $e->getMessage());
    // Важно: вызываем функцию, которая очистит буфер и выведет только нашу страницу ошибки
    sendCleanErrorPage($e->getMessage(), $e->getFile(), (string) $e->getLine(), $e->getTraceAsString());
}

// ob_end_flush() отправит накопленный буфер в браузер.
if (ob_get_level()) {
    ob_end_flush();
}