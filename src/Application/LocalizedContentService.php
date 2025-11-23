<?php
declare(strict_types=1);

namespace App\Application;

use App\Domain\ContentRepositoryInterface;
use App\Services\LanguageService;

class LocalizedContentService
{
    public function __construct(
        private readonly ContentRepositoryInterface $contentRepo,
        private readonly LanguageService $langService
    ) {}

    public function translate(string $lang, string $key, ?string $fallback = null): string
    {
        $lang = $this->langService->validate($lang);
        $value = $this->contentRepo->get($lang, $key);
        return is_string($value) ? $value : ($fallback ?? $key);
    }

    public function translateArray(string $lang, string $key, array $fallback = []): array
    {
        $lang = $this->langService->validate($lang);
        $value = $this->contentRepo->get($lang, $key);
        return is_array($value) ? $value : $fallback;
    }
}