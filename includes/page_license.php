<?php
declare(strict_types=1);

/** @var \App\View\PageLicenseData $data */
require_once __DIR__ . '/helpers.php';

$escape = static fn (string $value): string =>
htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// Локальная карта меток для навигации по документам.
// Не используем локализацию из контейнера в шаблоне — держим просто и явно.
$docLabels = [
    'overview' => 'Overview / Обзор',
    'book'     => 'Book / Книга',
    'site'     => 'Website / Сайт',
    'code'     => 'Source Code / Код',
    'dmca'     => 'DMCA / Авторские права',
];
?>

<section class="page-license">
    <h1><?= $escape($data->title) ?></h1>

    <nav class="license-nav" aria-label="License documents">
        <ul>
            <?php foreach ($docLabels as $key => $label): ?>
                <li>
                    <?php if ($data->doc === $key): ?>
                        <strong aria-current="page"><?= $escape($label) ?></strong>
                    <?php else: ?>
                        <a href="?page=license&amp;doc=<?= $escape($key) ?>&amp;lang=<?= $escape($data->lang_code) ?>">
                            <?= $escape($label) ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="license-content">
        <?= sanitizeHtml($data->contentHtml) ?>
    </div>
</section>