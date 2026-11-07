<?php
declare(strict_types=1);

namespace App\Domain\Books;

final class BookSummary
{
    /**
     * @param BookFormat[] $formats
     */
    public function __construct(
        public string $slug,
        public string $title,
        public string $description = '',
        public ?string $author = null,
        public ?string $language = null,
        public ?string $coverUrl = null,
        public ?string $updatedAt = null,
        public array $formats = [],
        public bool $featured = false,
        public int $order = 100,
    ) {
    }
}