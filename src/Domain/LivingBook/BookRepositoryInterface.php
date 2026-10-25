<?php

declare(strict_types=1);

namespace App\Domain\LivingBook;

interface BookRepositoryInterface
{
    public function findByNumber(string $uniqueNumber): ?BookEntity;

    public function findOrCreateByNumber(string $uniqueNumber): BookEntity;

    public function findByUserId(int $userId): ?BookEntryEntity;

    public function existsByUserId(int $userId): bool;

    public function findLatestEntryByBookId(int $bookId): ?int;

    public function createEntry(BookEntryEntity $entry): int;

    /**
     * @return BookEntryEntity[]
     */
    public function getRecentEntries(int $limit = 50): array;
    /**
     * Получает все книги вместе со всеми их отзывами для визуализации графа.
     * Оптимизировано под чтение одним запросом JOIN или двумя отдельными SELECTs.
     *
     * @return array{books: list<BookEntity>, entries: list<BookEntryEntity>}
     */
    public function getAllForGraph(): array;

    /**
     * Возвращает историю отзывов конкретной книги, отсортированную по времени.
     * Используется для таймлайна при клике на узел.
     *
     * @param int $bookId
     * @return list<BookEntryEntity>
     */
    public function getHistoryByBookId(int $bookId): array;
}