<?php
declare(strict_types=1);

/** @var \App\View\BooksData $data */

use App\Domain\Books\BookChapter;
use App\Domain\Books\BookFormat;
use App\Domain\Books\BookSummary;

$escape = static fn (string $value): string =>
htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$lang = $escape($data->lang_code);
?>
<section class="books-page">
    <?php if ($data->message !== ''): ?>
        <h1><?= $escape($data->message) ?></h1>
        <p>
            <a class="btn-secondary" href="?page=books&amp;lang=<?= $lang ?>">
                К списку книг
            </a>
        </p>

    <?php elseif (!$data->reader): ?>
        <h1>Книги</h1>

        <?php if ($data->books === []): ?>
            <p>Книги пока недоступны.</p>
        <?php else: ?>
            <?php /** @var BookSummary $book */ ?>
            <?php foreach ($data->books as $book): ?>
                <article class="book-card">
                    <?php if ($book->coverUrl !== null && $book->coverUrl !== ''): ?>
                        <img
                                class="book-cover"
                                src="<?= $escape($book->coverUrl) ?>"
                                alt="Обложка книги: <?= $escape($book->title) ?>"
                        >
                    <?php endif; ?>

                    <h2><?= $escape($book->title) ?></h2>

                    <?php if ($book->author !== null && $book->author !== ''): ?>
                        <p class="book-author">
                            <?= $escape($book->author) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($book->description !== ''): ?>
                        <p class="book-description">
                            <?= $escape($book->description) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($book->updatedAt !== null && $book->updatedAt !== ''): ?>
                        <p class="book-updated">
                            <small>
                                Обновлено: <?= $escape($book->updatedAt) ?>
                            </small>
                        </p>
                    <?php endif; ?>

                    <div class="book-actions">
                        <a
                                class="btn-primary"
                                href="?page=books&amp;book=<?= $escape($book->slug) ?>&amp;action=read&amp;lang=<?= $lang ?>"
                        >
                            Читать онлайн
                        </a>

                        <?php /** @var BookFormat $format */ ?>
                        <?php foreach ($book->formats as $format): ?>
                            <a
                                    class="btn-secondary"
                                    href="<?= $escape($format->url) ?>"
                                    download
                            >
                                Скачать <?= $escape($format->label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php else: ?>
        <article class="book-reader">
            <?php if ($data->coverUrl !== null && $data->coverUrl !== ''): ?>
                <img
                        class="book-cover"
                        src="<?= $escape($data->coverUrl) ?>"
                        alt="Обложка книги: <?= $escape($data->title) ?>"
                >
            <?php endif; ?>

            <h1><?= $escape($data->title) ?></h1>

            <?php if ($data->author !== ''): ?>
                <p class="book-author">
                    <?= $escape($data->author) ?>
                </p>
            <?php endif; ?>

            <?php if ($data->description !== ''): ?>
                <p class="book-description">
                    <?= $escape($data->description) ?>
                </p>
            <?php endif; ?>

            <?php if ($data->updatedAt !== null && $data->updatedAt !== ''): ?>
                <p class="book-updated">
                    <small>
                        Обновлено: <?= $escape($data->updatedAt) ?>
                    </small>
                </p>
            <?php endif; ?>

            <?php if ($data->formats !== []): ?>
                <div class="book-actions">
                    <?php /** @var BookFormat $format */ ?>
                    <?php foreach ($data->formats as $format): ?>
                        <a
                                class="btn-secondary"
                                href="<?= $escape($format->url) ?>"
                                download
                        >
                            Скачать <?= $escape($format->label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <p>
                <a class="btn-secondary" href="?page=books&amp;lang=<?= $lang ?>">
                    Назад к списку книг
                </a>
            </p>

            <?php if ($data->toc !== ''): ?>
                <section class="book-toc">
                    <h2>Оглавление</h2>
                    <?= $data->toc ?>
                </section>
            <?php endif; ?>

            <?php /** @var BookChapter $chapter */ ?>
            <?php foreach ($data->chapters as $chapter): ?>
                <section
                        class="book-chapter"
                        id="<?= $escape($chapter->id) ?>"
                >
                    <h2><?= $escape($chapter->title) ?></h2>
                    <?= $chapter->contentHtml ?>
                </section>
            <?php endforeach; ?>

            <?php if ($data->chapters === []): ?>
                <p>Главы пока недоступны.</p>
            <?php endif; ?>
        </article>
    <?php endif; ?>
</section>