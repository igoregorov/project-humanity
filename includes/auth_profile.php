<?php
declare(strict_types=1);

// includes/auth_profile.php

/** @var \App\View\AuthData $data */
$user = $data->user;
$langCode = (string) $data->lang_code;
$escape = static fn (mixed $value): string =>
htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<section class="profile-section">
    <h2>Профиль пользователя</h2>

    <?php if ($data->message !== null): ?>
        <p class="success-message"><?= $escape($data->message) ?></p>
    <?php endif; ?>

    <?php if (!empty($data->errors['general'])): ?>
        <p class="error-message">
            <?= $escape($data->translator->translate(
                    $langCode,
                    $data->errors['general'],
                    $data->errors['general']
            )) ?>
        </p>
    <?php endif; ?>

    <!-- Блок аватара -->
    <div class="avatar-block">
        <div class="avatar-wrapper">
            <div class="avatar-preview" aria-label="Изменить аватар">
                <?php if ($user->hasAvatar() && $data->avatarUrl): ?>
                    <img src="<?= $escape($data->avatarUrl) ?>"
                         alt="Аватар <?= $escape($user->username) ?>"
                         class="avatar-image">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <span class="avatar-placeholder-icon">📷</span>
                        <span class="avatar-placeholder-text">Загрузить аватар</span>
                    </div>
                <?php endif; ?>

                <div class="avatar-hover-hint">Изменить</div>
            </div>

            <form method="POST"
                  action="?page=auth&amp;action=do_upload_avatar&amp;lang=<?= $escape($langCode) ?>"
                  enctype="multipart/form-data"
                  class="avatar-upload-form">
                <input type="hidden" name="csrf_token" value="<?= $escape($data->csrf_token) ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= 20 * 1024 * 1024 ?>">

                <input type="file"
                       id="avatar"
                       name="avatar"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       class="avatar-file-input"
                        <?= isset($data->errors['avatar']) ? 'aria-invalid="true"' : '' ?>>

                <?php if (isset($data->errors['avatar'])): ?>
                    <span class="field-error"><?= $escape($data->errors['avatar']) ?></span>
                <?php endif; ?>

                <!-- Fallback, если JS отключён -->
                <noscript>
                    <div class="avatar-noscript-fallback">
                        <label for="avatar" class="btn-secondary">Выбрать файл</label>
                        <button type="submit" class="btn-primary">Загрузить</button>
                    </div>
                </noscript>
            </form>
        </div>
    </div>

    <div class="profile-info">
        <div class="profile-field">
            <label>Имя пользователя:</label>
            <span><?= $escape($user->username) ?></span>
        </div>
        <div class="profile-field">
            <label>Email:</label>
            <span><?= $escape($user->email) ?></span>
        </div>
        <div class="profile-field">
            <label>Роль:</label>
            <span><?= $user->isAdmin() ? 'Администратор' : 'Пользователь' ?></span>
        </div>
        <div class="profile-field">
            <label>Дата регистрации:</label>
            <span><?= $user->createdAt->format('d.m.Y H:i') ?></span>
        </div>
        <?php if ($user->lastLogin): ?>
            <div class="profile-field">
                <label>Последний вход:</label>
                <span><?= $user->lastLogin->format('d.m.Y H:i') ?></span>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="living-book-profile">
    <h3>Живая Книга</h3>

    <?php if ($data->bookEntry !== null): ?>
        <div class="living-book-published">
            <p>
                Вы уже опубликовали отклик для экземпляра
                <strong><?= $escape($data->bookEntry->getBookNumber() ?? '') ?></strong>.
            </p>

            <blockquote class="living-book-published-text">
                <?= nl2br($escape($data->bookEntry->getTextResponse())) ?>
            </blockquote>

            <p class="living-book-published-note">
                Запись опубликована. Отклик нельзя изменить или удалить.
            </p>

            <a class="btn-secondary" href="?page=living_book&amp;lang=<?= $escape($langCode) ?>">
                Перейти к Живой Книге
            </a>
        </div>
    <?php else: ?>
        <p class="living-book-intro">
            Добавьте номер экземпляра и свой отклик. Фото страницы можно приложить по желанию.
        </p>

        <form
                method="post"
                action="?page=living_book&amp;action=create&amp;lang=<?= $escape($langCode) ?>"
                enctype="multipart/form-data"
                class="living-book-form"
        >
            <input type="hidden" name="csrf_token" value="<?= $escape($data->csrf_token) ?>">

            <label for="book-number">Номер экземпляра</label>
            <input
                    id="book-number"
                    type="text"
                    name="unique_number"
                    maxlength="100"
                    required
                    value="<?= $escape($data->oldInput['unique_number'] ?? '') ?>"
                    <?= !empty($data->errors['unique_number']) ? 'class="error" aria-invalid="true"' : '' ?>
            >
            <?php if (!empty($data->errors['unique_number'])): ?>
                <p class="field-error"><?= $escape($data->errors['unique_number']) ?></p>
            <?php endif; ?>

            <label for="book-response">Ваш отклик</label>
            <textarea
                    id="book-response"
                    name="text_response"
                    maxlength="5000"
                    rows="8"
                    required
                <?= !empty($data->errors['text_response']) ? 'class="error" aria-invalid="true"' : '' ?>
            ><?= $escape($data->oldInput['text_response'] ?? '') ?></textarea>
            <?php if (!empty($data->errors['text_response'])): ?>
                <p class="field-error"><?= $escape($data->errors['text_response']) ?></p>
            <?php endif; ?>

            <label for="book-photo">Фото страницы (необязательно)</label>
            <input
                    id="book-photo"
                    type="file"
                    name="photo"
                    accept="image/jpeg"
                    aria-describedby="book-photo-hint"
            >
            <small id="book-photo-hint" class="hint">
                JPEG, максимум 25 МБ. Отзыв и загруженное фото сразу станут публичными.
            </small>

            <button type="submit" class="btn-primary">Опубликовать отклик</button>
        </form>
    <?php endif; ?>
</section>

<div class="profile-actions">
    <form method="post" action="?page=auth&amp;action=logout&amp;lang=<?= $escape($langCode) ?>">
        <input type="hidden" name="csrf_token" value="<?= $escape($data->csrf_token) ?>">
        <button type="submit" class="btn-secondary">Выйти</button>
    </form>
</div>