<?php
declare(strict_types=1);
namespace App\Domain\LivingBook;

interface BookRepositoryInterface
{
    /**
     * Найти книгу по уникальному номеру или создать новую
     */
    public function findOrCreateByNumber(string $uniqueNumber): BookEntity;

    /**
     * Сохранить запись владельца
     */
    public function saveEntry(BookEntryEntity $entry): void;

    /**
     * Получить всю историю книги (все записи)
     */
    public function getHistoryByBookId(int $bookId): array;

    /**
     * Получить последние N записей со всей системы (для главной страницы графа)
     */
    public function getRecentEntries(int $limit = 20): array;
}