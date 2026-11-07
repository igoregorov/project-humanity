<?php
// layout.php
// Ожидаем, что $layoutData и $contentHtml будут переданы сюда из ViewRenderer
// extract() в render() уже передал переменные

/** @var array $layoutData */ // Данные для layout
/** @var string $contentHtml */ // Результат рендеринга основного шаблона (main_content.php и т.п.)

// Извлекаем данные из $layoutData
$lang_code = (string) ($layoutData['lang_code'] ?? 'ru');
$site_title = (string) ($layoutData['site_title'] ?? '');
$page_title = (string) ($layoutData['page_title'] ?? '');
$description = (string) ($layoutData['description'] ?? '');
$version = (string) ($layoutData['version'] ?? '1.0');
$page = (string) ($layoutData['page'] ?? 'home');

// Извлекаем готовые HTML строки для компонентов (старая система)
$navHtml = $layoutData['nav_html'] ?? '';
$footerHtml = $layoutData['footer_html'] ?? '';

// Новые переменные для системы сайдбаров
$left_sidebar_html = $layoutData['left_sidebar_html'] ?? '';
$right_sidebar_html = $layoutData['right_sidebar_html'] ?? '';
$has_left_sidebar = $layoutData['has_left_sidebar'] ?? false;
$has_right_sidebar = $layoutData['has_right_sidebar'] ?? false;

// Сохраняем текущие query-параметры при переключении языка.
// Параметр lang всегда перезаписывается.
$queryParams = $_GET;
unset($queryParams['lang']);
$queryParams['page'] = $page;

$ruHref = '?' . http_build_query(array_merge($queryParams, ['lang' => 'ru']));
$enHref = '?' . http_build_query(array_merge($queryParams, ['lang' => 'en']));

$escapeAttr = static fn (string $value): string =>
htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// Формируем canonical URL. Принудительно используем основной домен для защиты от клонов в SEO.
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$canonicalUrl = 'https://projecthumanity.space' . $escapeAttr($requestUri);
?>
<!DOCTYPE html>
<html lang="<?= $escapeAttr($lang_code) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escapeAttr($page_title) ?> — <?= $escapeAttr($site_title) ?></title>
    <meta name="description" content="<?= $escapeAttr($description) ?>">
    <link rel="canonical" href="<?= $canonicalUrl ?>">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/styles/styles.css">
    <link rel="stylesheet" href="/styles/living-book-graph.css">
</head>
<body>
<div class="container">
    <header>
        <h1><?= $escapeAttr($site_title) ?></h1>
        <p class="tagline"><?= $escapeAttr($description) ?></p>
        <p><small><?= $escapeAttr($version) ?></small></p>
        <?= $navHtml ?>
        <div class="lang-switch">
            <a href="<?= $escapeAttr($ruHref) ?>"<?= $lang_code === 'ru' ? ' style="opacity: 1;"' : '' ?>>RU</a>
            <a href="<?= $escapeAttr($enHref) ?>"<?= $lang_code === 'en' ? ' style="opacity: 1;"' : '' ?>>EN</a>
        </div>
    </header>
    <div class="layout">
        <!-- Левая панель: сначала проверяем новую систему, потом старую -->
        <?php if ($has_left_sidebar && !empty($left_sidebar_html)): ?>
            <aside class="sidebar sidebar-left">
                <?= $left_sidebar_html ?>
            </aside>
        <?php endif; ?>
        <main class="main-content">
            <?= $contentHtml ?>
        </main>
        <!-- Правая панель: сначала проверяем новую систему, потом старую -->
        <?php if ($has_right_sidebar && !empty($right_sidebar_html)): ?>
            <aside class="sidebar sidebar-right">
                <?= $right_sidebar_html ?>
            </aside>
        <?php endif; ?>
    </div>
    <?= $footerHtml ?>
</div>
<script src="/scripts/vis-network.min.js" defer></script>
<script src="/scripts/living-book-graph.js" defer></script>
<script src="/scripts/avatar.js" defer></script>
</body>
</html>