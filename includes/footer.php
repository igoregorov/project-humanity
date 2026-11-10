<?php
declare(strict_types=1);

/** @var \App\View\FooterData $data */

$escape = static fn (string $value): string =>
htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<footer>
    <p>&copy; <?= date('Y') ?> <?= $escape($data->site_title) ?>. <?= $data->translator->translate($data->lang_code, 'copyright') ?></p>
    <p><?= $data->translator->translate($data->lang_code, 'made_with') ?></p>
    <p class="footer-licenses">
        <a href="?page=license&amp;lang=<?= $escape($data->lang_code) ?>">
            <?= $data->translator->translate($data->lang_code, 'licenses_link', 'Licenses / Лицензии') ?>
        </a>
    </p>
</footer>