<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\ContentRepositoryInterface;

class JsonContentRepository implements ContentRepositoryInterface
{
    public function __construct(private readonly string $localesPath) {}

    public function get(string $lang, string $key): string|array|null
    {
        $data = $this->loadJson($lang);
        return $data[$key] ?? null;
    }

    public function getAll(string $lang): array
    {
        return $this->loadJson($lang);
    }

    private function loadJson(string $lang): array
    {
        $file = rtrim($this->localesPath, '/\\') . "/$lang.json";
        if (!file_exists($file)) return [];
        $json = json_decode(file_get_contents($file), true);
        return is_array($json) ? $json : [];
    }
}