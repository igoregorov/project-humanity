<?php
declare(strict_types=1);
// src/View/AuthData.php

namespace App\View;

use App\Application\LocalizedContentService;
use App\Domain\Auth\User;

class AuthData implements TemplateDataInterface
{
    public function __construct(
        public readonly LocalizedContentService $translator,
        public readonly string $lang_code,
        public readonly string $action,
        public readonly ?User $user = null,
        public readonly array $errors = [],
        public readonly array $oldInput = []
    ) {}
}