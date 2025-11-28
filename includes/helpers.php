<?php
declare(strict_types=1);

/**
 * Безопасно очищает HTML-контент, разрешая только белый список тегов.
 * Особое внимание уделено обработке ссылок: атрибут href проходит строгую валидацию.
 */
function sanitizeHtml(string $html): string
{
    // Убираем двойное HTML-экранирование (на случай, если строка уже прошла через htmlspecialchars)
    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Разрешённые теги
    $allowedTags = '<p><br><strong><em><h3><ul><ol><li><blockquote><a>';
    $clean = strip_tags($html, $allowedTags);

    // Обработка ссылок: оставляем только безопасные href
    return preg_replace_callback(
        '/<a\s+([^>]*)>/i',
        static function (array $matches): string {
            // Извлекаем значение href (в двойных или одинарных кавычках)
            if (!preg_match('/href\s*=\s*["\']([^"\']*)["\']/i', $matches[1], $m)) {
                return '<a>';
            }

            $hrefRaw = $m[1];
            $hrefDecoded = trim(html_entity_decode($hrefRaw, ENT_QUOTES, 'UTF-8'));

            // Разрешаем только:
            // - относительные ссылки: начинающиеся с ?, /, #
            // - абсолютные ссылки на доверенные домены
            $isAllowed = (
                preg_match('/^[?\/#]/', $hrefDecoded) ||
                str_starts_with($hrefDecoded, 'https://projecthumanity.space') ||
                str_starts_with($hrefDecoded, 'https://t.me/')
            );

            if ($isAllowed) {
                // Экранируем исходное значение href для безопасного вывода в HTML
                $hrefSafe = htmlspecialchars($hrefRaw, ENT_QUOTES, 'UTF-8');
                return "<a href=\"{$hrefSafe}\">";
            }

            return '<a>';
        },
        $clean
    );
}