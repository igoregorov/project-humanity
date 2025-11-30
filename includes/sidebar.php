<?php
// includes/sidebar.php

declare(strict_types=1);

/** @var App\View\SidebarData $data */
$content = $data->content;
$position = $data->position;
?>

    <!-- Виджеты рендерятся без внешнего div.sidebar - он уже есть в layout.php -->
<?php if ($content['type'] === 'events'): ?>
    <div class="sidebar-widget events-widget">
        <h3><?= htmlspecialchars($content['title'] ?? 'Предстоящие события') ?></h3>
        <?php if (!empty($content['events'])): ?>
            <div class="events-list">
                <?php foreach (array_slice($content['events'], 0, $content['max_items'] ?? 5) as $event): ?>
                    <div class="event-item">
                        <div class="event-date"><?= htmlspecialchars($event['date'] ?? '') ?></div>
                        <div class="event-title"><?= htmlspecialchars($event['title'] ?? '') ?></div>
                        <?php if (isset($event['description'])): ?>
                            <div class="event-description"><?= htmlspecialchars($event['description']) ?></div>
                        <?php endif; ?>
                        <?php if (isset($event['type'])): ?>
                            <div class="event-type event-type-<?= htmlspecialchars($event['type']) ?>">
                                <?= htmlspecialchars($event['type']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><em><?= htmlspecialchars($content['no_content_message'] ?? 'Пока нет предстоящих событий') ?></em></p>
        <?php endif; ?>
    </div>

<?php elseif ($content['type'] === 'news'): ?>
    <div class="sidebar-widget news-widget">
        <h3><?= htmlspecialchars($content['title'] ?? 'Последние новости') ?></h3>
        <?php if (!empty($content['news'])): ?>
            <div class="news-list">
                <?php foreach (array_slice($content['news'], 0, $content['max_items'] ?? 3) as $news): ?>
                    <div class="news-item">
                        <div class="news-date"><?= htmlspecialchars($news['date'] ?? '') ?></div>
                        <div class="news-title"><?= htmlspecialchars($news['title'] ?? '') ?></div>
                        <?php if (isset($news['description'])): ?>
                            <div class="news-description"><?= htmlspecialchars($news['description']) ?></div>
                        <?php endif; ?>
                        <?php if (isset($news['type'])): ?>
                            <div class="news-type news-type-<?= htmlspecialchars($news['type']) ?>">
                                <?= htmlspecialchars($news['type']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><em><?= htmlspecialchars($content['no_content_message'] ?? 'Пока нет новостей') ?></em></p>
        <?php endif; ?>
    </div>

<?php elseif ($content['type'] === 'timeline'): ?>
    <div class="sidebar-widget timeline-widget">
        <h3><?= htmlspecialchars($content['title'] ?? 'История развития') ?></h3>
        <?php if (!empty($content['events'])): ?>
            <div class="timeline">
                <?php foreach ($content['events'] as $event): ?>
                    <div class="timeline-event">
                        <div class="timeline-year"><?= htmlspecialchars($event['year'] ?? '') ?></div>
                        <div class="timeline-description"><?= htmlspecialchars($event['description'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><em><?= htmlspecialchars($content['no_content_message'] ?? 'Нет данных для отображения') ?></em></p>
        <?php endif; ?>
    </div>

<?php elseif ($content['type'] === 'social'): ?>
    <div class="sidebar-widget social-widget">
        <h3><?= htmlspecialchars($content['title'] ?? 'Мы в соцсетях') ?></h3>
        <?php if (!empty($content['networks'])): ?>
            <div class="social-links">
                <?php foreach ($content['networks'] as $network): ?>
                    <a href="#" class="social-link social-<?= htmlspecialchars($network) ?>">
                        <?= htmlspecialchars(ucfirst($network)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><em><?= htmlspecialchars($content['no_content_message'] ?? 'Нет доступных соцсетей') ?></em></p>
        <?php endif; ?>
    </div>

<?php elseif ($content['type'] === 'telegram'): ?>
    <div class="sidebar-widget telegram-widget">
        <h3><?= htmlspecialchars($content['title'] ?? 'Telegram канал') ?></h3>
        <?php if (!empty($content['posts'])): ?>
            <div class="telegram-posts">
                <?php foreach (array_slice($content['posts'], 0, 3) as $post): ?>
                    <div class="telegram-post">
                        <div class="post-text"><?= htmlspecialchars($post['text'] ?? 'Пример поста из Telegram') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><em><?= htmlspecialchars($content['no_content_message'] ?? 'Нет постов для отображения') ?></em></p>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($content['channel_url'] ?? '#') ?>" target="_blank" class="telegram-link">
            Подписаться в Telegram
        </a>
    </div>
<?php endif; ?>