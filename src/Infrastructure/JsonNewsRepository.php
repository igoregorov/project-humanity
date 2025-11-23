<?php
declare(strict_types=1);

// src/Repositories/JsonNewsRepository.php

namespace App\Infrastructure;

use App\Domain\NewsRepositoryInterface;

class JsonNewsRepository implements NewsRepositoryInterface
{
    public function __construct(private readonly string $newsPath) {}

    public function findByLang(string $lang): array
    {
        $lang = in_array($lang, ['ru', 'en']) ? $lang : 'ru';
        $file = $this->newsPath . "/$lang.json";

        if (!file_exists($file)) {
            return [];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return [];
        }

        return array_filter($data, fn($item) => is_array($item) && isset($item['date']));
    }
}