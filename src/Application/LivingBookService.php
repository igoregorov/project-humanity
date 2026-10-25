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

        // Ограничение: 1 пользователь = 1 запись всего (или 1 на книгу? В задаче сказано "один отклик на одну конкретную книгу",
        // но текущая логика existsByUserId проверяет глобально.
        // Для MVP оставляем глобальное ограничение как в исходном коде, либо меняем логику.
        // Судя по коду выше: existsByUserId -> глобально.
        // Если нужно "1 на книгу", то проверка должна быть existsByUserIdAndBookId.
        // Оставим как есть в оригинальном коде (глобально), так как это проще для старта.

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
        $books = $rawData['books'];
        $entries = $rawData['entries'];

        $nodes = [];
        $edges = [];

        // Индексируем отзывы по книге
        $entriesByBook = [];
        foreach ($entries as $entry) {
            $entriesByBook[$entry['book_id']][] = $entry;
        }

        // 1. Узлы КНИГ
        foreach ($books as $book) {
            $nodes[] = [
                'id' => 'book_' . $book['id'],
                'label' => $book['number'],
                'group' => 'books',
                'shape' => 'hexagon',
                'size' => 30,
                'color' => '#9c27b0', // Purple
                'title' => 'Книга: ' . $book['number']
            ];
        }

        // 2. Узлы ОТЗЫВОВ и связи
        foreach ($books as $book) {
            $bookEntries = $entriesByBook[$book['id']] ?? [];

            if (empty($bookEntries)) {
                continue;
            }

            $previousNodeId = null;
            $firstEntryProcessed = false;

            foreach ($bookEntries as $entry) {
                $nodeId = 'entry_' . $entry['id'];

                $nodes[] = [
                    'id' => $nodeId,
                    'label' => $entry['label'],
                    'group' => 'entries',
                    'shape' => 'box',
                    'size' => 15,
                    'color' => '#2196f3', // Blue
                    'title' => 'Отзыв #' . $entry['id']
                ];

                // Связь: Книга -> Первый Отзыв
                if (!$firstEntryProcessed) {
                    $edges[] = [
                        'from' => 'book_' . $book['id'],
                        'to' => $nodeId,
                        'color' => '#aaaaaa',
                        'width' => 2,
                        'smooth' => true
                    ];
                    $firstEntryProcessed = true;
                }

                // Связь: Предыдущий Отзыв -> Текущий
                if ($previousNodeId !== null) {
                    $edges[] = [
                        'from' => $previousNodeId,
                        'to' => $nodeId,
                        'color' => '#cccccc',
                        'width' => 1,
                        'dashes' => true,
                        'arrows' => 'to'
                    ];
                }

                $previousNodeId = $nodeId;
            }
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges
        ];
    }

    /**
     * Детальная информация по книге для таймлайна (AJAX response).
     */
    public function getBookDetails(int $bookId): array
    {
        $history = $this->bookRepository->getHistoryByBookId($bookId);

        // Получаем номер книги из первого элемента истории или делаем отдельный запрос,
        // но так как история пустая может быть, лучше найти книгу явно.
        // Однако, в getHistoryByBookId мы уже джойним books.
        // Если история пуста, вернется []. Тогда номер неизвестен.
        // Для надежности:
        $book = $this->bookRepository->findById($bookId); // Нужно добавить этот метод в интерфейс/репо, если его нет
        // Если findById нет, можно использовать findByNumber, но у нас тут ID.
        // Допустим, в репозитории есть findById. Если нет - временно используем первый элемент истории.

        $bookNumber = '';
        if (!empty($history)) {
            $bookNumber = $history[0]->getBookNumber() ?? '';
        } elseif ($book) {
            $bookNumber = $book->getUniqueNumber();
        }

        $timeline = [];
        foreach ($history as $entry) {
            $timeline[] = [
                'id' => $entry->getId(),
                'username' => $entry->getUsername() ?? 'Аноним',
                'text' => htmlspecialchars($entry->getTextResponse(), ENT_QUOTES, 'UTF-8'),
                'date' => $entry->getCreatedAt(),
                'photoUrl' => $entry->getPhotoPath() ? '/uploads/books/' . basename($entry->getPhotoPath()) : null
            ];
        }

        return [
            'book_id' => $bookId,
            'book_number' => $bookNumber,
            'events' => $timeline
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