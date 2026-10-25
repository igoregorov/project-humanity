<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Exception\LivingBookValidationException;
use App\Domain\LivingBook\BookEntryEntity;
use App\Domain\LivingBook\BookRepositoryInterface;
use App\Infrastructure\Security\BookPhotoUploadService;
use PDOException;
use RuntimeException;
use Throwable;

final class LivingBookService
{
    private BookRepositoryInterface $bookRepository;
    private BookPhotoUploadService $photoUploadService;

    public function __construct(
        BookRepositoryInterface $bookRepository,
        BookPhotoUploadService $photoUploadService
    ) {
        $this->bookRepository = $bookRepository;
        $this->photoUploadService = $photoUploadService;
    }

    /**
     * Создание нового отклика пользователя.
     */
    public function createUserEntry(
        int $userId,
        string $uniqueNumber,
        string $textResponse,
        ?array $photoFile
    ): BookEntryEntity {
        $number = $this->validateNumber($uniqueNumber);
        $text = $this->validateText($textResponse);

        if ($this->bookRepository->existsByUserId($userId)) {
            throw new RuntimeException('living_book_error_duplicate_user');
        }

        $photoPath = null;

        if ($photoFile !== null) {
            $photoPath = $this->photoUploadService->uploadOne($photoFile, $userId);
        }

        try {
            $book = $this->bookRepository->findOrCreateByNumber($number);

            $entry = new BookEntryEntity(
                0,
                $book->getId(),
                $userId,
                null, // previous_entry_id будет установлен в репозитории автоматически
                $text,
                $photoPath,
                null
            );

            $id = $this->bookRepository->createEntry($entry);

            $saved = $this->bookRepository->findByUserId($userId);

            if ($saved !== null) {
                return $saved;
            }

            return new BookEntryEntity(
                $id,
                $book->getId(),
                $userId,
                null,
                $text,
                $photoPath,
                date('Y-m-d H:i:s'),
                null,
                $book->getUniqueNumber(),
                null
            );
        } catch (PDOException $e) {
            if ($photoPath !== null) {
                $this->photoUploadService->deleteRelativePath($photoPath);
            }

            if ($e->getCode() === '23000') {
                throw new RuntimeException('living_book_error_duplicate_user', 0, $e);
            }

            throw $e;
        } catch (Throwable $e) {
            if ($photoPath !== null) {
                $this->photoUploadService->deleteRelativePath($photoPath);
            }

            throw $e;
        }
    }

    public function getUserEntry(int $userId): ?BookEntryEntity
    {
        return $this->bookRepository->findByUserId($userId);
    }

    public function getHistoryByNumber(string $uniqueNumber): array
    {
        $number = trim($uniqueNumber);

        if ($number === '') {
            return [];
        }

        $book = $this->bookRepository->findByNumber($number);

        if ($book === null) {
            return [];
        }

        return $this->bookRepository->getHistoryByBookId($book->getId());
    }

    public function getRecentEntries(int $limit = 50): array
    {
        return $this->bookRepository->getRecentEntries($limit);
    }

