<?php
declare(strict_types=1);
// main_content.php
/** @var \App\View\MainContentData $data */

use App\View\MainContentData;
?>

<section class="mission">
    <h2><?= $data->translator->translate($data->lang_code, 'mission') ?></h2>
    <?php $mission_text = $data->translator->translateArray($data->lang_code, 'mission_text'); ?>
    <p><?= $mission_text[0] ?? '' ?></p>
    <p><?= $mission_text[1] ?? '' ?></p>
    <p><?= $mission_text[2] ?? '' ?></p>
</section>

<section>
    <h2><?= htmlspecialchars($data->translator->translate($data->lang_code, 'principles')) ?></h2>
    <?php $principles = $data->translator->translateArray($data->lang_code, 'principles_list'); ?>
    <?php if (!empty($principles)): ?>
        <ul>
            <?php foreach ($principles as $item): ?>
                <li><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><em>Ошибка: данные для принципов не являются списком.</em></p>
    <?php endif; ?>
</section>

<section>
    <h2><?= $data->translator->translate($data->lang_code, 'join') ?></h2>
    <?php $join_text = $data->translator->translateArray($data->lang_code, 'join_text'); ?>
    <p><?= $join_text[0] ?? '' ?></p>
    <p><?= $join_text[1] ?? '' ?></p>
    <p>
        <a href="mailto:hello@projecthumanity.space"><?= $data->translator->translate($data->lang_code, 'contact') ?></a> •
        <a href="https://t.me/projecthumanity_space" target="_blank" rel="noopener noreferrer">
            <?= $data->translator->translate($data->lang_code, 'telegram') ?>
        </a>
    </p>
</section>