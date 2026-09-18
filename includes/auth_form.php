<?php
// includes/auth_form.php
declare(strict_types=1);
/** @var AuthData $data */

use App\View\AuthData;

$action = $data->action;
$errors = $data->errors;
$oldInput = $data->oldInput;
?>

<section class="auth-section">
    <h2><?= $action === 'login' ? 'Вход' : 'Регистрация' ?></h2>

    <?php if (isset($errors['general'])): ?>
        <div class="error-message"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="?page=auth&action=do_<?= $action ?>&lang=<?= $data->lang_code ?>" class="auth-form">
        <div class="form-group">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($oldInput['username'] ?? '') ?>"
                   class="<?= isset($errors['username']) ? 'error' : '' ?>">
            <?php if (isset($errors['username'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['username']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($action === 'register'): ?>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                       class="<?= isset($errors['email']) ? 'error' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password"
                   class="<?= isset($errors['password']) ? 'error' : '' ?>">
            <?php if (isset($errors['password'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($action === 'register'): ?>
            <div class="form-group">
                <label for="password_confirm">Подтверждение пароля:</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       class="<?= isset($errors['password_confirm']) ? 'error' : '' ?>">
                <?php if (isset($errors['password_confirm'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password_confirm']) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="captcha">Код проверки:</label>
            <div class="captcha-container">
                <input type="text" id="captcha" name="captcha"
                       class="<?= isset($errors['captcha']) ? 'error' : '' ?>">
                <img src="/captcha.php?lang=<?= $data->lang_code ?>"
                     alt="CAPTCHA"
                     onclick="this.src='/captcha.php?lang=<?= $data->lang_code ?>&' + Math.random()"
                     style="cursor: pointer;">
            </div>
            <?php if (isset($errors['captcha'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['captcha']) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary">
            <?= $action === 'login' ? 'Войти' : 'Зарегистрироваться' ?>
        </button>
    </form>

    <div class="auth-links">
        <?php if ($action === 'login'): ?>
            <p>Нет аккаунта? <a href="?page=auth&action=register&lang=<?= $data->lang_code ?>">Зарегистрируйтесь</a></p>
        <?php else: ?>
            <p>Уже есть аккаунт? <a href="?page=auth&action=login&lang=<?= $data->lang_code ?>">Войдите</a></p>
        <?php endif; ?>
    </div>
</section>