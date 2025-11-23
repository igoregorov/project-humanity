<?php
declare(strict_types=1);

namespace App\Domain;

interface ContentRepositoryInterface
{
    public function get(string $lang, string $key): string|array|null;
    public function getAll(string $lang): array;
}