<?php
declare(strict_types=1);

namespace App\Domain\Books;

interface BookRepositoryInterface
{
    /**
     * @return BookSummary[]
     */
    public function findAll(string $lang): array;

    public function findSummary(string $slug, string $lang): ?BookSummary;

    public function findReader(string $slug, string $lang): ?BookReader;
}