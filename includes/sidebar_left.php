<?php
declare(strict_types=1);
/** @var SidebarLeftData $data */

use App\View\SidebarLeftData;

?>
    <h3><?= $data->translator->translate($data->lang_code, 'upcoming_events') ?></h3>
<?php if (!empty($data->upcoming_events)): ?>
    <ul class="timeline-list">
        <?php foreach ($data->upcoming_events as $event): ?>
            <li class="timeline-item">
                <time datetime="<?= $event['date'] ?>"><?= date('d.m.Y', strtotime($event['date'])) ?></time>
                <strong><?= htmlspecialchars($event['title']) ?></strong>
                <p><?= htmlspecialchars($event['description']) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><em><?= $data->translator->translate($data->lang_code, 'no_upcoming_events') ?></em></p>
<?php endif; ?>