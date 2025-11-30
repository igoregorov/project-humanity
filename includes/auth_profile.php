<?php
// includes/auth_profile.php
declare(strict_types=1);
/** @var AuthData $data */

use App\View\AuthData;

$user = $data->user;
?>

<section class="profile-section">
    <h2>Профиль пользователя</h2>

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

    <div class="profile-actions">
        <a href="?page=auth&action=logout&lang=<?= $data->lang_code ?>" class="btn-secondary">Выйти</a>
    </div>
</section>