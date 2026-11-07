<?php

declare(strict_types=1);

/** @var \App\View\NavData $data */

use App\View\NavData;

$escape = static fn (string $value): string =>
htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$isLoggedIn = $data->nav_service->isUserLoggedIn();
$csrfToken = $data->csrf_token;
?>

<nav class="main-nav">
    <a
            href="?lang=<?= $escape($data->current_lang) ?>"
            <?= $data->current_page === 'home' ? 'class="active"' : '' ?>
    >
        <?= $escape($data->translator->translate($data->current_lang, 'nav_home')) ?>
    </a>

    <a
            href="?page=about&amp;lang=<?= $escape($data->current_lang) ?>"
            <?= $data->current_page === 'about' ? 'class="active"' : '' ?>
    >
        <?= $escape($data->translator->translate($data->current_lang, 'nav_about')) ?>
    </a>

    <a
            href="?page=principles&amp;lang=<?= $escape($data->current_lang) ?>"
            <?= $data->current_page === 'principles' ? 'class="active"' : '' ?>
    >
        <?= $escape($data->translator->translate($data->current_lang, 'nav_principles')) ?>
    </a>

    <a
            href="?page=living_book&amp;lang=<?= $escape($data->current_lang) ?>"
            <?= $data->current_page === 'living_book' ? 'class="active"' : '' ?>
    >
        <?= $escape(
                $data->translator->translate(
                        $data->current_lang,
                        'nav_living_book',
                        'Живая Книга'
                )
        ) ?>
    </a>
    <a
            href="?page=books&amp;lang=<?= $escape($data->current_lang) ?>"
            <?= $data->current_page === 'books' ? 'class="active"' : '' ?>
    >
        Книги
    </a>

    <?php if ($isLoggedIn): ?>
        <a
                href="?page=auth&amp;action=profile&amp;lang=<?= $escape($data->current_lang) ?>"
                <?= $data->current_page === 'auth' ? 'class="active"' : '' ?>
        >
            <?= $escape($data->translator->translate($data->current_lang, 'profile')) ?>
        </a>

        <form
                method="post"
                action="?page=auth&amp;action=logout&amp;lang=<?= $escape($data->current_lang) ?>"
                class="nav-logout-form"
        >
            <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escape($csrfToken) ?>"
            >
            <button type="submit" class="nav-logout-button">
                <?= $escape($data->translator->translate($data->current_lang, 'logout')) ?>
            </button>
        </form>
    <?php else: ?>
        <a
                href="?page=auth&amp;action=login&amp;lang=<?= $escape($data->current_lang) ?>"
                <?= $data->current_page === 'auth' ? 'class="active"' : '' ?>
        >
            <?= $escape($data->translator->translate($data->current_lang, 'login')) ?>
        </a>
    <?php endif; ?>
</nav>