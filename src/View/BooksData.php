<?php
declare(strict_types=1);

namespace App\View;

use App\Domain\Books\BookChapter;
use App\Domain\Books\BookSummary;

final class BooksData implements TemplateDataInterface
{
    /**
     * @param BookChapter[] $chapters
     * @param BookSummary[] $books
     */
    public function __construct(
        public string $lang_code,
        public bool $reader = false,
        public string $title = '',
        public string $toc = '',
        public array $chapters = [],
        public array $books = [],
        public string $message = '',
    ) {
    }
}