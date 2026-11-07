<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\View\BooksData;
use League\CommonMark\CommonMarkConverter;
use RuntimeException;

final class BooksController extends AbstractController
{
    private const BOOK_DIR = BOOKS_DIR . '/project-humanity';

    public function handle(string $page, string $lang): string
    {
        $action = $_GET['action'] ?? 'index';

        if (!is_string($action)) {
            $action = 'index';
        }

        if ($action === 'read') {
            return $this->renderReader($page, $lang);
        }

        return $this->renderPage(
            'page_books.php',
            new BooksData($lang),
            $page,
            $lang
        );
    }

    private function renderReader(string $page, string $lang): string
    {
        $manifestPath = self::BOOK_DIR . '/book.json';

        if (!is_file($manifestPath) || !is_readable($manifestPath)) {
            throw new RuntimeException(
                'Не найден файл описания книги: ' . $manifestPath
            );
        }

        $manifest = json_decode(
            (string) file_get_contents($manifestPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($manifest) || !is_array($manifest['chapters'] ?? null)) {
            throw new RuntimeException('Некорректный формат book.json.');
        }

        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $chapters = [];
        $chapterIds = [];
        $chapterNumber = 0;

        foreach ($manifest['chapters'] as $chapter) {
            if (
                !is_array($chapter)
                || !isset($chapter['file'], $chapter['title'])
                || !is_string($chapter['file'])
                || !is_string($chapter['title'])
            ) {
                continue;
            }

            // Не разрешаем абсолютные пути и переходы за пределы каталога книги.
            $relativePath = str_replace('\\', '/', $chapter['file']);

            if (
                str_starts_with($relativePath, '/')
                || preg_match('#(^|/)\.\.(/|$)#', $relativePath)
            ) {
                continue;
            }

            $filePath = self::BOOK_DIR . '/' . $relativePath;

            if (!is_file($filePath) || !is_readable($filePath)) {
                continue;
            }

            $id = 'chapter-' . (++$chapterNumber);
            $chapterIds[$relativePath] = $id;

            $markdown = (string) file_get_contents($filePath);

            $chapters[] = [
                'id' => $id,
                'title' => $chapter['title'],
                'content' => $converter->convert($markdown)->getContent(),
            ];
        }

        $tocPath = self::BOOK_DIR . '/toc.md';
        $tocMarkdown = is_file($tocPath) && is_readable($tocPath)
            ? (string) file_get_contents($tocPath)
            : '';

        // Ссылки на Markdown-файлы заменяем якорями на главы книги.
        $tocMarkdown = preg_replace_callback(
            '/\]\(([^)#]+\.md)(#[^)]*)?\)/i',
            static function (array $match) use ($chapterIds): string {
                $path = str_replace('\\', '/', rawurldecode($match[1]));

                if (!isset($chapterIds[$path])) {
                    return $match[0];
                }

                return '](#' . $chapterIds[$path] . ')';
            },
            $tocMarkdown
        ) ?? $tocMarkdown;

        $tocHtml = $converter->convert($tocMarkdown)->getContent();

        $data = new BooksData(
            lang_code: $lang,
            reader: true,
            title: (string) ($manifest['title'] ?? 'Книга'),
            toc: $tocHtml,
            chapters: $chapters
        );

        return $this->renderPage('page_books.php', $data, $page, $lang);
    }
}