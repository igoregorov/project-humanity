SET NAMES utf8mb4;

DROP TABLE IF EXISTS book_entries;
DROP TABLE IF EXISTS books;

CREATE TABLE books (
                       id INT NOT NULL AUTO_INCREMENT,
                       unique_number VARCHAR(100) NOT NULL,
                       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                       PRIMARY KEY (id),
                       UNIQUE KEY uq_books_unique_number (unique_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE book_entries (
                              id INT NOT NULL AUTO_INCREMENT,
                              book_id INT NOT NULL,
                              user_id INT NOT NULL,
                              previous_entry_id INT NULL,
                              text_response TEXT NOT NULL,
                              photo_path VARCHAR(255) NULL,
                              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                              PRIMARY KEY (id),
                              UNIQUE KEY uq_book_entries_user_id (user_id),
                              INDEX idx_book_entries_book_id (book_id),
                              INDEX idx_book_entries_previous_entry_id (previous_entry_id),
                              INDEX idx_book_entries_created_at (created_at),
                              INDEX idx_book_entries_book_created (book_id, created_at, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;