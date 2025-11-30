<?php
// layout.php
// Ожидаем, что $layoutData и $contentHtml будут переданы сюда из ViewRenderer
// extract() в render() уже передал переменные

/** @var array $layoutData */ // Данные для layout
/** @var string $contentHtml */ // Результат рендеринга основного шаблона (main_content.php и т.п.)

// Извлекаем данные из $layoutData
$lang_code = $layoutData['lang_code'] ?? 'ru';
$site_title = $layoutData['site_title'] ?? '';
$page_title = $layoutData['page_title'] ?? '';
$description = $layoutData['description'] ?? '';
$version = $layoutData['version'] ?? '1.0';
$page = $layoutData['page'] ?? 'home';

// Извлекаем готовые HTML строки для компонентов (старая система)
$navHtml = $layoutData['nav_html'] ?? '';
$footerHtml = $layoutData['footer_html'] ?? '';

// Новые переменные для системы сайдбаров
$left_sidebar_html = $layoutData['left_sidebar_html'] ?? '';
$right_sidebar_html = $layoutData['right_sidebar_html'] ?? '';
$has_left_sidebar = $layoutData['has_left_sidebar'] ?? false;
$has_right_sidebar = $layoutData['has_right_sidebar'] ?? false;
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — <?= htmlspecialchars($site_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/styles.css"> <!-- Убедись, что путь корректен -->
</head>
<body>
<div class="container">
    <header>
        <h1><?= htmlspecialchars($site_title) ?></h1>
        <p class="tagline"><?= htmlspecialchars($description) ?></p>
        <p><small><?= htmlspecialchars($version) ?></small></p>
        <?= $navHtml ?>
        <div class="lang-switch">
            <a href="?page=<?= $page ?>&lang=ru"<?= $lang_code === 'ru' ? ' style="opacity: 1;"' : '' ?>>RU</a>
            <a href="?page=<?= $page ?>&lang=en"<?= $lang_code === 'en' ? ' style="opacity: 1;"' : '' ?>>EN</a>
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
</body>
</html>