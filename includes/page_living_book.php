<?php

declare(strict_types=1);

/** @var \App\View\LivingBookData $data */
$data = $data->toArray();

$translator = $data['translator'] ?? null;
$lang = (string) ($data['lang_code'] ?? 'ru');
$mode = (string) ($data['mode'] ?? 'recent');
$filterNumber = isset($data['filterNumber']) ? (string) $data['filterNumber'] : null;
$entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
$currentUserEntry = is_array($data['currentUserEntry'] ?? null) ? $data['currentUserEntry'] : null;
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : [];
$dbAvailable = (bool) ($data['dbAvailable'] ?? false);
$csrfToken = isset($data['csrfToken']) ? (string) $data['csrfToken'] : '';

$graphDataUrl = '?page=living_book&action=graph-data&lang=' . rawurlencode($lang);

$t = static function (string $key) use ($translator, $lang): string {
    if (is_object($translator) && method_exists($translator, 'translate')) {
        return (string) $translator->translate($lang, $key);
    }

    return $key;
};

$e = static function (mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

    <h1><?= $e($t('living_book_title')) ?></h1>
    <p><?= $e($t('living_book_subtitle')) ?></p>

<?php if (!empty($flash['success'])): ?>
    <div class="alert alert-success">
        <?= $e($t((string) $flash['success'])) ?>
    </div>
<?php endif; ?>

<?php if (!empty($flash['errors']) && is_array($flash['errors'])): ?>
    <?php foreach ($flash['errors'] as $errorKey): ?>
        <div class="alert alert-error">
            <?= $e($t((string) $errorKey)) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!$dbAvailable): ?>
    <p><?= $e($t('living_book_db_unavailable')) ?></p>
<?php else: ?>

    <form method="get" action="?page=living_book" class="living-book-filter">
        <input type="hidden" name="page" value="living_book">
        <input type="hidden" name="lang" value="<?= $e($lang) ?>">

        <label for="living-book-filter-input">
            <?= $e($t('living_book_filter_label')) ?>
        </label>

        <input
            id="living-book-filter-input"
            type="text"
            name="filter"
            value="<?= $e($filterNumber ?? '') ?>"
            placeholder="<?= $e($t('living_book_filter_placeholder')) ?>"
            maxlength="100"
        >

        <button type="submit">
            <?= $e($t('living_book_filter_submit')) ?>
        </button>

        <a href="?page=living_book&lang=<?= $e($lang) ?>">
            <?= $e($t('living_book_show_all')) ?>
        </a>
    </form>

    <?php if ($mode === 'form'): ?>

        <h2><?= $e($t('living_book_form_title')) ?></h2>

        <form
            method="post"
            action="?page=living_book&action=create&lang=<?= $e($lang) ?>"
            enctype="multipart/form-data"
            class="living-book-form"
        >
            <input type="hidden" name="page" value="living_book">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="lang" value="<?= $e($lang) ?>">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">

            <label for="living-book-number">
                <?= $e($t('living_book_number_label')) ?>
            </label>
            <input
                id="living-book-number"
                type="text"
                name="unique_number"
                value="<?= $e($flash['old']['unique_number'] ?? '') ?>"
                maxlength="100"
                required
            >

            <label for="living-book-text">
                <?= $e($t('living_book_response_label')) ?>
            </label>
            <textarea
                id="living-book-text"
                name="text_response"
                maxlength="5000"
                rows="8"
                required
            ><?= $e($flash['old']['text_response'] ?? '') ?></textarea>

            <label for="living-book-photo">
                <?= $e($t('living_book_photo_label')) ?>
            </label>
            <input
                id="living-book-photo"
                type="file"
                name="photo"
                accept="image/jpeg"
            >

            <p class="hint">
                <?= $e($t('living_book_photo_hint')) ?>
            </p>

            <p class="warning">
                <?= $e($t('living_book_public_warning')) ?>
            </p>

            <button type="submit">
                <?= $e($t('living_book_submit')) ?>
            </button>
        </form>

    <?php else: ?>

        <?php if ($mode === 'history' && $filterNumber !== null && $filterNumber !== ''): ?>
            <h2>
                <?= $e($t('living_book_history_title')) ?>:
                <?= $e($filterNumber) ?>
            </h2>
        <?php elseif ($mode === 'recent'): ?>
            <h2><?= $e($t('living_book_recent_title')) ?></h2>
        <?php endif; ?>

        <section class="living-book-graph-section" aria-labelledby="living-book-graph-title">
            <h2 id="living-book-graph-title">Граф экземпляров и откликов</h2>

            <ul class="living-book-graph-legend" aria-label="Легенда графа">
                <li><span class="living-book-legend-book" aria-hidden="true"></span> Книга</li>
                <li><span class="living-book-legend-entry" aria-hidden="true"></span> Отклик</li>
                <li><span class="living-book-legend-edge" aria-hidden="true"></span> Последовательность откликов</li>
            </ul>

            <p class="living-book-graph-status" data-graph-status role="status" aria-live="polite">
                Граф загружается…
            </p>

            <div class="living-book-graph" data-living-book-graph
                 data-graph-url="<?= $e($graphDataUrl) ?>"
                 aria-label="Интерактивный граф книг и откликов"></div>

            <section class="living-book-node-details" data-node-details aria-live="polite">
                <h3>Детали узла</h3>
                <p data-node-details-content>Выберите книгу или отклик на графе.</p>
            </section>
        </section>

        <?php if (empty($entries)): ?>
            <p><?= $e($t('living_book_empty')) ?></p>
        <?php else: ?>
            <div class="living-book-grid">
                <?php foreach ($entries as $entry): ?>
                    <?php
                    $entryId = (int) ($entry['id'] ?? 0);
                    $isCurrentUserEntry = $currentUserEntry !== null
                        && $entryId === (int) ($currentUserEntry['id'] ?? 0);
                    ?>
                    <article class="living-book-card<?= $isCurrentUserEntry ? ' living-book-card--mine' : '' ?>">
                        <header>
                            <div>
                                <strong><?= $e($t('living_book_number_label')) ?>:</strong>
                                <?= $e($entry['book_number'] ?? '') ?>
                            </div>

                            <div>
                                <strong><?= $e($t('living_book_owner')) ?>:</strong>
                                <?= $e($entry['username'] ?? '') ?: '—' ?>
                            </div>

                            <?php if (($entry['previous_entry_id'] ?? null) !== null): ?>
                                <div>
                                    <strong><?= $e($t('living_book_previous_owner')) ?>:</strong>
                                    <?= $e($entry['previous_username'] ?? '') ?: '—' ?>
                                </div>
                            <?php endif; ?>

                            <time datetime="<?= $e($entry['created_at'] ?? '') ?>">
                                <?= $e($entry['created_at'] ?? '') ?>
                            </time>

                            <?php if ($isCurrentUserEntry): ?>
                                <div class="badge">
                                    <?= $e($t('living_book_your_entry')) ?>
                                </div>
                            <?php endif; ?>
                        </header>

                        <div class="living-book-text">
                            <?= nl2br($e($entry['text_response'] ?? '')) ?>
                        </div>

                        <?php if (!empty($entry['photo_url'])): ?>
                            <figure class="living-book-photo">
                                <img
                                    src="<?= $e($entry['photo_url']) ?>"
                                    alt="<?= $e($entry['book_number'] ?? '') ?>"
                                    loading="lazy"
                                >
                            </figure>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

<?php endif; ?>