<?php
declare(strict_types=1);

namespace App\Domain\Books;

final class BookFormat
{
    public function __construct(
        public string $type,
        public string $label,
        public string $url,
    ) {
    }
}