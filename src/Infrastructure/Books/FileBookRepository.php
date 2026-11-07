<?php
declare(strict_types=1);

namespace App\Infrastructure\Books;

use App\Domain\Books\BookChapter;
use App\Domain\Books\BookFormat;
use App\Domain\Books\BookReader;
use App\Domain\Books\BookRepositoryInterface;
use App\Domain\Books\BookSummary;
use JsonException;
use League\CommonMark\CommonMarkConverter;

final class FileBookRepository implements BookRepositoryInterface
{
    private ?array $configCache = null;

    private CommonMarkConverter $converter;

    public function __construct(
        private string $booksDir,
        private string $configPath,
    ) {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * @return BookSummary[]
     */
    public function findAll(string $lang): array
    {
        $books = [];

        foreach ($this->config() as $slug => $options) {
            if (!is_string($slug) || !is_array($options)) {
                continue;
            }

            if (!$this->isEnabled($options)) {
                continue;
            }

            $book = $this->findSummary($slug, $lang);
            if ($book !== null) {
                $books[] = $book;
            }
        }

        usort($books, static function (BookSummary $a, BookSummary $b): int {
            return [$a->order, $a->slug] <=> [$b->order, $b->slug];
        });

        return $books;
    }

    public function findSummary(string $slug, string $lang): ?BookSummary
    {
        $options = $this->optionsFor($slug);
        if ($options === null || !$this->isEnabled($options)) {
            return null;
        }

        $bookDir = $this->resolveBookDir($slug, $options);
        if ($bookDir === null) {
            return null;
        }

        $manifest = $this->loadManifest($bookDir);
        if ($manifest === null) {
            return null;
        }

        return $this->buildSummary($slug, $manifest, $options, $lang);
    }

    public function findReader(string $slug, string $lang): ?BookReader
    {
        $options = $this->optionsFor($slug);
        if ($options === null || !$this->isEnabled($options)) {
            return null;
        }

        $bookDir = $this->resolveBookDir($slug, $options);
        if ($bookDir === null) {
            return null;
        }

        $manifest = $this->loadManifest($bookDir);
        if ($manifest === null) {
            return null;
        }

        $summary = $this->buildSummary($slug, $manifest, $options, $lang);
        if ($summary === null) {
            return null;
        }

        $chaptersConfig = $manifest['chapters'] ?? [];
        if (!is_array($chaptersConfig)) {
            $chaptersConfig = [];
        }

        $chapters = [];
        $chapterIdsByPath = [];
        $usedIds = [];
        $chapterNumber = 0;

        foreach ($chaptersConfig as $chapter) {
            if (
                !is_array($chapter)
                || !isset($chapter['file'], $chapter['title'])
                || !is_string($chapter['file'])
                || !is_string($chapter['title'])
            ) {
                continue;
            }

            $relativePath = $this->normalizeRelativePath($chapter['file']);
            if (!$this->isSafeRelativePath($relativePath)) {
                continue;
            }

            $fullPath = $bookDir . DIRECTORY_SEPARATOR . $relativePath;
            $realPath = realpath($fullPath);

            if (
                $realPath === false
                || !str_starts_with($realPath, $bookDir . DIRECTORY_SEPARATOR)
                || !is_file($realPath)
                || !is_readable($realPath)
            ) {
                continue;
            }

            $rawId = isset($chapter['id']) && is_string($chapter['id'])
                ? trim($chapter['id'])
                : '';

            if (
                $rawId !== ''
                && preg_match('/^[a-z0-9_-]{1,64}$/i', $rawId) === 1
                && !isset($usedIds[$rawId])
            ) {
                $id = $rawId;
            } else {
                $id = 'chapter-' . (++$chapterNumber);

                while (isset($usedIds[$id])) {
                    $id = 'chapter-' . (++$chapterNumber);
                }
            }

            $usedIds[$id] = true;
            $chapterIdsByPath[$relativePath] = $id;

            $markdown = (string) file_get_contents($realPath);
            $contentHtml = $this->converter->convert($markdown)->getContent();

            $chapters[] = new BookChapter(
                id: $id,
                title: $chapter['title'],
                contentHtml: $contentHtml,
            );
        }

        $tocHtml = $this->buildTocHtml($bookDir, $chapterIdsByPath);

        return new BookReader(
            slug: $summary->slug,
            title: $summary->title,
            tocHtml: $tocHtml,
            chapters: $chapters,
            description: $summary->description,
            author: $summary->author,
            coverUrl: $summary->coverUrl,
            updatedAt: $summary->updatedAt,
            formats: $summary->formats,
        );
    }

    private function config(): array
    {
        if ($this->configCache !== null) {
            return $this->configCache;
        }

        if (!is_file($this->configPath) || !is_readable($this->configPath)) {
            $this->configCache = [];
            return $this->configCache;
        }

        $loaded = require $this->configPath;

        $this->configCache = is_array($loaded) ? $loaded : [];

        return $this->configCache;
    }

    private function optionsFor(string $slug): ?array
    {
        if (!$this->isValidSlug($slug)) {
            return null;
        }

        $config = $this->config();

        if (!isset($config[$slug]) || !is_array($config[$slug])) {
            return null;
        }

        return $config[$slug];
    }

    private function isEnabled(array $options): bool
    {
        return ($options['enabled'] ?? true) !== false;
    }

    private function resolveBookDir(string $slug, array $options): ?string
    {
        $dir = isset($options['dir']) && is_string($options['dir'])
            ? $this->normalizeRelativePath($options['dir'])
            : $slug;

        if (!$this->isSafeRelativePath($dir)) {
            return null;
        }

        $baseDir = realpath($this->booksDir);
        if ($baseDir === false || !is_dir($baseDir)) {
            return null;
        }

        $bookDir = realpath($baseDir . DIRECTORY_SEPARATOR . $dir);
        if ($bookDir === false || !is_dir($bookDir)) {
            return null;
        }

        if (!str_starts_with($bookDir, $baseDir . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $bookDir;
    }

    private function loadManifest(string $bookDir): ?array
    {
        $manifestPath = $bookDir . '/book.json';

        if (!is_file($manifestPath) || !is_readable($manifestPath)) {
            return null;
        }

        try {
            $decoded = json_decode(
                (string) file_get_contents($manifestPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function buildSummary(string $slug, array $manifest, array $options, string $lang): ?BookSummary
    {
        $title = $this->localizedString($manifest, 'title', $lang);
        if ($title === '') {
            $title = $slug;
        }

        $description = $this->localizedString($manifest, 'description', $lang);

        $author = isset($manifest['author']) && is_string($manifest['author'])
            ? $manifest['author']
            : null;

        $language = isset($manifest['language']) && is_string($manifest['language'])
            ? $manifest['language']
            : null;

        $coverUrl = $this->extractCoverUrl($manifest);

        $updatedAt = isset($manifest['updated_at']) && is_string($manifest['updated_at'])
            ? $manifest['updated_at']
            : null;

        $formats = $this->buildFormats($manifest, $options);

        $featured = (bool) ($options['featured'] ?? false);
        $order = (int) ($options['order'] ?? 100);

        return new BookSummary(
            slug: $slug,
            title: $title,
            description: $description,
            author: $author,
            language: $language,
            coverUrl: $coverUrl,
            updatedAt: $updatedAt,
            formats: $formats,
            featured: $featured,
            order: $order,
        );
    }

    private function localizedString(array $manifest, string $key, string $lang): string
    {
        if (!isset($manifest[$key])) {
            return '';
        }

        $value = $manifest[$key];

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            if (isset($value[$lang]) && is_string($value[$lang])) {
                return $value[$lang];
            }

            foreach (['ru', 'en'] as $fallbackLang) {
                if (
                    $fallbackLang !== $lang
                    && isset($value[$fallbackLang])
                    && is_string($value[$fallbackLang])
                ) {
                    return $value[$fallbackLang];
                }
            }

            foreach ($value as $item) {
                if (is_string($item)) {
                    return $item;
                }
            }
        }

        return '';
    }

    private function extractCoverUrl(array $manifest): ?string
    {
        foreach (['cover_url', 'coverUrl', 'cover'] as $key) {
            if (isset($manifest[$key]) && is_string($manifest[$key])) {
                $url = $this->normalizePublicUrl($manifest[$key]);
                if ($url !== null) {
                    return $url;
                }
            }
        }

        return null;
    }

    /**
     * @return BookFormat[]
     */
    private function buildFormats(array $manifest, array $options): array
    {
        $formatsByType = [];

        $configDownloads = $options['downloads'] ?? [];
        if (is_array($configDownloads)) {
            foreach ($configDownloads as $item) {
                $format = $this->normalizeFormat($item);
                if ($format !== null) {
                    $formatsByType[$format->type] = $format;
                }
            }
        }

        $manifestFormats = $manifest['formats'] ?? [];
        if (is_array($manifestFormats)) {
            foreach ($manifestFormats as $item) {
                $format = $this->normalizeFormat($item);
                if ($format !== null) {
                    $formatsByType[$format->type] = $format;
                }
            }
        }

        return array_values($formatsByType);
    }

    private function normalizeFormat($item): ?BookFormat
    {
        if (is_string($item)) {
            $url = $this->normalizePublicUrl($item);
            if ($url === null) {
                return null;
            }

            $type = $this->inferFormatType($url);

            return new BookFormat(
                type: $type,
                label: strtoupper($type),
                url: $url,
            );
        }

        if (!is_array($item)) {
            return null;
        }

        $url = isset($item['url']) && is_string($item['url'])
            ? $this->normalizePublicUrl($item['url'])
            : null;

        if ($url === null) {
            return null;
        }

        $type = isset($item['type']) && is_string($item['type'])
            ? strtolower(trim($item['type']))
            : $this->inferFormatType($url);

        if ($type === '' || preg_match('/^[a-z0-9_-]{1,32}$/', $type) !== 1) {
            $type = $this->inferFormatType($url);
        }

        $label = isset($item['label']) && is_string($item['label']) && trim($item['label']) !== ''
            ? trim($item['label'])
            : strtoupper($type);

        return new BookFormat(
            type: $type,
            label: $label,
            url: $url,
        );
    }

    private function inferFormatType(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = $url;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (is_string($extension) && $extension !== '') {
            $extension = strtolower($extension);
            if (preg_match('/^[a-z0-9]{1,16}$/', $extension) === 1) {
                return $extension;
            }
        }

        $basename = basename($path);
        $normalized = strtolower((string) preg_replace('/[^a-z0-9_-]+/i', '-', $basename));
        $normalized = trim($normalized, '-');

        return $normalized !== '' ? substr($normalized, 0, 32) : 'file';
    }

    private function normalizePublicUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        return null;
    }

    /**
     * @param array<string, string> $chapterIdsByPath
     */
    private function buildTocHtml(string $bookDir, array $chapterIdsByPath): string
    {
        $tocPath = $bookDir . '/toc.md';

        if (!is_file($tocPath) || !is_readable($tocPath)) {
            return '';
        }

        $markdown = (string) file_get_contents($tocPath);

        $markdown = preg_replace_callback(
            '/\]\(([^)#]+\.md)(#[^)]*)?\)/i',
            function (array $match) use ($chapterIdsByPath): string {
                $path = $this->normalizeRelativePath(rawurldecode(trim($match[1])));

                if (isset($chapterIdsByPath[$path])) {
                    return '](#' . $chapterIdsByPath[$path] . ')';
                }

                return $match[0];
            },
            $markdown
        ) ?? $markdown;

        return $this->converter->convert($markdown)->getContent();
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        while (str_starts_with($path, './')) {
            $path = substr($path, 2);
        }

        return $path;
    }

    private function isSafeRelativePath(string $path): bool
    {
        if ($path === '' || str_starts_with($path, '/')) {
            return false;
        }

        if (preg_match('#(^|/)\.\.(/|$)#', $path) === 1) {
            return false;
        }

        return preg_match('#^[a-z0-9][a-z0-9._-]*(?:/[a-z0-9][a-z0-9._-]*)*$#i', $path) === 1;
    }

    private function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/i', $slug) === 1;
    }
}