<?php
declare(strict_types=1);

namespace App\View;

final class BooksData implements TemplateDataInterface
{
    public function __construct(
        public string $lang_code,
        public bool $reader = false,
        public string $title = '',
        public string $toc = '',
        public array $chapters = [],
        public array $books = [],
        public string $message = '',
        public string $author = '',
        public string $description = '',
        public ?string $coverUrl = null,
        public ?string $updatedAt = null,
        public array $formats = [],
    ) {
    }
}