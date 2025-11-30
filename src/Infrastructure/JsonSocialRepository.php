<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\SocialRepositoryInterface;

class JsonSocialRepository implements SocialRepositoryInterface
{
    public function __construct(private readonly string $socialPath) {}

    public function findByLang(string $lang): array
    {
        $lang = $lang ?? 'ru';
        $file = $this->socialPath . "/$lang.json";

        if (!file_exists($file)) {
            return [];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return [];
        }

        return $data['networks'] ?? [];
    }

    public function getWidgetData(string $lang): array
    {
        $lang = $lang ?? 'ru';
        $configFile = $this->socialPath . "/$lang.config.json";

        if (!file_exists($configFile)) {
            return [
                'widget_title' => 'Мы в соцсетях',
                'no_items' => 'Нет доступных соцсетей'
            ];
        }

        $json = file_get_contents($configFile);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return [
                'widget_title' => 'Мы в соцсетях',
                'no_items' => 'Нет доступных соцсетей'
            ];
        }

        return [
            'widget_title' => $data['widget_title'] ?? 'Мы в соцсетях',
            'no_items' => $data['no_items'] ?? 'Нет доступных соцсетей'
        ];
    }
}