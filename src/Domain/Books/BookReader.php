<?php
declare(strict_types=1);

namespace App\Domain\Books;

final class BookReader
{
    /**
     * @param BookChapter[] $chapters
     * @param BookFormat[]  $formats
     */
    public function __construct(
        public string $slug,
        public string $title,
        public string $tocHtml,
        public array $chapters,
        public string $description = '',
        public ?string $author = null,
        public ?string $coverUrl = null,
        public ?string $updatedAt = null,
        public array $formats = [],
    ) {
    }
}