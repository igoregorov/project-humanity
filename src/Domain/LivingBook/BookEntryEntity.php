<?php

declare(strict_types=1);

namespace App\Domain\LivingBook;

final class BookEntryEntity implements BookEntryInterface
{
    private readonly int $id;
    private readonly int $bookId;
    private readonly int $userId;
    private readonly ?int $previousEntryId;
    private readonly string $textResponse;
    private readonly ?string $photoPath;
    private readonly ?string $createdAt;

    /**
     * Presentation-only fields.
     * Не являются частью записи, используются только для отображения.
     */
    private readonly ?string $username;
    private readonly ?string $bookNumber;
    private readonly ?string $previousUsername;

    public function __construct(
        int $id,
        int $bookId,
        int $userId,
        ?int $previousEntryId,
        string $textResponse,
        ?string $photoPath,
        ?string $createdAt,
        ?string $username = null,
        ?string $bookNumber = null,
        ?string $previousUsername = null
    ) {
        $this->id = $id;
        $this->bookId = $bookId;
        $this->userId = $userId;
        $this->previousEntryId = $previousEntryId;
        $this->textResponse = $textResponse;
        $this->photoPath = $photoPath;
        $this->createdAt = $createdAt;
        $this->username = $username;
        $this->bookNumber = $bookNumber;
        $this->previousUsername = $previousUsername;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBookId(): int
    {
        return $this->bookId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPreviousEntryId(): ?int
    {
        return $this->previousEntryId;
    }

    public function getTextResponse(): string
    {
        return $this->textResponse;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    /**
     * @return string[]
     */
    public function getPhotoPaths(): array
    {
        return $this->photoPath === null ? [] : [$this->photoPath];
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getBookNumber(): ?string
    {
        return $this->bookNumber;
    }

    public function getPreviousUsername(): ?string
    {
        return $this->previousUsername;
    }
}