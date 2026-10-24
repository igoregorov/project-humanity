INSERT INTO `widget_texts` (`widget`, `lang`, `title`, `no_items`) VALUES
('events', 'ru', 'Предстоящие события', 'Пока нет предстоящих событий'),
('events', 'en', 'Upcoming events', 'No upcoming events yet'),
('news', 'ru', 'Последние новости', 'Пока нет новостей'),
('news', 'en', 'Latest news', 'No news yet'),
('timeline', 'ru', 'История развития', 'Нет данных для отображения'),
('timeline', 'en', 'History', 'No data available'),
('social', 'ru', 'Мы в соцсетях', 'Нет доступных соцсетей'),
('social', 'en', 'Social media', 'No social networks available'),
('telegram', 'ru', 'Telegram канал', 'Нет постов для отображения'),
('telegram', 'en', 'Telegram channel', 'No posts available')
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `no_items` = VALUES(`no_items`);
