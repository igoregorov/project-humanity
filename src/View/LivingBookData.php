<?php

declare(strict_types=1);

namespace App\View;

final class LivingBookData
{
    /**
     * @param array<int, array<string, mixed>> $entries
     * @param array<string, mixed>|null $currentUserEntry
     * @param array{
     *     success: string|null,
     *     errors: string[],
     *     old: array<string, mixed>
     * } $flash
     */
    public function __construct(
        private readonly object $translator,
        private readonly string $langCode,
        private readonly string $currentPage,
        private readonly array $entries,
        private readonly ?string $filterNumber,
        private readonly string $mode,
        private readonly ?array $currentUserEntry,
        private readonly array $flash,
        private readonly bool $dbAvailable,
        private readonly ?string $csrfToken
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'translator' => $this->translator,
            'lang_code' => $this->langCode,
            'current_page' => $this->currentPage,
            'entries' => $this->entries,
            'filterNumber' => $this->filterNumber,
            'mode' => $this->mode,
            'currentUserEntry' => $this->currentUserEntry,
            'flash' => $this->flash,
            'dbAvailable' => $this->dbAvailable,
            'csrfToken' => $this->csrfToken,
        ];
    }
}