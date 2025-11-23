<?php
declare(strict_types=1);

namespace App\Domain;
interface RepositoryInterface
{
    public function findByLang(string $lang): array;
}