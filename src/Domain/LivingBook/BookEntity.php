<?php

declare(strict_types=1);

namespace App\Domain\LivingBook;

final class BookEntity
{
    private readonly int $id;
    private readonly string $uniqueNumber;
    private readonly ?string $createdAt;

    public function __construct(
        int $id,
        string $uniqueNumber,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->uniqueNumber = $uniqueNumber;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUniqueNumber(): string
    {
        return $this->uniqueNumber;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }
}