<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\ItemsRepositoryInterface;
use PDO;

abstract class AbstractDatabaseItemsRepository implements ItemsRepositoryInterface
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $itemsCache = [];

    /**
     * @var array<string, array{widget_title: string, no_items: string}>
     */
    private array $widgetCache = [];

    public function __construct(
        protected readonly PDO $pdo
    ) {
    }

    final public function findByLang(string $lang): array
    {
        $lang = $this->normalizeLang($lang);

        if (isset($this->itemsCache[$lang])) {
            return $this->itemsCache[$lang];
        }

        $sql = sprintf(
            'SELECT %s FROM %s WHERE lang = :lang AND is_active = 1 ORDER BY %s',
            $this->selectColumns(),
            $this->table(),
            $this->orderBy()
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['lang' => $lang]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->normalizeRow($row);
        }

        return $this->itemsCache[$lang] = $items;
    }

    final public function getWidgetData(string $lang): array
    {
        $lang = $this->normalizeLang($lang);

        if (isset($this->widgetCache[$lang])) {
            return $this->widgetCache[$lang];
        }

        $stmt = $this->pdo->prepare(
            'SELECT title, no_items FROM widget_texts WHERE widget = :widget AND lang = :lang LIMIT 1'
        );
        $stmt->execute([
            'widget' => $this->widgetKey(),
            'lang' => $lang,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($row)) {
            $data = [
                'widget_title' => (string) ($row['title'] ?? ''),
                'no_items' => (string) ($row['no_items'] ?? ''),
            ];
        } else {
            $data = $this->defaultWidgetData();
        }

        return $this->widgetCache[$lang] = $data;
    }

    abstract protected function table(): string;

    abstract protected function widgetKey(): string;

    /**
     * @return array{widget_title: string, no_items: string}
     */
    abstract protected function defaultWidgetData(): array;

    abstract protected function selectColumns(): string;

    abstract protected function orderBy(): string;

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    abstract protected function normalizeRow(array $row): array;

    private function normalizeLang(string $lang): string
    {
        return $lang === '' ? 'ru' : $lang;
    }
}