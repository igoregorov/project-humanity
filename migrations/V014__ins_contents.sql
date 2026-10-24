SET NAMES utf8mb4;

START TRANSACTION;

-- =========================================================
-- widget_texts
-- Обычно уже заполняется миграцией V014, но дублируем безопасно.
-- =========================================================

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

-- =========================================================
-- content_values: main
-- =========================================================

INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('main', 'ru', 'site_title', JSON_QUOTE('Проект Человечество')),
('main', 'ru', 'page_title', JSON_QUOTE('Главная')),
('main', 'ru', 'description', JSON_QUOTE('Космическая экспансия без денег и законов. Новое государство разума.')),
('main', 'ru', 'version', JSON_QUOTE('v1.1-test')),

('main', 'ru', 'mission', JSON_QUOTE('Миссия')),
('main', 'ru', 'mission_text', JSON_ARRAY(
    'Мы создаём новую форму цивилизации — без денег, без иерархии, без законов.',
    'Только разум, доверие и стремление к звёздам.',
    'Это не утопия. Это инженерия будущего.'
)),

('main', 'ru', 'principles', JSON_QUOTE('Принципы')),
('main', 'ru', 'principles_list', JSON_ARRAY(
    'Отсутствие денег — ресурсы распределяются по необходимости и смыслу.',
    'Отсутствие законов — поведение регулируется разумом и доверием.',
    'Открытая архитектура — каждый может внести вклад.'
)),

('main', 'ru', 'join', JSON_QUOTE('Как присоединиться')),
('main', 'ru', 'join_text', JSON_ARRAY(
    'Мы ищем инженеров, философов, мечтателей.',
    'Присоединяйся к обсуждению, предлагай идеи, помогай строить.'
)),

('main', 'ru', 'contact', JSON_QUOTE('Написать нам')),
('main', 'ru', 'telegram', JSON_QUOTE('Присоединиться в Telegram')),
('main', 'ru', 'copyright', JSON_QUOTE('Все права защищены... или нет?')),
('main', 'ru', 'made_with', JSON_QUOTE('Сделано с верой в будущее.')),

('main', 'ru', 'nav_home', JSON_QUOTE('Главная')),
('main', 'ru', 'nav_about', JSON_QUOTE('О Проекте')),
('main', 'ru', 'nav_principles', JSON_QUOTE('Принципы')),
('main', 'ru', 'login', JSON_QUOTE('Вход')),
('main', 'ru', 'register', JSON_QUOTE('Регистрация')),
('main', 'ru', 'logout', JSON_QUOTE('Выход')),
('main', 'ru', 'profile', JSON_QUOTE('Профиль')),

('main', 'en', 'site_title', JSON_QUOTE('Project Humanity')),
('main', 'en', 'page_title', JSON_QUOTE('Home')),
('main', 'en', 'description', JSON_QUOTE('Space expansion without money or laws. A new state of reason.')),
('main', 'en', 'version', JSON_QUOTE('v1.1-test')),

('main', 'en', 'mission', JSON_QUOTE('Mission')),
('main', 'en', 'mission_text', JSON_ARRAY(
    'We are creating a new form of civilization — without money, hierarchy, or laws.',
    'Only reason, trust, and the drive toward the stars.',
    'This is not utopia. This is the engineering of the future.'
)),

('main', 'en', 'principles', JSON_QUOTE('Principles')),
('main', 'en', 'principles_list', JSON_ARRAY(
    'No money — resources are distributed by need and meaning.',
    'No laws — behavior is regulated by reason and trust.',
    'Open architecture — anyone can contribute.'
)),

('main', 'en', 'join', JSON_QUOTE('How to Join')),
('main', 'en', 'join_text', JSON_ARRAY(
    'We are looking for engineers, philosophers, dreamers.',
    'Join the discussion, propose ideas, help build.'
)),

('main', 'en', 'contact', JSON_QUOTE('Contact us')),
('main', 'en', 'telegram', JSON_QUOTE('Join on Telegram')),
('main', 'en', 'copyright', JSON_QUOTE('All rights reserved... or not?')),
('main', 'en', 'made_with', JSON_QUOTE('Made with faith in the future.')),

('main', 'en', 'nav_home', JSON_QUOTE('Home')),
('main', 'en', 'nav_about', JSON_QUOTE('About')),
('main', 'en', 'nav_principles', JSON_QUOTE('Principles')),
('main', 'en', 'login', JSON_QUOTE('Login')),
('main', 'en', 'register', JSON_QUOTE('Register')),
('main', 'en', 'logout', JSON_QUOTE('Logout')),
('main', 'en', 'profile', JSON_QUOTE('Profile'))

ON DUPLICATE KEY UPDATE
    `value_json` = VALUES(`value_json`);

-- =========================================================
-- content_values: about
-- =========================================================

INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('about', 'ru', 'title', JSON_QUOTE('О Проекте Человечество')),
('about', 'ru', 'content', JSON_ARRAY(
    '<p><strong>«Проект Человечество» — это социальный эксперимент.</strong><br>Мы не обещаем утопию. Мы строим новую систему с нуля.</p>',
    '<p>Мы принимаем несовершенство человека и проектируем систему, в которой оно не ведёт к разрушению.</p>',
    '<p><a href="?page=principles&amp;lang=ru">Перейти к принципам</a></p>'
)),

('about', 'en', 'title', JSON_QUOTE('About Project Humanity')),
('about', 'en', 'content', JSON_ARRAY(
    '<p><strong>Project Humanity is a social experiment.</strong><br>We do not promise utopia. We are building a new system from scratch.</p>',
    '<p>We accept human imperfection and design a system where it does not lead to collapse.</p>',
    '<p><a href="?page=principles&amp;lang=en">Go to principles</a></p>'
))

