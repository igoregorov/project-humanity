<?php

declare(strict_types=1);

namespace App\Domain\LivingBook;

interface BookRepositoryInterface
{
    public function findByNumber(string $uniqueNumber): ?BookEntity;

    public function findById(int $id): ?BookEntity;

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
     * @return array{books: list<array{id:int, number:string}>, entries:list<array{id:int, book_id:int, prev_id:?int, label:string}>}
     */
    public function getAllForGraph(): array;

    /**
     * @return list<BookEntryEntity>
     */
    public function getHistoryByBookId(int $bookId): array;

    public function findBookIdByNumber(string $uniqueNumber): ?int;
}