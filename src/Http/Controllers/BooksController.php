<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Books\BookRepositoryInterface;
use App\View\BooksData;
use RuntimeException;

final class BooksController extends AbstractController
{
    public function handle(string $page, string $lang): string
    {
        $action = $_GET['action'] ?? 'index';
        if (!is_string($action)) {
            $action = 'index';
        }

        $repository = $this->container->get('catalog_book_repository');
        if (!$repository instanceof BookRepositoryInterface) {
            throw new RuntimeException('catalog_book_repository is not configured.');
        }

        if ($action === 'read') {
            $slug = $_GET['book'] ?? '';

            if (!is_string($slug) || $slug === '') {
                return $this->renderNotFound($page, $lang);
            }

            $reader = $repository->findReader($slug, $lang);
            if ($reader === null) {
                return $this->renderNotFound($page, $lang);
            }

            $data = new BooksData(
                lang_code: $lang,
                reader: true,
                title: $reader->title,
                toc: $reader->tocHtml,
                chapters: $reader->chapters,
                author: $reader->author,
                description: $reader->description,
                coverUrl: $reader->coverUrl,
                updatedAt: $reader->updatedAt,
                formats: $reader->formats,
            );

            return $this->renderPage('page_books.php', $data, $page, $lang);
        }

        $books = $repository->findAll($lang);

        $data = new BooksData(
            lang_code: $lang,
            books: $books,
        );

        return $this->renderPage('page_books.php', $data, $page, $lang);
    }

    private function renderNotFound(string $page, string $lang): string
    {
        http_response_code(404);

        $data = new BooksData(
            lang_code: $lang,
            message: 'Книга не найдена.',
        );

        return $this->renderPage('page_books.php', $data, $page, $lang);
    }
}