    /**
     * Формирует данные для визуализации графа.
     * Nodes: Книги (hexagon) + Отзывы (box).
     * Edges: Книга -> Первый Отзыв, Отзыв_N -> Отзыв_N+1.
     */
    public function prepareGraphData(): array
    {
        $rawData = $this->bookRepository->getAllForGraph();
        $rawBooks = is_array($rawData['books'] ?? null) ? $rawData['books'] : [];
        $rawEntries = is_array($rawData['entries'] ?? null) ? $rawData['entries'] : [];

        $booksById = [];
        $entriesById = [];

        foreach ($rawBooks as $book) {
            if (!is_array($book)) {
                continue;
            }

            $id = filter_var($book['id'] ?? null, FILTER_VALIDATE_INT);
            if (!is_int($id) || $id < 1 || isset($booksById[$id])) {
                continue;
            }

            $booksById[$id] = [
                'id' => $id,
                'number' => (string) ($book['number'] ?? ''),
            ];
        }

        foreach ($rawEntries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $id = filter_var($entry['id'] ?? null, FILTER_VALIDATE_INT);
            $bookId = filter_var($entry['book_id'] ?? null, FILTER_VALIDATE_INT);

            if (
                !is_int($id)
                || $id < 1
                || !is_int($bookId)
                || $bookId < 1
                || !isset($booksById[$bookId])
                || isset($entriesById[$id])
            ) {
                continue;
            }

            $prevId = isset($entry['prev_id']) && $entry['prev_id'] !== ''
                ? filter_var($entry['prev_id'], FILTER_VALIDATE_INT)
                : null;

            $entriesById[$id] = [
                'id' => $id,
                'book_id' => $bookId,
                'prev_id' => is_int($prevId) && $prevId > 0 ? $prevId : null,
                'label' => mb_substr((string) ($entry['label'] ?? 'Отклик'), 0, 80, 'UTF-8'),
                'username' => (string) ($entry['username'] ?? 'Аноним'),
                'created_at' => (string) ($entry['created_at'] ?? ''),
                'text_response' => (string) ($entry['text_response'] ?? ''),
            ];
        }

        $nodes = [];
        $edges = [];
        $nodeIds = [];
        $edgeIds = [];

        foreach ($booksById as $book) {
            $id = 'book_' . $book['id'];
            $nodeIds[$id] = true;

            $nodes[] = [
                'id' => $id,
                'label' => 'Книга ' . $book['number'],
                'group' => 'books',
                'shape' => 'hexagon',
                'color' => '#9c27b0',
                'title' => 'Книга ' . $book['number'],
                'details' => [
                    'kind' => 'book',
                    'book_number' => $book['number'],
                ],
            ];
        }

        foreach ($entriesById as $entry) {
            $id = 'entry_' . $entry['id'];
            $nodeIds[$id] = true;

            $nodes[] = [
                'id' => $id,
                'label' => 'Отклик #' . $entry['id'] . ': ' . $entry['label'],
                'group' => 'entries',
                'shape' => 'box',
                'color' => '#2196f3',
                'title' => 'Отклик #' . $entry['id'],
                'details' => [
                    'kind' => 'entry',
                    'entry_id' => $entry['id'],
                    'book_number' => $booksById[$entry['book_id']]['number'],
                    'username' => $entry['username'],
                    'created_at' => $entry['created_at'],
                    'text_response' => $entry['text_response'],
                ],
            ];
        }

        foreach ($entriesById as $entry) {
            $to = 'entry_' . $entry['id'];

            if ($entry['prev_id'] === null) {
                $from = 'book_' . $entry['book_id'];
            } else {
                $previous = $entriesById[$entry['prev_id']] ?? null;

                // Не соединяем с отсутствующим, самим собой или чужим экземпляром.
                if (
                    $previous === null
                    || $previous['id'] === $entry['id']
                    || $previous['book_id'] !== $entry['book_id']
                ) {
                    continue;
                }

                $from = 'entry_' . $previous['id'];
            }

            if (!isset($nodeIds[$from], $nodeIds[$to])) {
                continue;
            }

            $edgeKey = $from . '>' . $to;
            if (isset($edgeIds[$edgeKey])) {
                continue;
            }
            $edgeIds[$edgeKey] = true;

            $edges[] = [
                'from' => $from,
                'to' => $to,
                'arrows' => 'to',
                'color' => '#aaaaaa',
                'width' => 1,
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    /**
     * Детальная информация по книге для таймлайна (AJAX response).
     */
    public function getBookDetails(int $bookId): array
    {
        if ($bookId <= 0) {
            return [
                'book_id' => $bookId,
                'book_number' => '',
                'events' => [],
            ];
        }

        $book = $this->bookRepository->findById($bookId);
        $history = $this->bookRepository->getHistoryByBookId($bookId);

        $timeline = [];

        foreach ($history as $entry) {
            $timeline[] = [
                'id' => $entry->getId(),
                'username' => $entry->getUsername() ?? 'Аноним',
                'text' => htmlspecialchars(
                    $entry->getTextResponse(),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ),
                'date' => $entry->getCreatedAt(),
                'photoUrl' => $this->photoUploadService->buildPublicUrl(
                    $entry->getPhotoPath()
                ),
            ];
        }

        return [
            'book_id' => $bookId,
            'book_number' => $book?->getUniqueNumber() ?? '',
            'events' => $timeline,
        ];
    }

    private function validateNumber(string $value): string
    {
        $value = trim($value);

        if (
            $value === ''
            || mb_strlen($value, 'UTF-8') > 100
            || preg_match('/[\x00-\x1F\x7F]/u', $value) === 1
            || preg_match('#^[\p{L}\p{N} .\-_/]+$#u', $value) !== 1
        ) {
            throw new LivingBookValidationException([
                'unique_number' => 'living_book_error_number',
            ]);
        }

        return $value;
    }

    private function validateText(string $value): string
    {
        $value = trim($value);
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = trim($value);

        if (
            $value === ''
            || mb_strlen($value, 'UTF-8') > 5000
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value) === 1
        ) {
            throw new LivingBookValidationException([
                'text_response' => 'living_book_error_text',
            ]);
        }

        return $value;
    }
}