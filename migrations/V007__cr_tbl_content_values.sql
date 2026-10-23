CREATE TABLE IF NOT EXISTS `content_values` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section` VARCHAR(64) NOT NULL,
  `lang` CHAR(2) NOT NULL,
  `item_key` VARCHAR(191) NOT NULL,
  `value_json` LONGTEXT NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_content_section_lang_key` (`section`, `lang`, `item_key`),
  KEY `idx_content_lang` (`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
