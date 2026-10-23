<?php
declare(strict_types=1);
namespace App\Domain\LivingBook;

use DateTimeImmutable;

interface BookEntryInterface
{
    public function getId(): ?int;
    public function getBookId(): int;
    public function getUserId(): int;
    public function getTextResponse(): string;
    public function getPhotoPaths(): array;
    public function getCreatedAt(): DateTimeImmutable;
}