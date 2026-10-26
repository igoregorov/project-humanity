<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\CsrfService;
use App\Domain\Auth\AuthServiceInterface;
use App\Domain\Auth\User;
use App\Infrastructure\Security\AvatarUploadService;
use App\Infrastructure\Security\CaptchaService;
use App\View\AuthData;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class AuthController extends AbstractController
{
    public function handle(string $page, string $lang): string
    {
        $action = $this->getQueryString('action', 'login');

        return match ($action) {
            'register' => $this->showForm($page, $lang, 'register'),
            'profile' => $this->showProfile($page, $lang),
            'do_login' => $this->processLogin($page, $lang),
            'do_register' => $this->processRegister($page, $lang),
            'do_upload_avatar' => $this->processAvatarUpload($page, $lang),
            'do_delete_avatar' => $this->processAvatarDelete($page, $lang),
            'logout' => $this->processLogout($lang),
            default => $this->showForm($page, $lang, 'login'),
        };
    }

    private function showForm(string $page, string $lang, string $action): string
    {
        $auth = $this->authService();

        if ($auth->isLoggedIn()) {
            $this->redirect('?page=auth&action=profile&lang=' . rawurlencode($lang));
        }

        $data = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: $action,
            errors: [],
            oldInput: [],
            csrf_token: $this->csrfService()->getToken()
        );

        return $this->renderPage('auth_form.php', $data, $page, $lang);
    }

    private function showProfile(string $page, string $lang): string
    {
        $user = $this->authService()->getCurrentUser();

        if ($user === null) {
            $this->redirect(
                '?page=auth&action=login&lang=' . rawurlencode($lang)
            );
        }

        return $this->renderPage(
            'auth_profile.php',
            $this->buildProfileData($lang, $user),
            $page,
            $lang
        );
    }

    private function processLogin(string $page, string $lang): string
    {
        if (!$this->isPost()) {
            http_response_code(405);
            header('Allow: POST');
            return '';
        }

        $username = $this->postString('username');
        $password = $this->postString('password');
        $captchaCode = $this->postString('captcha');

        $errors = [];
        $oldInput = ['username' => $username];

        if (!$this->validCsrf()) {
            $errors['general'] = 'Сессия устарела. Обновите страницу и попробуйте снова.';
        } else {
            if ($username === '') {
                $errors['username'] = 'Введите имя пользователя';
            }

            if ($password === '') {
                $errors['password'] = 'Введите пароль';
            }

            /** @var CaptchaService $captcha */
            $captcha = $this->container->get('captcha_service');

            if ($captchaCode === '') {
                $errors['captcha'] = 'Введите код с картинки';
            } elseif (!$captcha->validate($captchaCode)) {
                $errors['captcha'] = 'Неверный код проверки';
            }
        }

        if ($errors === []) {
            try {
                $user = $this->authService()->login($username, $password);

                if ($user !== null) {
                    $this->redirect(
                        '?page=auth&action=profile&lang=' . rawurlencode($lang)
                    );
                }

                $errors['general'] = 'Неверное имя пользователя или пароль';
            } catch (RuntimeException $e) {
                // Показываем контролируемое сообщение, а не детали ошибки БД.
                $errors['general'] = $e->getMessage();
            }
        }

        $data = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'login',
            errors: $errors,
            oldInput: $oldInput,
            csrf_token: $this->csrfService()->getToken(true)
        );

        return $this->renderPage('auth_form.php', $data, $page, $lang);
    }

    private function processRegister(string $page, string $lang): string
    {
        if (!$this->isPost()) {
            http_response_code(405);
            header('Allow: POST');
            return '';
        }

        $username = $this->postString('username');
        $email = $this->postString('email');
        $password = $this->postString('password');
        $passwordConfirm = $this->postString('password_confirm');
        $captchaCode = $this->postString('captcha');

        $errors = [];
        $oldInput = [
            'username' => $username,
            'email' => $email,
        ];

        if (!$this->validCsrf()) {
            $errors['general'] = 'Сессия устарела. Обновите страницу и попробуйте снова.';
        } else {
            if (mb_strlen($username, 'UTF-8') < 3 || mb_strlen($username, 'UTF-8') > 50) {
                $errors['username'] = 'Имя пользователя должно содержать от 3 до 50 символов';
            }

            if (
                $email === ''
                || mb_strlen($email, 'UTF-8') > 100
                || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            ) {
                $errors['email'] = 'Некорректный email адрес';
            }

            if (strlen($password) < 8 || strlen($password) > 4096) {
                $errors['password'] = 'Пароль должен содержать не менее 8 символов';
            }

            if ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'Пароли не совпадают';
            }

            /** @var CaptchaService $captcha */
            $captcha = $this->container->get('captcha_service');

            if ($captchaCode === '') {
                $errors['captcha'] = 'Введите код с картинки';
            } elseif (!$captcha->validate($captchaCode)) {
                $errors['captcha'] = 'Неверный код проверки';
            }
        }

        if ($errors === []) {
            try {
                $this->authService()->register($username, $email, $password);
                $user = $this->authService()->login($username, $password);

                if ($user === null) {
                    $errors['general'] = 'Регистрация выполнена. Войдите в аккаунт.';
                } else {
                    $this->redirect(
                        '?page=auth&action=profile&lang=' . rawurlencode($lang)
                    );
                }
            } catch (InvalidArgumentException $e) {
                $errors['general'] = $e->getMessage();
            } catch (RuntimeException $e) {
                error_log('Registration failed: ' . $e->getMessage());
                $errors['general'] = 'Не удалось завершить регистрацию.';
            }
        }

        $data = new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'register',
            errors: $errors,
            oldInput: $oldInput,
            csrf_token: $this->csrfService()->getToken(true)
        );

        return $this->renderPage('auth_form.php', $data, $page, $lang);
    }

    private function processLogout(string $lang): string
    {
        if (!$this->isPost()) {
            http_response_code(405);
            header('Allow: POST');
            return '';
        }

        if (!$this->validCsrf()) {
            http_response_code(403);
            return 'Недействительный CSRF-токен.';
        }

        $this->authService()->logout();

        $this->redirect(
            '?page=auth&action=login&lang=' . rawurlencode($lang)
        );
    }

    private function processAvatarUpload(string $page, string $lang): string
    {
        $user = $this->requireCurrentUser($lang);

        if (!$this->isPost()) {
            http_response_code(405);
            header('Allow: POST');
            return '';
        }

        if (!$this->validCsrf()) {
            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $user,
                ['general' => 'Сессия устарела. Обновите страницу.'],
                null
            );
        }

        $file = $_FILES['avatar'] ?? null;

        if (!is_array($file)) {
            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $user,
                ['avatar' => 'Выберите файл для загрузки.'],
                null
            );
        }

        /** @var AvatarUploadService $avatarService */
        $avatarService = $this->container->get('avatar_service');
        $userRepository = $this->container->get('user_repository');

        $newFilename = null;

        try {
            if ($user->id === null) {
                throw new RuntimeException('Authenticated user has no ID.');
            }

            // Сначала сохраняем новый файл.
            $newFilename = $avatarService->upload($file, $user->id);

            $updatedUser = new User(
                id: $user->id,
                username: $user->username,
                email: $user->email,
                passwordHash: $user->passwordHash,
                role: $user->role,
                isActive: $user->isActive,
                createdAt: $user->createdAt,
                updatedAt: new DateTimeImmutable(),
                lastLogin: $user->lastLogin,
                avatarPath: $newFilename
            );

            // Сначала обновляем запись пользователя.
            $userRepository->save($updatedUser);

            // Старый файл удаляем только после успешного сохранения нового пути.
            if ($user->hasAvatar() && $user->avatarPath !== $newFilename) {
                $avatarService->delete($user->avatarPath);
            }

            $freshUser = $userRepository->findById($user->id) ?? $updatedUser;

            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $freshUser,
                [],
                'Аватар успешно загружен.'
            );
        } catch (InvalidArgumentException $e) {
            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $user,
                ['avatar' => $e->getMessage()],
                null
            );
        } catch (Throwable $e) {
            // Если запись в БД не удалась, удаляем только новый файл.
            if (is_string($newFilename) && $newFilename !== '') {
                $avatarService->delete($newFilename);
            }

            error_log('Avatar upload error: ' . $e->getMessage());

            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $user,
                ['avatar' => 'Ошибка сервера при загрузке файла.'],
                null
            );
        }
    }

    private function processAvatarDelete(string $page, string $lang): string
    {
        $user = $this->requireCurrentUser($lang);

        if (!$this->isPost()) {
            http_response_code(405);
            header('Allow: POST');
            return '';
        }

        if (!$this->validCsrf()) {
            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $user,
                ['general' => 'Сессия устарела. Обновите страницу.'],
                null
            );
        }

        if ($user->id === null) {
            http_response_code(401);
            return '';
        }

        /** @var AvatarUploadService $avatarService */
        $avatarService = $this->container->get('avatar_service');
        $userRepository = $this->container->get('user_repository');

        try {
            $updatedUser = new User(
                id: $user->id,
                username: $user->username,
                email: $user->email,
                passwordHash: $user->passwordHash,
                role: $user->role,
                isActive: $user->isActive,
                createdAt: $user->createdAt,
                updatedAt: new DateTimeImmutable(),
                lastLogin: $user->lastLogin,
                avatarPath: null
            );

            $userRepository->save($updatedUser);

            if ($user->hasAvatar()) {
                $avatarService->delete($user->avatarPath);
            }

            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $userRepository->findById($user->id) ?? $updatedUser,
                [],
                'Аватар удалён.'
            );
        } catch (Throwable $e) {
            error_log('Avatar delete error: ' . $e->getMessage());

            return $this->renderProfileWithMessages(
                $page,
                $lang,
                $user,
                ['avatar' => 'Не удалось удалить аватар.'],
                null
            );
        }
    }

    private function buildProfileData(
        string $lang,
        User $user,
        ?string $message = null,
        array $errors = [],
        array $oldInput = []
    ): AuthData {
        /** @var AvatarUploadService $avatarService */
        $avatarService = $this->container->get('avatar_service');

        $bookEntry = null;

        if ($user->id !== null && $this->container->has('living_book_service')) {
            try {
                $bookEntry = $this->container
                    ->get('living_book_service')
                    ->getUserEntry($user->id);
            } catch (Throwable $e) {
                error_log('Unable to load Living Book entry: ' . $e->getMessage());
                $errors['general'] ??= 'Не удалось загрузить данные Живой книги.';
            }
        }

        return new AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'profile',
            user: $user,
            errors: $errors,
            oldInput: $oldInput,
            csrf_token: $this->csrfService()->getToken(),
            avatarUrl: $user->hasAvatar()
                ? $avatarService->getPublicUrl((string) $user->avatarPath)
                : null,
            bookEntry: $bookEntry,
            message: $message
        );
    }

    private function renderProfileWithMessages(
        string $page,
        string $lang,
        User $user,
        array $errors,
        ?string $message
    ): string {
        return $this->renderPage(
            'auth_profile.php',
            $this->buildProfileData($lang, $user, $message, $errors),
            $page,
            $lang
        );
    }

    private function requireCurrentUser(string $lang): User
    {
        $user = $this->authService()->getCurrentUser();

        if ($user === null) {
            $this->redirect(
                '?page=auth&action=login&lang=' . rawurlencode($lang)
            );
        }

        return $user;
    }

    private function validCsrf(): bool
    {
        $token = $this->postString('csrf_token');

        return $token !== '' && $this->csrfService()->validateToken($token);
    }

    private function postString(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    private function getQueryString(string $key, string $default): string
    {
        $value = $_GET[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    private function isPost(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    private function authService(): AuthServiceInterface
    {
        return $this->container->get('auth_service');
    }

    private function csrfService(): CsrfService
    {
        return $this->container->get('csrf_service');
    }

    private function redirect(string $location): never
    {
        header('Location: ' . $location, true, 303);
        exit;
    }
}