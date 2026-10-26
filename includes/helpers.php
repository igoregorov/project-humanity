<?php

declare(strict_types=1);

use App\Infrastructure\Security\HtmlSanitizer;

function sanitizeHtml(string $html): string
{
    static $sanitizer = null;

    if (!$sanitizer instanceof HtmlSanitizer) {
        $sanitizer = new HtmlSanitizer();
    }

    return $sanitizer->sanitize($html);
}