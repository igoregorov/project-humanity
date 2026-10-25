<?php

declare(strict_types=1);

// src/Http/Controllers/LivingBookController.php

namespace App\Http\Controllers;

use App\Application\CsrfService;
use App\Application\LivingBookService;
use App\Domain\Auth\AuthServiceInterface;
use App\Domain\LivingBook\BookEntryEntity;
use App\Infrastructure\ContainerInterface;
use App\Infrastructure\Security\BookPhotoUploadService;
use App\View\LivingBookData;
use RuntimeException;
use Throwable;

use App\Application\Exception\LivingBookValidationException;

final class LivingBookController extends AbstractController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function handle(string $page, string $lang): string
    {
        $action = (string) ($_GET['action'] ?? 'index');

        if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            return $this->processCreate($lang);
        }

        if ($action === 'graph-data') {
            return $this->sendJson($this->getLivingBookService()->prepareGraphData());
        }

        if ($action === 'book-details') {
            $bookId = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT);

            if (!is_int($bookId) || $bookId < 1) {
                return $this->sendJson(['error' => 'Invalid book ID'], 400);
            }

            return $this->sendJson(
                $this->getLivingBookService()->getBookDetails($bookId)
            );
        }

        return $this->renderIndex($page, $lang);
    }

    private function processCreate(string $lang): string
    {
        /** @var AuthServiceInterface $authService */
        $authService = $this->container->get('auth_service');
        $user = $authService->getCurrentUser();

        if ($user === null || $user->id === null) {
            header('Location: ?page=auth&action=login&lang=' . rawurlencode($lang));
            exit;
        }

        /** @var CsrfService $csrf */
        $csrf = $this->container->get('csrf_service');

        if (!$csrf->validateToken((string) ($_POST['csrf_token'] ?? ''))) {
            return $this->renderCreateResult(
                $lang,
                ['general' => 'living_book_error_csrf'],
                [
                    'unique_number' => (string) ($_POST['unique_number'] ?? ''),
                    'text_response' => (string) ($_POST['text_response'] ?? ''),
                ]
            );
        }

        $photo = $_FILES['photo'] ?? null;

        if (!is_array($photo) || (int) ($photo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $photo = null;
        }

        try {
            $this->getLivingBookService()->createUserEntry(
                $user->id,
                (string) ($_POST['unique_number'] ?? ''),
                (string) ($_POST['text_response'] ?? ''),
                $photo
            );

            header('Location: ?page=living_book&created=1&lang=' . rawurlencode($lang));
            exit;
        } catch (LivingBookValidationException $e) {
            $errors = $e->getErrors();

            return $this->renderCreateResult(
                $lang,
                $errors,
                [
                    'unique_number' => (string) ($_POST['unique_number'] ?? ''),
                    'text_response' => (string) ($_POST['text_response'] ?? ''),
                ]
            );
        } catch (Throwable $e) {
            error_log('Living book create error: ' . $e->getMessage());

            $messageKey = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'living_book_db_unavailable';

            return $this->renderCreateResult(
                $lang,
                ['general' => $messageKey],
                [
                    'unique_number' => (string) ($_POST['unique_number'] ?? ''),
                    'text_response' => (string) ($_POST['text_response'] ?? ''),
                ]
            );
        }
    }

    private function renderCreateResult(string $lang, array $errors, array $oldInput): string
    {
        /** @var AuthServiceInterface $authService */
        $authService = $this->container->get('auth_service');
        $user = $authService->getCurrentUser();

        if ($user === null || $user->id === null) {
            header('Location: ?page=auth&action=login&lang=' . rawurlencode($lang));
            exit;
        }

        /** @var BookPhotoUploadService $photoService */
        $photoService = $this->container->get('book_photo_service');

        $entry = $this->getLivingBookService()->getUserEntry($user->id);
        $authData = $this->container->get('localized_content');

        $profileData = new \App\View\AuthData(
            translator: $this->container->get('localized_content'),
            lang_code: $lang,
            action: 'profile',
            user: $user,
            errors: $errors,
            oldInput: $oldInput,
            csrf_token: $this->container->get('csrf_service')->getToken(true),
            avatarUrl: $user->hasAvatar()
                ? $this->container->get('avatar_service')->getPublicUrl($user->avatarPath)
                : null,
            bookEntry: $entry
        );

        return $this->renderPage('auth_profile.php', $profileData, 'auth', $lang);
    }

    private function renderIndex(string $page, string $lang): string
    {
        $service = $this->getLivingBookService();
        $csrf = $this->container->get('csrf_service');
        $entries = array_map(
            fn (BookEntryEntity $entry): array => $this->formatEntry(
                $entry,
                $this->container->get('book_photo_service')
            ),
            $service->getRecentEntries(50)
        );

        $filter = trim((string) ($_GET['filter'] ?? ''));

        if ($filter !== '') {
            $history = $service->getHistoryByNumber($filter);
            $entries = array_map(
                fn (BookEntryEntity $entry): array => $this->formatEntry(
                    $entry,
                    $this->container->get('book_photo_service')
                ),
                $history
            );
        }

        $translator = $this->container->get('localized_content');
        $flash = [];

        if (isset($_GET['created'])) {
            $flash['success'] = 'living_book_success_created';
        }

        $data = new LivingBookData(
            translator: $translator,
            langCode: $lang,
            currentPage: 'living_book',
            entries: $entries,
            filterNumber: $filter !== '' ? $filter : null,
            mode: $filter === '' ? 'recent' : 'history',
            currentUserEntry: null,
            flash: $flash,
            dbAvailable: true,
            csrfToken: $csrf->getToken()
        );

        return $this->renderPage('page_living_book.php', $data, $page, $lang);
    }

    private function formatEntry(
        BookEntryEntity $entry,
        BookPhotoUploadService $photoService
    ): array {
        return [
            'id' => $entry->getId(),
            'book_id' => $entry->getBookId(),
            'user_id' => $entry->getUserId(),
            'previous_entry_id' => $entry->getPreviousEntryId(),
            'book_number' => $entry->getBookNumber() ?? '',
            'username' => $entry->getUsername() ?? 'Anonymous',
            'previous_username' => $entry->getPreviousUsername(),
            'created_at' => $entry->getCreatedAt(),
            'text_response' => $entry->getTextResponse(),
            'photo_url' => $photoService->buildPublicUrl($entry->getPhotoPath()),
        ];
    }

    private function getLivingBookService(): LivingBookService
    {
        return $this->container->get('living_book_service');
    }

    private function sendJson(array $payload, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        return (string) json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }
}