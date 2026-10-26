<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

final class LivingBookValidationException extends RuntimeException
{
    /**
     * @var array<string, string>
     */
    private array $errors;

    /**
     * @param array<string, string> $errors [field => translation_key]
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct('living_book_validation');
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}