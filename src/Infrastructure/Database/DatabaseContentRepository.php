<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\ContentRepositoryInterface;
use PDO;

final class DatabaseContentRepository implements ContentRepositoryInterface
{
    /**
     * @var array<string, array<string, string|array<mixed>|null>>
     */
    private array $cache = [];

    public function __construct(
        private readonly PDO $pdo,
        private readonly string $section
    ) {
    }

    public function get(string $lang, string $key): string|array|null
    {
        $this->ensureLoaded($lang);

        return $this->cache[$lang][$key] ?? null;
    }

    public function getAll(string $lang): array
    {
        $this->ensureLoaded($lang);

        return $this->cache[$lang] ?? [];
    }

    private function ensureLoaded(string $lang): void
    {
        $lang = $lang === '' ? 'ru' : $lang;

        if (isset($this->cache[$lang])) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT item_key, value_json FROM content_values WHERE section = :section AND lang = :lang'
        );
        $stmt->execute([
            'section' => $this->section,
            'lang' => $lang,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $key = (string) ($row['item_key'] ?? '');
            if ($key === '') {
                continue;
            }

            $raw = (string) ($row['value_json'] ?? '');
            $decoded = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = $raw;
            }

            $data[$key] = $decoded;
        }

        $this->cache[$lang] = $data;
    }
}