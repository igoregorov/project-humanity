<?php
declare(strict_types=1);

namespace App\Domain\Books;

final class BookChapter
{
    public function __construct(
        public string $id,
        public string $title,
        public string $contentHtml,
    ) {
    }
}