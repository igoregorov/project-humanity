<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\CsrfService;
use App\Application\Exception\LivingBookValidationException;
use App\Application\FlashMessageService;
use App\Application\LivingBookService;
use App\Domain\LivingBook\BookEntryEntity;
use App\Infrastructure\Security\BookPhotoUploadService;
use App\View\LivingBookData;
use PDOException;
use RuntimeException;
use Throwable;

final class LivingBookController
{
    private object $container;

    public function __construct(object $container)
    {
        $this->container = $container;
    }

    public function __invoke(): void
    {
        $this->handle();
    }

    public function handle(): void
    {
        $action = strtolower(trim((string) ($_GET['action'] ?? 'index')));
        $lang = $this->resolveLang();

        $flashService = $this->getFlashService();

        if ($action === 'create') {
            $this->create(
                $lang,
                $flashService,
                $this->currentUserId(),
                $this->container->has('living_book_service')
            );

            return;
        }

        $flash = $flashService->pull();

        $userId = $this->currentUserId();
        $dbAvailable = $this->container->has('living_book_service');
        $currentUserEntry = null;

        if ($dbAvailable && $userId !== null) {
            try {
                $entry = $this->getLivingBookService()->getUserEntry($userId);
                $currentUserEntry = $entry !== null ? $this->formatEntry($entry) : null;
            } catch (Throwable) {
                $dbAvailable = false;
                $currentUserEntry = null;
            }
        }

        if ($action === 'form') {
            if ($userId === null) {
                $flashService->error('living_book_error_auth');
                $this->redirect([
                    'page' => 'living_book',
                    'lang' => $lang,
                ]);
            }

            if (!$dbAvailable) {
                $this->render(
                    'form',
                    [],
                    null,
                    null,
                    $flash,
                    false,
                    $lang,
                    null
                );

                return;
            }

            if ($currentUserEntry !== null) {
                $filter = (string) ($currentUserEntry['book_number'] ?? '');

                if ($filter !== '') {
                    $this->redirect([
                        'page' => 'living_book',
                        'filter' => $filter,
                        'lang' => $lang,
                    ]);
                }
            }

            $csrfToken = $this->getCsrfService()->getToken();

            $this->render(
                'form',
                [],
                null,
                $currentUserEntry,
                $flash,
                true,
                $lang,
                $csrfToken
            );

            return;
        }

        $filter = trim((string) ($_GET['filter'] ?? ''));
        $entries = [];
        $mode = 'recent';

        if ($dbAvailable) {
            try {
                if ($filter !== '') {
                    $mode = 'history';
                    $entities = $this->getLivingBookService()->getHistoryByNumber($filter);
                } else {
                    $mode = 'recent';
                    $entities = $this->getLivingBookService()->getRecentEntries(50);
                }

                $entries = array_map(
                    fn (BookEntryEntity $entry): array => $this->formatEntry($entry),
                    $entities
                );
            } catch (Throwable) {
                $dbAvailable = false;
                $entries = [];
                $mode = 'recent';
            }
        } else {
            $mode = 'recent';
        }

        $this->render(
            $mode,
            $entries,
            $filter !== '' ? $filter : null,
            $currentUserEntry,
            $flash,
            $dbAvailable,
            $lang,
            null
        );
    }

