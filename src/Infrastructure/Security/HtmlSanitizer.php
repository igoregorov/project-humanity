<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use DOMDocument;
use DOMElement;
use DOMNode;
use RuntimeException;

final class HtmlSanitizer
{
    /**
     * Разрешены только эти теги. Атрибуты, кроме безопасного href у <a>,
     * не сохраняются.
     *
     * @var array<string, true>
     */
    private const ALLOWED_TAGS = [
        'p' => true,
        'br' => true,
        'strong' => true,
        'em' => true,
        'h3' => true,
        'ul' => true,
        'ol' => true,
        'li' => true,
        'blockquote' => true,
        'a' => true,
    ];

    public function sanitize(string $html): string
    {
        if (!class_exists(DOMDocument::class)) {
            throw new RuntimeException(
                'Для безопасной очистки HTML необходимо расширение PHP ext-dom.'
            );
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="sanitizer-root">'
                . $html
                . '</div>',
                LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            if (!$loaded) {
                return htmlspecialchars(
                    $html,
                    ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                    'UTF-8'
                );
            }

            $root = $document->getElementById('sanitizer-root');

            if (!$root instanceof DOMElement) {
                return htmlspecialchars(
                    $html,
                    ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                    'UTF-8'
                );
            }

            return $this->sanitizeChildren($root);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }
    }

    private function sanitizeChildren(DOMNode $parent): string
    {
        $result = '';

        foreach ($parent->childNodes as $child) {
            $result .= $this->sanitizeNode($child);
        }

        return $result;
    }

    private function sanitizeNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return htmlspecialchars(
                $node->nodeValue ?? '',
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                'UTF-8'
            );
        }

        if (!$node instanceof DOMElement) {
            // Комментарии, processing instructions и прочие узлы не выводим.
            return '';
        }

        $tag = strtolower($node->tagName);

        // Скриптовое и стилизующее содержимое удаляем целиком,
        // а не просто снимаем оболочку тега.
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
            return '';
        }

        $children = $this->sanitizeChildren($node);

        if (!isset(self::ALLOWED_TAGS[$tag])) {
            // Неизвестный тег удаляем, но оставляем безопасный текст и
            // безопасные дочерние элементы.
            return $children;
        }

        if ($tag === 'br') {
            return '<br>';
        }

        if ($tag === 'a') {
            $href = $node->getAttribute('href');
            $safeHref = $this->safeHref($href);

            if ($safeHref === null) {
                return '<a>' . $children . '</a>';
            }

            $escapedHref = htmlspecialchars(
                $safeHref,
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                'UTF-8'
            );

            $external = str_starts_with($safeHref, 'https://');

            if ($external) {
                return '<a href="' . $escapedHref
                    . '" rel="noopener noreferrer">'
                    . $children
                    . '</a>';
            }

            return '<a href="' . $escapedHref . '">' . $children . '</a>';
        }

        return '<' . $tag . '>' . $children . '</' . $tag . '>';
    }

    private function safeHref(string $href): ?string
    {
        $href = trim($href);

        if ($href === '' || preg_match('/[\x00-\x20\x7F\\\\]/', $href) === 1) {
            return null;
        }

        // Разрешены локальные ссылки, но не protocol-relative //host.
        if (
            str_starts_with($href, '#')
            || str_starts_with($href, '?')
            || (
                str_starts_with($href, '/')
                && !str_starts_with($href, '//')
            )
        ) {
            return $href;
        }

        $parts = parse_url($href);

        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https') {
            return null;
        }

        // Сравнение именно с полным hostname, а не с префиксом.
        if (
            $host === 'projecthumanity.space'
            || $host === 'www.projecthumanity.space'
            || $host === 't.me'
        ) {
            return $href;
        }

        return null;
    }
}