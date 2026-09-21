<!-- includes/auth_profile.php -->
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

        <?php if ($user->hasAvatar() && $data->avatarUrl): ?>
            <div class="avatar-preview">
                <img src="<?= htmlspecialchars($data->avatarUrl) ?>"
                     alt="Аватар <?= htmlspecialchars($user->username) ?>"
                     class="avatar-image">
            </div>

            <form method="POST"
                  action="?page=auth&action=do_delete_avatar&lang=<?= $data->lang_code ?>"
                  class="avatar-delete-form"
                  onsubmit="return confirm('Удалить аватар?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data->csrf_token) ?>">
                <button type="submit" class="btn-secondary">Удалить аватар</button>
            </form>
        <?php else: ?>
            <p><em>Аватар не загружен</em></p>
        <?php endif; ?>

        <form method="POST"
              action="?page=auth&action=do_upload_avatar&lang=<?= $data->lang_code ?>"
              enctype="multipart/form-data"
              class="avatar-upload-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data->csrf_token) ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= 20 * 1024 * 1024 ?>">

            <div class="form-group">
                <label for="avatar">
                    <?= $user->hasAvatar() ? 'Заменить аватар' : 'Загрузить аватар' ?>:
                </label>
                <input type="file"
                       id="avatar"
                       name="avatar"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       class="<?= isset($data->errors['avatar']) ? 'error' : '' ?>"
                       required>
                <small class="form-hint">Максимум 20 МБ. Форматы: JPG, PNG, WEBP, GIF.</small>
                <?php if (isset($data->errors['avatar'])): ?>
                    <span class="field-error"><?= htmlspecialchars($data->errors['avatar']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary">Загрузить</button>
        </form>
    </div>

    <div class="profile-actions">
        <a href="?page=auth&action=logout&lang=<?= $data->lang_code ?>" class="btn-secondary">Выйти</a>
    </div>
</section>
