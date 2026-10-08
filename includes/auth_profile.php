<?php
declare(strict_types=1);
/** @var \App\View\AuthData $data */
$user = $data->user;
?>
<section class="profile-section">
    <h2>Профиль пользователя</h2>

    <?php if (isset($data->errors['general'])): ?>
        <div class="error-message"><?= htmlspecialchars($data->errors['general']) ?></div>
    <?php endif; ?>

    <?php if ($data->avatarMessage): ?>
        <div class="success-message"><?= htmlspecialchars($data->avatarMessage) ?></div>
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
        <h3>Аватар</h3>

        <div class="avatar-wrapper">
            <div class="avatar-preview"
                 title="Нажмите, чтобы загрузить новый аватар">

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

            <small class="form-hint">
                Нажмите на аватар, чтобы загрузить новый.<br>
                Максимум 20 МБ. Форматы: JPG, PNG, WEBP, GIF.
            </small>
        </div>
    </div>

    <div class="profile-actions">
        <a href="?page=auth&action=logout&lang=<?= $data->lang_code ?>" class="btn-secondary">Выйти</a>
    </div>
</section>