ON DUPLICATE KEY UPDATE
    `value_json` = VALUES(`value_json`);

-- =========================================================
-- content_values: principles
-- =========================================================

INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('principles', 'ru', 'title', JSON_QUOTE('Этические принципы участника Проекта Человечество')),
('principles', 'ru', 'principles_list', JSON_ARRAY(
    '<strong>1. Солидарная ответственность за общий ресурс</strong><br>Участник признаёт, что общие ресурсы требуют коллективной заботы.',
    '<strong>2. Действенная эмпатия</strong><br>Этическая значимость действия определяется его способностью снижать страдание или напряжение.',
    '<strong>3. Открытое будущее</strong><br>Ни один путь развития не считается окончательным.'
)),

('principles', 'en', 'title', JSON_QUOTE('Ethical Principles of a Participant in Project Humanity')),
('principles', 'en', 'principles_list', JSON_ARRAY(
    '<strong>1. Solidary Responsibility for Shared Resources</strong><br>The participant acknowledges that shared resources require collective care.',
    '<strong>2. Actionable Empathy</strong><br>The ethical significance of an action is determined by its ability to reduce suffering or tension.',
    '<strong>3. Open Future</strong><br>No path of development is considered final.'
))

ON DUPLICATE KEY UPDATE
    `value_json` = VALUES(`value_json`);

-- =========================================================
-- events
-- id фиксированы, чтобы скрипт можно было запускать повторно.
-- =========================================================

INSERT INTO `events`
    (`id`, `lang`, `date`, `title`, `description`, `type`, `is_active`, `sort_order`)
VALUES
    (1, 'ru', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Прошедшее событие', 'Должно попасть в архив', 'meeting', 1, 0),
    (2, 'ru', CURDATE(), 'Сегодняшнее событие', 'Должно считаться активным', 'meeting', 1, 1),
    (3, 'ru', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Будущее событие', 'Должно отображаться в сайдбаре', 'workshop', 1, 2),
    (4, 'en', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Future event', 'Should appear in sidebar', 'meeting', 1, 0)
ON DUPLICATE KEY UPDATE
    `lang` = VALUES(`lang`),
    `date` = VALUES(`date`),
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `type` = VALUES(`type`),
    `is_active` = VALUES(`is_active`),
    `sort_order` = VALUES(`sort_order`);

-- =========================================================
-- news
-- =========================================================

INSERT INTO `news`
    (`id`, `lang`, `date`, `title`, `description`, `type`, `is_active`, `sort_order`)
VALUES
    (1, 'ru', CURDATE(), 'Создан сайт', 'Новая версия сайта на PHP и MySQL', 'announcement', 1, 0),
    (2, 'ru', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Старая новость', 'Должна быть ниже свежих новостей', 'news', 1, 1),
    (3, 'en', CURDATE(), 'Site launched', 'New PHP and MySQL version', 'announcement', 1, 0)
ON DUPLICATE KEY UPDATE
    `lang` = VALUES(`lang`),
    `date` = VALUES(`date`),
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `type` = VALUES(`type`),
    `is_active` = VALUES(`is_active`),
    `sort_order` = VALUES(`sort_order`);

-- =========================================================
-- timeline_events
-- =========================================================

INSERT INTO `timeline_events`
    (`id`, `lang`, `year`, `title`, `description`, `type`, `is_active`, `sort_order`)
VALUES
    (1, 'ru', 2024, 'Начало проекта', 'Первые идеи и наработки', 'milestone', 1, 0),
    (2, 'ru', 2026, 'Запуск сайта', 'Переход на базу данных', 'release', 1, 1),
    (3, 'en', 2026, 'Site launch', 'Database migration', 'release', 1, 0)
ON DUPLICATE KEY UPDATE
    `lang` = VALUES(`lang`),
    `year` = VALUES(`year`),
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `type` = VALUES(`type`),
    `is_active` = VALUES(`is_active`),
    `sort_order` = VALUES(`sort_order`);

-- =========================================================
-- telegram_posts
-- =========================================================

INSERT INTO `telegram_posts`
    (`id`, `lang`, `text`, `posted_at`, `url`, `is_active`, `sort_order`)
VALUES
    (1, 'ru', 'Первый тестовый пост в Telegram', NOW(), 'https://t.me/projecthumanity_space', 1, 0),
    (2, 'ru', 'Второй тестовый пост, должен быть ниже свежего', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 1, 1),
    (3, 'en', 'First Telegram test post', NOW(), 'https://t.me/projecthumanity_space', 1, 0)
ON DUPLICATE KEY UPDATE
    `lang` = VALUES(`lang`),
    `text` = VALUES(`text`),
    `posted_at` = VALUES(`posted_at`),
    `url` = VALUES(`url`),
    `is_active` = VALUES(`is_active`),
    `sort_order` = VALUES(`sort_order`);

-- =========================================================
-- social_links
-- Уникальный ключ: lang + code
-- =========================================================

INSERT INTO `social_links`
    (`lang`, `code`, `name`, `url`, `is_active`, `sort_order`)
VALUES
    ('ru', 'telegram', 'Telegram', 'https://t.me/projecthumanity_space', 1, 0),
    ('ru', 'vk', 'VK', 'https://vk.com/', 1, 1),
    ('ru', 'github', 'GitHub', 'https://github.com/', 1, 2),
    ('en', 'telegram', 'Telegram', 'https://t.me/projecthumanity_space', 1, 0),
    ('en', 'github', 'GitHub', 'https://github.com/', 1, 1)
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `url` = VALUES(`url`),
    `is_active` = VALUES(`is_active`),
    `sort_order` = VALUES(`sort_order`);

COMMIT;
