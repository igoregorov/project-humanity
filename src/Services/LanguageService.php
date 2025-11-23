<?php
declare(strict_types=1);

namespace App\Services;

class LanguageService
{
    /**
     * @param array<string> $allowedLanguages Список кодов: ['ru', 'en']
     * @param array<string, string> $languageNames Названия: ['ru' => 'Русский', ...]
     */
    public function __construct(
        private readonly array $allowedLanguages,
        private readonly array $languageNames
    ) {}

    public function validate(string $lang): string
    {
        return in_array($lang, $this->allowedLanguages, true) ? $lang : 'ru';
    }

    public function getAllowedLanguages(): array
    {
        return $this->allowedLanguages;
    }

    public function getLanguageName(string $lang): string
    {
        return $this->languageNames[$lang] ?? $lang;
    }
}