<?php
declare(strict_types=1);

// auth_profile.php

/** @var \App\View\AuthData $data */
$user = $data->user;
$escape = static fn (mixed $value): string =>
htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<section class="living-book-profile">
    <h3>Живая Книга</h3>

    <?php if ($data->message !== null): ?>
        <p class="success-message"><?= $escape($data->message) ?></p>
    <?php endif; ?>

    <?php if (!empty($data->errors['general'])): ?>
        <p class="error-message">
            <?= $escape($data->translator->translate(
                    $data->lang_code,
                    $data->errors['general'],
                    $data->errors['general']
            )) ?>
        </p>
    <?php endif; ?>

    <?php if ($data->bookEntry !== null): ?>
        <p>Вы уже опубликовали отклик для экземпляра
            <strong><?= $escape($data->bookEntry->getBookNumber() ?? '') ?></strong>.
        </p>
        <p><?= nl2br($escape($data->bookEntry->getTextResponse())) ?></p>

        <p>Запись опубликована. Отклик нельзя изменить или удалить.</p>

        <p>
            <a href="?page=living_book&lang=<?= $escape($data->lang_code) ?>">
                Перейти к Живой Книге
            </a>
        </p>
    <?php else: ?>
        <p>Добавьте номер экземпляра и свой отклик. Фото страницы можно приложить по желанию.</p>

        <form
                method="post"
                action="?page=living_book&action=create&lang=<?= $escape($data->lang_code) ?>"
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
            >

            <label for="book-response">Ваш отклик</label>
            <textarea
                    id="book-response"
                    name="text_response"
                    maxlength="5000"
                    rows="8"
                    required
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
            >
            <small>JPEG, максимум 25 МБ. Отзыв и загруженное фото сразу станут публичными.</small>

            <button type="submit" class="btn-primary">Опубликовать отклик</button>
        </form>
    <?php endif; ?>
</section>
<section class="profile-section">
    <h2>Профиль пользователя</h2>

    <?php if (isset($data->errors['general'])): ?>
        <div class="error-message"><?= htmlspecialchars($data->errors['general']) ?></div>
    <?php endif; ?>
    <?php if (!empty($data->errors['unique_number'])): ?>
        <p class="field-error"><?= $escape($data->errors['unique_number']) ?></p>
    <?php endif; ?>

    <div class="profile-info">
        <div class="profile-field">
            <label>Имя пользователя:</label>
            <span><?= htmlspecialchars($user->username) ?></span>
        </div>
        <div class="profile-field">
            <label>Email:</label>
            <span><?= htmlspecialchars($user->email) ?></span>
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

    <!-- Блок аватара -->
    <div class="avatar-block">
        <div class="avatar-wrapper">
            <div class="avatar-preview">

                <?php if ($user->hasAvatar() && $data->avatarUrl): ?>
                    <img src="<?= htmlspecialchars($data->avatarUrl) ?>"
                         alt="Аватар <?= htmlspecialchars($user->username) ?>"
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
                  action="?page=auth&action=do_upload_avatar&lang=<?= $data->lang_code ?>"
                  enctype="multipart/form-data"
                  class="avatar-upload-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data->csrf_token) ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= 20 * 1024 * 1024 ?>">

                <input type="file"
                       id="avatar"
                       name="avatar"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       class="avatar-file-input"
                        <?= isset($data->errors['avatar']) ? 'aria-invalid="true"' : '' ?>>

                <?php if (isset($data->errors['avatar'])): ?>
                    <span class="field-error" style="text-align:center;display:block;margin-top:0.5rem;">
                    <?= htmlspecialchars($data->errors['avatar']) ?>
                </span>
                <?php endif; ?>

                <!-- Fallback, если JS отключён -->
                <noscript>
                    <div class="avatar-noscript-fallback">
                        <label for="avatar" class="btn-secondary">Выбрать файл</label>
                        <button type="submit" class="btn-primary" style="width:auto;margin-top:0;">Загрузить</button>
                    </div>
                </noscript>
            </form>
        </div>
    </div>

    <div class="profile-actions">
        <a href="?page=auth&action=logout&lang=<?= $data->lang_code ?>" class="btn-secondary">Выйти</a>
    </div>
</section>
