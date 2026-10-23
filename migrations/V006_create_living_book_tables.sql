-- migrations/006_create_living_book_tables.sql

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unique_number VARCHAR(100) NOT NULL UNIQUE COMMENT 'Номер с заднего форзаца',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_unique_number (unique_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'ID пользователя из таблицы users',
    previous_owner_id INT NULL COMMENT 'ID предыдущего владельца (для построения цепочки)',
    text_response TEXT NOT NULL COMMENT 'Гнев, надежда, критика...',
    photo_paths JSON DEFAULT NULL COMMENT 'Массив относительных путей к файлам в storage/books/',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_book_id (book_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
