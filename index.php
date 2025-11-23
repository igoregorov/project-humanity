<?php
declare(strict_types=1);
// index.php

use App\View\TemplateDataInterface;
use App\View\FooterData;
use App\View\MainContentData;
use App\View\SidebarLeftData;
use App\View\SidebarRightData;

$container = require_once 'container.php';
$debugMode = $container->get('config')['app']['debug'];

// --- ТЕСТ ПОДКЛЮЧЕНИЯ К БД (временно, для проверки) ---
if ($debugMode) {
    $pdo = $container->get('pdo');
    try {
        $stmt = $pdo->query("SELECT 1 as test_connection");
        $result = $stmt->fetch();
        if ($result && $result['test_connection'] == 1) {
            echo "<!-- Подключение к БД успешно -->\n";
        } else {
            echo "<!-- Ошибка подключения к БД: не удалось выполнить тестовый запрос -->\n";
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT AUTO_INCREMENT PRIMARY KEY, data VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");

        $insertSql = "INSERT INTO test_table (data) VALUES (?)";
        $stmt = $pdo->prepare($insertSql);
        $testData = "Test entry from index.php on " . date('Y-m-d H:i:s');
        $stmt->execute([$testData]);

        $selectSql = "SELECT id, data, created_at FROM test_table ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->query($selectSql);
        $lastEntry = $stmt->fetch();

        if ($lastEntry) {
            echo "<!-- Запись в БД успешна: ID {$lastEntry['id']}, Data: {$lastEntry['data']} -->\n";
        } else {
            echo "<!-- Ошибка записи/чтения в БД -->\n";
        }

    } catch (PDOException $e) {
        echo "<!-- Ошибка работы с БД: " . $e->getMessage() . " -->\n";
    }
}
// --- КОНЕЦ ТЕСТА ПОДКЛЮЧЕНИЯ ---

function renderTemplate(string $templateFile, TemplateDataInterface $data): void
{
    include $templateFile;
}

$siteData = $container->get('site_data');
$translator = $siteData['translator'];
$lang_code = $siteData['lang_code'];
$upcoming_events = $siteData['events_data'];
$news_items = $siteData['news_data'];
$site_title = $siteData['site_title'];
$page_title = $siteData['page_title'];
$description = $siteData['description'];
$version = $siteData['version'];

?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — <?= htmlspecialchars($site_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <header>
        <h1><?= htmlspecialchars($site_title) ?></h1>
        <p class="tagline"><?= htmlspecialchars($description) ?></p>
        <p><small><?= htmlspecialchars($version) ?></small></p>

        <div class="lang-switch">
            <a href="?lang=ru" <?php if ($lang_code === 'ru') echo 'style="opacity: 1;"'; ?>>RU</a>
            <a href="?lang=en" <?php if ($lang_code === 'en') echo 'style="opacity: 1;"'; ?>>EN</a>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar sidebar-left">
            <?php renderTemplate('includes/sidebar_left.php', new SidebarLeftData(
                translator: $translator,
                upcoming_events: $upcoming_events,
                lang_code: $lang_code
            )); ?>
        </aside>

        <main class="main-content">
            <?php renderTemplate('includes/main_content.php', new MainContentData(
                translator: $translator,
                lang_code: $lang_code
            )); ?>
        </main>

        <aside class="sidebar sidebar-right">
            <?php renderTemplate('includes/sidebar_right.php', new SidebarRightData(
                translator: $translator,
                news_items: $news_items,
                lang_code: $lang_code
            )); ?>
        </aside>
    </div>

    <?php renderTemplate('includes/footer.php', new FooterData(
        site_title: $site_title,
        translator: $translator,
        lang_code: $lang_code
    )); ?>

</div>

</body>
</html>