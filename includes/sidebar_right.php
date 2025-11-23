<?php
declare(strict_types=1);
/** @var SidebarRightData $data */

use App\View\SidebarRightData;

?>

    <h3><?= $data->translator->translate($data->lang_code, 'news') ?></h3>
<?php if (!empty($data->news_items)): ?>
    <ul class="timeline-list">
        <?php foreach ($data->news_items as $item): ?>
            <li class="timeline-item">
                <time datetime="<?= $item['date'] ?>"><?= date('d.m.Y', strtotime($item['date'])) ?></time>
                <strong><?= htmlspecialchars($item['title']) ?></strong>
                <p><?= htmlspecialchars($item['description']) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><em><?= $data->translator->translate($data->lang_code, 'no_news') ?></em></p>
<?php endif; ?>