    private function create(
        string $lang,
        FlashMessageService $flashService,
        ?int $userId,
        bool $dbAvailable
    ): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect([
                'page' => 'living_book',
                'action' => 'form',
                'lang' => $lang,
            ]);
        }

        $number = (string) ($_POST['unique_number'] ?? '');
        $text = (string) ($_POST['text_response'] ?? '');

        $photo = $_FILES['photo'] ?? null;

        if (!is_array($photo)) {
            $photo = null;
        }

        if ($userId === null) {
            $flashService->error('living_book_error_auth');
            $flashService->oldInput('unique_number', $number);
            $flashService->oldInput('text_response', $text);

            $this->redirect([
                'page' => 'living_book',
                'action' => 'form',
                'lang' => $lang,
            ]);
        }

        $csrfToken = (string) ($_POST['csrf_token'] ?? '');

        if (!$this->getCsrfService()->validateToken($csrfToken)) {
            $flashService->error('living_book_error_csrf');
            $flashService->oldInput('unique_number', $number);
            $flashService->oldInput('text_response', $text);

            $this->redirect([
                'page' => 'living_book',
                'action' => 'form',
                'lang' => $lang,
            ]);
        }

        if (!$dbAvailable) {
            $flashService->error('living_book_db_unavailable');

            $this->redirect([
                'page' => 'living_book',
                'lang' => $lang,
            ]);
        }

        try {
            $entry = $this->getLivingBookService()->createUserEntry(
                $userId,
                $number,
                $text,
                $photo
            );

            $filter = $entry->getBookNumber() ?? trim($number);

            $flashService->success('living_book_success_created');

            $this->redirect([
                'page' => 'living_book',
                'filter' => $filter,
                'lang' => $lang,
            ]);
        } catch (LivingBookValidationException $e) {
            foreach ($e->getErrors() as $errorKey) {
                $flashService->error($errorKey);
            }

            $flashService->oldInput('unique_number', $number);
            $flashService->oldInput('text_response', $text);

            $this->redirect([
                'page' => 'living_book',
                'action' => 'form',
                'lang' => $lang,
            ]);
        } catch (RuntimeException $e) {
            $key = $e->getMessage();

            if (!in_array($key, [
                'living_book_error_photo',
                'living_book_error_duplicate_user',
            ], true)) {
                $key = 'living_book_error_text';
            }

            $flashService->error($key);
            $flashService->oldInput('unique_number', $number);
            $flashService->oldInput('text_response', $text);

            $this->redirect([
                'page' => 'living_book',
                'action' => 'form',
                'lang' => $lang,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $flashService->error('living_book_error_duplicate_user');
            } else {
                $flashService->error('living_book_db_unavailable');
            }

            $flashService->oldInput('unique_number', $number);
            $flashService->oldInput('text_response', $text);

            $this->redirect([
                'page' => 'living_book',
                'action' => 'form',
                'lang' => $lang,
            ]);
        } catch (Throwable) {
            $flashService->error('living_book_db_unavailable');
            $flashService->oldInput('unique_number', $number);
            $flashService->oldInput('text_response', $text);

            $this->redirect([
                'page' => 'living_book',
                'action' => 'form',
                'lang' => $lang,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatEntry(BookEntryEntity $entry): array
    {
        $photoService = null;

        if ($this->container->has('book_photo_service')) {
            $candidate = $this->container->get('book_photo_service');

            if ($candidate instanceof BookPhotoUploadService) {
                $photoService = $candidate;
            }
        }

        return [
            'id' => $entry->getId(),
            'book_id' => $entry->getBookId(),
            'user_id' => $entry->getUserId(),
            'previous_entry_id' => $entry->getPreviousEntryId(),
            'book_number' => $entry->getBookNumber() ?? '',
            'username' => $entry->getUsername() ?? '',
            'previous_username' => $entry->getPreviousUsername(),
            'created_at' => $entry->getCreatedAt() ?? '',
            'text_response' => $entry->getTextResponse(),
            'photo_path' => $entry->getPhotoPath(),
            'photo_url' => $photoService !== null
                ? $photoService->buildPublicUrl($entry->getPhotoPath())
                : null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @param array<string, mixed>|null $currentUserEntry
     * @param array{
     *     success: string|null,
     *     errors: string[],
     *     old: array<string, mixed>
     * } $flash
     */
    private function render(
        string $mode,
        array $entries,
        ?string $filterNumber,
        ?array $currentUserEntry,
        array $flash,
        bool $dbAvailable,
        string $lang,
        ?string $csrfToken
    ): void {
        $data = (new LivingBookData(
            $this->getTranslator(),
            $lang,
            'living_book',
            $entries,
            $filterNumber,
            $mode,
            $currentUserEntry,
            $flash,
            $dbAvailable,
            $csrfToken
        ))->toArray();

        include dirname(__DIR__, 3) . '/includes/page_living_book.php';
    }

    private function getLivingBookService(): LivingBookService
    {
        return $this->container->get('living_book_service');
    }

    private function getCsrfService(): CsrfService
    {
        if ($this->container->has('csrf_service')) {
            $service = $this->container->get('csrf_service');

            if ($service instanceof CsrfService) {
                return $service;
            }
        }

        return new CsrfService();
    }

    private function getFlashService(): FlashMessageService
    {
        if ($this->container->has('flash_message_service')) {
            $service = $this->container->get('flash_message_service');

            if ($service instanceof FlashMessageService) {
                return $service;
            }
        }

        return new FlashMessageService();
    }

    private function getTranslator(): object
    {
        if ($this->container->has('translator')) {
            $translator = $this->container->get('translator');

            if (is_object($translator)) {
                return $translator;
            }
        }

        return new class {
            public function translate(string $key): string
            {
                return $key;
            }
        };
    }

    private function currentUserId(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;

        if (is_int($id)) {
            return $id > 0 ? $id : null;
        }

        if (is_string($id) && ctype_digit($id)) {
            $intValue = (int) $id;

            return $intValue > 0 ? $intValue : null;
        }

        return null;
    }

    private function resolveLang(): string
    {
        $candidates = [
            $_GET['lang'] ?? null,
            $_POST['lang'] ?? null,
            $_SESSION['lang'] ?? null,
            'ru',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, ['ru', 'en'], true)) {
                return $candidate;
            }
        }

        return 'ru';
    }

    /**
     * @param array<string, mixed> $params
     */
    private function redirect(array $params): void
    {
        $params['lang'] = $params['lang'] ?? $this->resolveLang();

        $url = '?' . http_build_query($params, '', '&');

        header('Location: ' . $url);
        exit;
    }
}