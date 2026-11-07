<?php

declare(strict_types=1);

namespace App\View;

final class BooksData implements TemplateDataInterface
{
    /**
     * @param array<int, array{title: string, content: string}> $chapters
     */
    public function __construct(
        public readonly string $lang_code,
        public readonly bool $reader = false,
        public readonly string $title = 'Проект Человечество: реальная утопия',
        public readonly string $toc = '',
        public readonly array $chapters = []
    ) {
    }
}