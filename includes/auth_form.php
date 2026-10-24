<?php
// includes/auth_form.php
declare(strict_types=1);

/** @var AuthData $data */
use App\View\AuthData;

$action = $data->action;
$errors = $data->errors;
$oldInput = $data->oldInput;
$langCode = $data->lang_code;
?>
<section class="auth-section">
    <h2><?= $action === 'login' ? 'Вход' : 'Регистрация' ?></h2>

    <?php if (isset($errors['general'])): ?>
        <div class="error-message"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST"
          action="?page=auth&action=do_<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>&lang=<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>"
          class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data->csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($oldInput['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="<?= isset($errors['username']) ? 'error' : '' ?>">
            <?php if (isset($errors['username'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <?php if ($action === 'register'): ?>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($oldInput['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="<?= isset($errors['email']) ? 'error' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password"
                   class="<?= isset($errors['password']) ? 'error' : '' ?>">
            <?php if (isset($errors['password'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <?php if ($action === 'register'): ?>
            <div class="form-group">
                <label for="password_confirm">Подтверждение пароля:</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       class="<?= isset($errors['password_confirm']) ? 'error' : '' ?>">
                <?php if (isset($errors['password_confirm'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password_confirm'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="captcha">Код проверки:</label>
            <div class="captcha-container">
                <input type="text" id="captcha" name="captcha"
                       class="<?= isset($errors['captcha']) ? 'error' : '' ?>">

                <img src="/public/captcha.php?lang=<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>"
                     alt="CAPTCHA"
                     data-captcha-image
                     data-captcha-refresh
                     style="cursor: pointer;">
            </div>
            <?php if (isset($errors['captcha'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['captcha'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary">
            <?= $action === 'login' ? 'Войти' : 'Зарегистрироваться' ?>
        </button>
    </form>

    <div class="auth-links">
        <?php if ($action === 'login'): ?>
            <p>Нет аккаунта? <a href="?page=auth&action=register&lang=<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>">Зарегистрируйтесь</a></p>
        <?php else: ?>
            <p>Уже есть аккаунт? <a href="?page=auth&action=login&lang=<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>">Войдите</a></p>
        <?php endif; ?>
    </div>
</section>

<script src="/scripts/captcha.js" defer></script>