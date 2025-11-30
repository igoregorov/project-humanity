<?php
declare(strict_types=1);

namespace App\Domain;

interface ItemsRepositoryInterface
{
    public function findByLang(string $lang): array;
    public function getWidgetData(string $lang): array;
}