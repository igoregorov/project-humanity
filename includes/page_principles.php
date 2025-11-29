<?php
declare(strict_types=1);
/** @var PagePrinciplesData $data */
use App\View\PagePrinciplesData;
require_once __DIR__ . '/helpers.php';
?>

<section class="page-principles">
    <h2><?= htmlspecialchars($data->translator->translate($data->lang_code, 'title')) ?></h2>
    <?php $principles = $data->translator->translateArray($data->lang_code, 'principles_list'); ?>
    <div class="principles-list-wrapper">
        <?php foreach ($principles as $block): ?>
            <div class="principle-item">
                <?= sanitizeHtml($block) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>