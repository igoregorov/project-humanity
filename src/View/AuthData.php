<?php

declare(strict_types=1);

namespace App\View;

use App\Application\LocalizedContentService;
use App\Domain\Auth\User;
use App\Domain\LivingBook\BookEntryEntity;

final class AuthData implements TemplateDataInterface
{
    public function __construct(
        public readonly LocalizedContentService $translator,
        public readonly string $lang_code,
        public readonly string $action,
        public readonly ?User $user = null,
        public readonly array $errors = [],
        public readonly array $oldInput = [],
        public readonly string $csrf_token = '',
        public readonly ?string $avatarUrl = null,
        public readonly ?BookEntryEntity $bookEntry = null,
        public readonly ?string $message = null
    ) {
    }
}