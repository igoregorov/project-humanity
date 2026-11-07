<?php

declare(strict_types=1);

/** @var \App\View\BooksData $data */

$escape = static fn (string $value): string =>
htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$lang = $escape($data->lang_code);
?>

<section class="books-page">
    <?php if (!$data->reader): ?>
        <h1>Книги</h1>

        <article class="book-card">
            <h2><?= $escape($data->title) ?></h2>

            <div class="book-actions">
                <a class="btn-primary"
                   href="?page=books&amp;action=read&amp;lang=<?= $lang ?>">
                    Читать онлайн
                </a>

                <a class="btn-secondary"
                   href="/books/project-humanity.fb2"
                   download>
                    Скачать FB2
                </a>

                <a class="btn-secondary"
                   href="/books/project-humanity.pdf"
                   download>
                    Скачать PDF
                </a>
            </div>
        </article>
    <?php else: ?>
        <article class="book-reader">
            <h1><?= $escape($data->title) ?></h1>

            <p>
                <a class="btn-secondary" href="?page=books&amp;lang=<?= $lang ?>">
                    Назад к книге
                </a>
            </p>

            <?php if ($data->toc !== ''): ?>
                <section class="book-toc">
                    <h2>Оглавление</h2>
                    <?= $data->toc ?>
                </section>
            <?php endif; ?>

            <?php foreach ($data->chapters as $chapter): ?>
                <section
                        class="book-chapter"
                        id="<?= $escape($chapter['id']) ?>"
                >
                    <h2><?= $escape($chapter['title']) ?></h2>
                    <?= $chapter['content'] ?>
                </section>
            <?php endforeach; ?>

            <?php if ($data->chapters === []): ?>
                <p>Главы пока недоступны.</p>
            <?php endif; ?>
        </article>
    <?php endif; ?>
</section>