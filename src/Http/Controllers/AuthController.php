<?php
declare(strict_types=1);
// src/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Domain\Auth\AuthServiceInterface;
use App\Infrastructure\Security\CaptchaService;
use App\View\AuthData;
use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;
use RuntimeException;
use Throwable;

class AuthController extends AbstractController
{
    /**
     * @throws Throwable
     */
    public function handle(string $page, string $lang): string
    {
        $action = $_GET['action'] ?? 'login';

        return match ($action) {
            'login' => $this->showLogin($page, $lang),
            'register' => $this->showRegister($page, $lang),
            'profile' => $this->showProfile($page, $lang),
            'do_login' => $this->processLogin($page, $lang),
            'do_register' => $this->processRegister($page, $lang),
            'logout' => $this->processLogout($page, $lang),
            default => $this->showLogin($page, $lang)
        };
    }

    private function showLogin(string $page, string $lang): string
    {
        $authService = $this->container->get('auth_service');

        if ($authService->isLoggedIn()) {
            header("Location: ?page=auth&action=profile&lang=$lang");
            exit;
        }

        $authData = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'login',
            errors: [],
            oldInput: []
        );

        return $this->renderPage('auth_form.php', $authData, $page, $lang);
    }

    private function showRegister(string $page, string $lang): string
    {
        $authService = $this->container->get('auth_service');

        if ($authService->isLoggedIn()) {
            header("Location: ?page=auth&action=profile&lang=$lang");
            exit;
        }

        $authData = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'register',
            errors: [],
            oldInput: []
        );

        return $this->renderPage('auth_form.php', $authData, $page, $lang);
    }

    private function showProfile(string $page, string $lang): string
    {
        $authService = $this->container->get('auth_service');

        if (!$authService->isLoggedIn()) {
            header("Location: ?page=auth&action=login&lang=$lang");
            exit;
        }

        $user = $authService->getCurrentUser();
        $authData = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'profile',
            user: $user,
            errors: [],
            oldInput: []
        );

        return $this->renderPage('auth_profile.php', $authData, $page, $lang);
    }

    private function processLogin(string $page, string $lang): string
    {
        $authService = $this->container->get('auth_service');
        $captchaService = $this->container->get('captcha_service');

        $errors = [];
        $oldInput = [
            'username' => $_POST['username'] ?? ''
        ];

        // Валидация
        if (empty($_POST['username'])) {
            $errors['username'] = 'Введите имя пользователя';
        }

        if (empty($_POST['password'])) {
            $errors['password'] = 'Введите пароль';
        }

        if (empty($_POST['captcha'])) {
            $errors['captcha'] = 'Введите код с картинки';
        } elseif (!$captchaService->validate($_POST['captcha'])) {
            $errors['captcha'] = 'Неверный код проверки';
        }

        if (empty($errors)) {
            try {
                $user = $authService->login($_POST['username'], $_POST['password']);

                if ($user) {
                    header("Location: ?page=auth&action=profile&lang=$lang");
                    exit;
                } else {
                    $errors['general'] = 'Неверное имя пользователя или пароль';
                }
            } catch (RuntimeException $e) {
                $errors['general'] = $e->getMessage();
            }
        }

        $authData = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'login',
            errors: $errors,
            oldInput: $oldInput
        );

        return $this->renderPage('auth_form.php', $authData, $page, $lang);
    }

    private function processRegister(string $page, string $lang): string
    {
        $authService = $this->container->get('auth_service');
        $captchaService = $this->container->get('captcha_service');

        $errors = [];
        $oldInput = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];

        // Валидация
        if (empty($_POST['username'])) {
            $errors['username'] = 'Введите имя пользователя';
        }

        if (empty($_POST['email'])) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email адрес';
        }

        if (empty($_POST['password'])) {
            $errors['password'] = 'Введите пароль';
        } elseif (strlen($_POST['password']) < 8) {
            $errors['password'] = 'Пароль должен быть не менее 8 символов';
        }

        if ($_POST['password'] !== ($_POST['password_confirm'] ?? '')) {
            $errors['password_confirm'] = 'Пароли не совпадают';
        }

        if (empty($_POST['captcha'])) {
            $errors['captcha'] = 'Введите код с картинки';
        } elseif (!$captchaService->validate($_POST['captcha'])) {
            $errors['captcha'] = 'Неверный код проверки';
        }

        if (empty($errors)) {
            try {
                $user = $authService->register(
                    $_POST['username'],
                    $_POST['email'],
                    $_POST['password']
                );

                // Автоматический логин после регистрации
                $authService->login($_POST['username'], $_POST['password']);

                header("Location: ?page=auth&action=profile&lang=$lang");
                exit;

            } catch (InvalidArgumentException $e) {
                $errors['general'] = $e->getMessage();
            }
        }

        $authData = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'register',
            errors: $errors,
            oldInput: $oldInput
        );

        return $this->renderPage('auth_form.php', $authData, $page, $lang);
    }

    #[NoReturn] private function processLogout(string $page, string $lang): string
    {
        $authService = $this->container->get('auth_service');
        $authService->logout();

        header("Location: ?page=auth&action=login&lang=$lang");
        exit;
    }
}