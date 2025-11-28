<?php
declare(strict_types=1);
/** @var \App\View\PageAboutData $data */
use App\View\PageAboutData;
?>

<section class="page-about">
    <h1><?= htmlspecialchars($data->translator->translate($data->lang_code, 'title')) ?></h1>
    <?php $content = $data->translator->translateArray($data->lang_code, 'content'); ?>
    <?php foreach ($content as $block): ?>
        <?= sanitizeHtml($block) ?>
    <?php endforeach; ?>
</section>