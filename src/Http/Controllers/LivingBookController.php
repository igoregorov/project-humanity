<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\CsrfService;
use App\Application\FlashMessageService;
use App\Application\LivingBookService;
use App\Domain\LivingBook\BookEntryEntity;
use App\View\LivingBookData;
use Throwable;

final class LivingBookController
{
    private object $container;

    public function __construct(object $container)
    {
        $this->container = $container;
    }

    public function handle(string $page, string $lang): string
    {
        /** @var LivingBookService $service */
        $service = $this->container->get('living_book_service');
        /** @var FlashMessageService $flash */
        $flash = $this->container->get('flash_message_service');
        /** @var CsrfService $csrf */
        $csrf = $this->container->get('csrf_service');

        $action = $_GET['action'] ?? 'index';

        try {
            // 1. POST создание записи
            if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
                return $this->processCreate($service, $flash, $csrf, $lang);
            }

            // 2. AJAX: Данные для графа
            if ($action === 'graph-data') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($service->prepareGraphData());
                exit;
            }

            // 3. AJAX: Детали книги (таймлайн)
            if ($action === 'book-details') {
                $bookId = (int)($_GET['book_id'] ?? 0);
                if ($bookId <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid book ID']);
                    exit;
                }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($service->getBookDetails($bookId));
                exit;
            }

            // 4. HTML Views
            return $this->processView($service, $flash, $csrf, $lang, $action);

        } catch (Throwable $e) {
            error_log("LivingBook Error: " . $e->getMessage());
            // Если это AJAX запрос, вернуть ошибку в JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['error' => 'Server error']);
                exit;
            }

            $flash->error('living_book_db_unavailable');
            return $this->renderErrorPage($lang);
        }
    }

    private function processCreate(LivingBookService $service, FlashMessageService $flash, CsrfService $csrf, string $lang): string
    {
        $authService = $this->container->get('auth_service');
        if (!$authService->isLoggedIn()) {
            $flash->error('living_book_error_auth');
            header("Location: ?page=living_book&lang=$lang");
            exit;
        }
        $userId = $authService->getCurrentUser()->id;

        $token = $_POST['csrf_token'] ?? '';
        if (!$csrf->validateToken($token)) {
            $flash->error('living_book_error_csrf');
            header("Location: ?page=living_book&action=form&lang=$lang");
            exit;
        }

        $number = trim((string)($_POST['unique_number'] ?? ''));
        $text = trim((string)($_POST['text_response'] ?? ''));
        $photo = $_FILES['photo'] ?? null;

        if (!is_array($photo) || ($photo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $photo = null;
        }

        try {
            $entry = $service->createUserEntry($userId, $number, $text, $photo);
            $filter = $entry->getBookNumber() ?? $number;
            $flash->success('living_book_success_created');
            header("Location: ?page=living_book&filter=" . urlencode($filter) . "&lang=$lang");
            exit;
        } catch (\App\Application\Exception\LivingBookValidationException $e) {
            foreach ($e->getErrors() as $key => $msgKey) {
                $flash->error(is_string($msgKey) ? $msgKey : $key);
            }
            $flash->oldInput('unique_number', $number);
            $flash->oldInput('text_response', $text);
            header("Location: ?page=living_book&action=form&lang=$lang");
            exit;
        } catch (\RuntimeException $e) {
            $flash->error($e->getMessage());
            header("Location: ?page=living_book&action=form&lang=$lang");
            exit;
        }
    }

    private function processView(LivingBookService $service, FlashMessageService $flash, CsrfService $csrf, string $lang, string $action): string
    {
        $filter = trim((string)($_GET['filter'] ?? ''));
        $entries = [];
        $mode = 'recent';

        // 1. Определение режима и загрузка данных
        if ($action === 'map') {
            $mode = 'map';
            $entries = [];
        } elseif ($action === 'form') {
            $mode = 'form';
            $entries = [];
        } elseif ($filter !== '') {
            $mode = 'history';
            try {
                $entities = $service->getHistoryByNumber($filter);
                $entries = array_map([$this, 'formatEntry'], $entities);
            } catch (Throwable $e) {
                error_log("LivingBook History Error: " . $e->getMessage());
                $entries = [];
            }
        } else {
            $mode = 'recent';
            try {
                $entities = $service->getRecentEntries(50);
                $entries = array_map([$this, 'formatEntry'], $entities);
            } catch (Throwable $e) {
                error_log("LivingBook Recent Error: " . $e->getMessage());
                $entries = [];
            }
        }

        // 2. Получение CSRF токена
        $csrfToken = $csrf->getToken();


        $flashData = $flash->pull();

        $successMsg = $flashData['success'] ?? null;
        $errorMsgs = $flashData['errors'] ?? [];
        $oldInputs = $flashData['old'] ?? [];

        // 4. Проверка авторизации для отображения "моей записи" (опционально, но важно для UI)
        $currentUserEntry = null;
        $authService = $this->container->has('auth_service') ? $this->container->get('auth_service') : null;

        if ($authService && $authService->isLoggedIn()) {
            $userId = $authService->getCurrentUser()->id;
            try {
                $userEntry = $service->getUserEntry($userId);
                if ($userEntry !== null) {
                    $currentUserEntry = $this->formatEntry($userEntry);
                }
            } catch (Throwable $e) {
                // Игнорируем ошибку получения своей записи, чтобы не ломать страницу
                error_log("LivingBook User Entry Error: " . $e->getMessage());
            }
        }

        $flashArray = [
            'success' => $successMsg,
            'errors' => $errorMsgs,
            'old' => $oldInputs,
        ];

        // Определяем доступность БД (упрощенно: если сервис есть, считаем что доступна)
        $dbAvailable = true;

        $data = new LivingBookData(
            translator: $this->container->get('localized_content'),
            langCode: $lang,
            currentPage: 'living_book',
            entries: $entries,
            filterNumber: $filter !== '' ? $filter : null,
            mode: $mode,
            currentUserEntry: $currentUserEntry,
            flash: $flashArray,
            dbAvailable: $dbAvailable,
            csrfToken: $csrfToken
        );

        // 6. Рендеринг шаблона
        // Шаблоны в includes/page_living_book.php ожидают переменную $data
        include dirname(__DIR__, 3) . '/includes/page_living_book.php';

        return '';
    }

    private function formatEntry(BookEntryEntity $entity): array
    {
        $photoService = $this->container->has('book_photo_service')
            ? $this->container->get('book_photo_service')
            : null;

        return [
            'id' => $entity->getId(),
            'book_id' => $entity->getBookId(),
            'user_id' => $entity->getUserId(),
            'previous_entry_id' => $entity->getPreviousEntryId(),
            'book_number' => $entity->getBookNumber() ?? '',
            'username' => $entity->getUsername() ?? 'Anonymous',
            'previous_username' => $entity->getPreviousUsername(),
            'created_at' => $entity->getCreatedAt(),
            'text_response' => $entity->getTextResponse(),
            'photo_url' => $photoService ? $photoService->buildPublicUrl($entity->getPhotoPath()) : null,
        ];
    }

    private function renderErrorPage(string $lang): string {
        echo "<h1>Ошибка</h1><p>Сервис временно недоступен.</p>";
        return '';
    }
}