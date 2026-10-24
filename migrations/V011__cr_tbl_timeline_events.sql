CREATE TABLE IF NOT EXISTS `timeline_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lang` CHAR(2) NOT NULL,
  `year` SMALLINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NULL,
  `type` VARCHAR(64) NOT NULL DEFAULT 'milestone',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_timeline_lang_active_year` (`lang`, `is_active`, `year`),
  KEY `idx_timeline_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
