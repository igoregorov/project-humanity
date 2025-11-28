<?php
declare(strict_types=1);
/** @var NavData $data */
use App\View\NavData;
?>

<nav class="main-nav">
    <a href="?lang=<?= htmlspecialchars($data->current_lang) ?>"<?= $data->current_page === 'home' ? ' class="active"' : '' ?>>
        <?= htmlspecialchars($data->translator->translate($data->current_lang, 'nav_home')) ?>
    </a>
    <a href="?page=about&lang=<?= htmlspecialchars($data->current_lang) ?>"<?= $data->current_page === 'about' ? ' class="active"' : '' ?>>
        <?= htmlspecialchars($data->translator->translate($data->current_lang, 'nav_about')) ?>
    </a>
    <a href="?page=principles&lang=<?= htmlspecialchars($data->current_lang) ?>"<?= $data->current_page === 'principles' ? ' class="active"' : '' ?>>
        <?= htmlspecialchars($data->translator->translate($data->current_lang, 'nav_principles')) ?>
    </a>
</nav>