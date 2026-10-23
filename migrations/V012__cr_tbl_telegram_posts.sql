CREATE TABLE IF NOT EXISTS `telegram_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lang` CHAR(2) NOT NULL,
  `text` TEXT NOT NULL,
  `posted_at` DATETIME NULL,
  `url` VARCHAR(512) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_telegram_lang_active_posted` (`lang`, `is_active`, `posted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
