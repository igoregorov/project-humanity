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
     * @param array<string, mixed>|null $photoFile
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
                null,
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

    /**
     * @return BookEntryEntity[]
     */
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

    /**
     * @return BookEntryEntity[]
     */
    public function getRecentEntries(int $limit = 50): array
    {
        return $this->bookRepository->getRecentEntries($limit);
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