<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\LivingBook\BookEntity;
use App\Domain\LivingBook\BookEntryEntity;
use App\Domain\LivingBook\BookRepositoryInterface;
use PDO;
use PDOException;
use Throwable;

final class DatabaseBookRepository implements BookRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByNumber(string $uniqueNumber): ?BookEntity
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, unique_number, created_at
             FROM books
             WHERE unique_number = :unique_number
             LIMIT 1'
        );

        $stmt->execute(['unique_number' => $uniqueNumber]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrateBook($row) : null;
    }

    public function findOrCreateByNumber(string $uniqueNumber): BookEntity
    {
        $started = !$this->pdo->inTransaction();

        if ($started) {
            $this->pdo->beginTransaction();
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, unique_number, created_at
                 FROM books
                 WHERE unique_number = :unique_number
                 FOR UPDATE'
            );

            $stmt->execute(['unique_number' => $uniqueNumber]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($row)) {
                if ($started) {
                    $this->pdo->commit();
                }

                return $this->hydrateBook($row);
            }

            $insert = $this->pdo->prepare(
                'INSERT INTO books (unique_number, created_at)
                 VALUES (:unique_number, NOW())'
            );

            $insert->execute(['unique_number' => $uniqueNumber]);

            $id = (int) $this->pdo->lastInsertId();

            if ($started) {
                $this->pdo->commit();
            }

            return new BookEntity($id, $uniqueNumber, null);
        } catch (PDOException $e) {
            if ($started && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($e->getCode() === '23000') {
                $stmt = $this->pdo->prepare(
                    'SELECT id, unique_number, created_at
                     FROM books
                     WHERE unique_number = :unique_number
                     LIMIT 1'
                );

                $stmt->execute(['unique_number' => $uniqueNumber]);

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (is_array($row)) {
                    return $this->hydrateBook($row);
                }
            }

            throw $e;
        } catch (Throwable $e) {
            if ($started && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function findByUserId(int $userId): ?BookEntryEntity
    {
        $sql = <<<'SQL'
            SELECT
                be.id,
                be.book_id,
                be.user_id,
                be.previous_entry_id,
                be.text_response,
                be.photo_path,
                be.created_at,
                u.username,
                b.unique_number AS book_number,
                pu.username AS previous_username
            FROM book_entries be
            LEFT JOIN users u ON u.id = be.user_id
            LEFT JOIN books b ON b.id = be.book_id
            LEFT JOIN book_entries pe ON pe.id = be.previous_entry_id
            LEFT JOIN users pu ON pu.id = pe.user_id
            WHERE be.user_id = :user_id
            LIMIT 1
            SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrateEntry($row) : null;
    }

    public function existsByUserId(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM book_entries
             WHERE user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchColumn() !== false;
    }

    public function findLatestEntryByBookId(int $bookId): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT id
             FROM book_entries
             WHERE book_id = :book_id
             ORDER BY created_at DESC, id DESC
             LIMIT 1'
        );

        $stmt->execute(['book_id' => $bookId]);

        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    public function createEntry(BookEntryEntity $entry): int
    {
        $started = !$this->pdo->inTransaction();

        if ($started) {
            $this->pdo->beginTransaction();
        }

        try {
            $prevStmt = $this->pdo->prepare(
                'SELECT id
                 FROM book_entries
                 WHERE book_id = :book_id
                 ORDER BY created_at DESC, id DESC
                 LIMIT 1
                 FOR UPDATE'
            );

            $prevStmt->execute(['book_id' => $entry->getBookId()]);

            $prevId = $prevStmt->fetchColumn();
            $previousEntryId = $prevId === false ? null : (int) $prevId;

            $insert = $this->pdo->prepare(
                'INSERT INTO book_entries
                    (book_id, user_id, previous_entry_id, text_response, photo_path, created_at)
                 VALUES
                    (:book_id, :user_id, :previous_entry_id, :text_response, :photo_path, NOW())'
            );

            $insert->bindValue(':book_id', $entry->getBookId(), PDO::PARAM_INT);
            $insert->bindValue(':user_id', $entry->getUserId(), PDO::PARAM_INT);

            if ($previousEntryId === null) {
                $insert->bindValue(':previous_entry_id', null, PDO::PARAM_NULL);
            } else {
                $insert->bindValue(':previous_entry_id', $previousEntryId, PDO::PARAM_INT);
            }

            $insert->bindValue(':text_response', $entry->getTextResponse(), PDO::PARAM_STR);

            if ($entry->getPhotoPath() === null) {
                $insert->bindValue(':photo_path', null, PDO::PARAM_NULL);
            } else {
                $insert->bindValue(':photo_path', $entry->getPhotoPath(), PDO::PARAM_STR);
            }

            $insert->execute();

            $id = (int) $this->pdo->lastInsertId();

            if ($started) {
                $this->pdo->commit();
            }

            return $id;
        } catch (Throwable $e) {
            if ($started && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @return BookEntryEntity[]
     */
    public function getHistoryByBookId(int $bookId): array
    {
        $sql = <<<'SQL'
            SELECT
                be.id,
                be.book_id,
                be.user_id,
                be.previous_entry_id,
                be.text_response,
                be.photo_path,
                be.created_at,
                u.username,
                b.unique_number AS book_number,
                pu.username AS previous_username
            FROM book_entries be
            LEFT JOIN users u ON u.id = be.user_id
            LEFT JOIN books b ON b.id = be.book_id
            LEFT JOIN book_entries pe ON pe.id = be.previous_entry_id
            LEFT JOIN users pu ON pu.id = pe.user_id
            WHERE be.book_id = :book_id
            ORDER BY be.created_at ASC, be.id ASC
            SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['book_id' => $bookId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn (array $row): BookEntryEntity => $this->hydrateEntry($row),
            $rows
        );
    }

    /**
     * @return BookEntryEntity[]
     */
    public function getRecentEntries(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        $sql = <<<'SQL'
            SELECT
                be.id,
                be.book_id,
                be.user_id,
                be.previous_entry_id,
                be.text_response,
                be.photo_path,
                be.created_at,
                u.username,
                b.unique_number AS book_number,
                pu.username AS previous_username
            FROM book_entries be
            LEFT JOIN users u ON u.id = be.user_id
            LEFT JOIN books b ON b.id = be.book_id
            LEFT JOIN book_entries pe ON pe.id = be.previous_entry_id
            LEFT JOIN users pu ON pu.id = pe.user_id
            ORDER BY be.created_at DESC, be.id DESC
            LIMIT :limit
            SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn (array $row): BookEntryEntity => $this->hydrateEntry($row),
            $rows
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateBook(array $row): BookEntity
    {
        return new BookEntity(
            (int) $row['id'],
            (string) $row['unique_number'],
            isset($row['created_at']) ? (string) $row['created_at'] : null
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateEntry(array $row): BookEntryEntity
    {
        return new BookEntryEntity(
            (int) $row['id'],
            (int) $row['book_id'],
            (int) $row['user_id'],
            isset($row['previous_entry_id']) && $row['previous_entry_id'] !== null
                ? (int) $row['previous_entry_id']
                : null,
            (string) $row['text_response'],
            isset($row['photo_path']) ? (string) $row['photo_path'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['username']) ? (string) $row['username'] : null,
            isset($row['book_number']) ? (string) $row['book_number'] : null,
            isset($row['previous_username']) ? (string) $row['previous_username'] : null
        );
    }
}