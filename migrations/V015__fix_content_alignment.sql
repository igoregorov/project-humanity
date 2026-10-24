-- migrations/V015__fix_content_alignment.sql
-- Корректировка контента: приведение БД в соответствие с JSON-файлами
-- Исправлены: about (полный контент), principles (все 8 принципов),
-- main (добавлены ключи auth, исправлена версия и mission_text)

SET NAMES utf8mb4;
START TRANSACTION;

-- =========================================================
-- content_values: main (ru) — полный набор ключей
-- =========================================================
INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('main', 'ru', 'site_title', JSON_QUOTE('Проект Человечество')),
('main', 'ru', 'page_title', JSON_QUOTE('Главная')),
('main', 'ru', 'description', JSON_QUOTE('Космическая экспансия без денег и законов. Новое государство разума.')),
('main', 'ru', 'version', JSON_QUOTE('v1.1')),
('main', 'ru', 'mission', JSON_QUOTE('Миссия')),
('main', 'ru', 'mission_text', JSON_ARRAY(
    'Мы создаём новую форму цивилизации — без денег, без иерархии, без законов.',
    'Только разум, доверие и стремление к звёздам.',
    'Это не утопия. Это инженерия будущего. <a href="?page=about&amp;lang=ru">Подробнее о Проекте</a>.'
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
('main', 'ru', 'news', JSON_QUOTE('Новости')),
('main', 'ru', 'no_news', JSON_QUOTE('Пока нет новостей.')),
('main', 'ru', 'upcoming_events', JSON_QUOTE('Предстоящие события')),
('main', 'ru', 'no_upcoming_events', JSON_QUOTE('Пока нет предстоящих событий.')),
('main', 'ru', 'contact', JSON_QUOTE('Написать нам')),
('main', 'ru', 'copyright', JSON_QUOTE('Все права защищены... или нет?')),
('main', 'ru', 'telegram', JSON_QUOTE('Присоединиться в Telegram')),
('main', 'ru', 'made_with', JSON_QUOTE('Сделано с верой в будущее.')),
('main', 'ru', 'nav_home', JSON_QUOTE('Главная')),
('main', 'ru', 'nav_about', JSON_QUOTE('О Проекте')),
('main', 'ru', 'nav_principles', JSON_QUOTE('Принципы')),
('main', 'ru', 'nav_auth', JSON_QUOTE('Аккаунт')),
('main', 'ru', 'login', JSON_QUOTE('Вход')),
('main', 'ru', 'register', JSON_QUOTE('Регистрация')),
('main', 'ru', 'logout', JSON_QUOTE('Выход')),
('main', 'ru', 'profile', JSON_QUOTE('Профиль')),
('main', 'ru', 'username', JSON_QUOTE('Имя пользователя')),
('main', 'ru', 'email', JSON_QUOTE('Email')),
('main', 'ru', 'password', JSON_QUOTE('Пароль')),
('main', 'ru', 'password_confirm', JSON_QUOTE('Подтверждение пароля')),
('main', 'ru', 'captcha', JSON_QUOTE('Код проверки')),
('main', 'ru', 'welcome_user', JSON_QUOTE('Добро пожаловать, %s!'))
ON DUPLICATE KEY UPDATE `value_json` = VALUES(`value_json`);

-- =========================================================
-- content_values: main (en) — полный набор ключей
-- =========================================================
INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('main', 'en', 'site_title', JSON_QUOTE('Project Humanity')),
('main', 'en', 'page_title', JSON_QUOTE('Home')),
('main', 'en', 'description', JSON_QUOTE('Space expansion without money or laws. A new state of reason.')),
('main', 'en', 'version', JSON_QUOTE('v1.1')),
('main', 'en', 'mission', JSON_QUOTE('Mission')),
('main', 'en', 'mission_text', JSON_ARRAY(
    'We are creating a new form of civilization — without money, hierarchy, or laws.',
    'Only reason, trust, and the drive toward the stars.',
    'This is not utopia. This is the engineering of the future. <a href="?page=about&amp;lang=en">Learn more about the Project</a>.'
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
('main', 'en', 'news', JSON_QUOTE('News')),
('main', 'en', 'no_news', JSON_QUOTE('No news yet.')),
('main', 'en', 'upcoming_events', JSON_QUOTE('Upcoming Events')),
('main', 'en', 'no_upcoming_events', JSON_QUOTE('No upcoming events yet.')),
('main', 'en', 'contact', JSON_QUOTE('Contact us')),
('main', 'en', 'copyright', JSON_QUOTE('All rights reserved... or not?')),
('main', 'en', 'telegram', JSON_QUOTE('Join on Telegram')),
('main', 'en', 'made_with', JSON_QUOTE('Made with faith in the future.')),
('main', 'en', 'nav_home', JSON_QUOTE('Home')),
('main', 'en', 'nav_about', JSON_QUOTE('About')),
('main', 'en', 'nav_principles', JSON_QUOTE('Principles')),
('main', 'en', 'nav_auth', JSON_QUOTE('Account')),
('main', 'en', 'login', JSON_QUOTE('Login')),
('main', 'en', 'register', JSON_QUOTE('Register')),
('main', 'en', 'logout', JSON_QUOTE('Logout')),
('main', 'en', 'profile', JSON_QUOTE('Profile')),
('main', 'en', 'username', JSON_QUOTE('Username')),
('main', 'en', 'email', JSON_QUOTE('Email')),
('main', 'en', 'password', JSON_QUOTE('Password')),
('main', 'en', 'password_confirm', JSON_QUOTE('Confirm Password')),
('main', 'en', 'captcha', JSON_QUOTE('Verification Code')),
('main', 'en', 'welcome_user', JSON_QUOTE('Welcome, %s!'))
ON DUPLICATE KEY UPDATE `value_json` = VALUES(`value_json`);

-- =========================================================
-- content_values: about (ru) — полный контент из JSON
-- =========================================================
INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('about', 'ru', 'title', JSON_QUOTE('О Проекте Человечество')),
('about', 'ru', 'content', JSON_ARRAY(
    '<p><strong>«Проект Человечество» — это социальный эксперимент с вероятностью успеха менее 10 %.</strong><br>Мы сознательно начинаем с этого признания. Не из пессимизма, а из честности. Мы не обещаем утопии. Мы не верим в идеального человека. Мы не пытаемся реформировать старый мир.</p>',
    '<p>Мы строим <strong>новый — с нуля</strong>, из тех, кто разделяет его <a href="?page=principles&amp;lang=ru">принципы</a>.</p>',
    '<h3>Что мы предлагаем?</h3><p>Освобождение от многовекового подсознательного стресса — страха перед нехваткой ресурсов, борьбы за выживание, зависимости от денег и иерархии.<br>Взамен — <strong>высокий уровень жизни, защиту здоровья, качественное образование и свободу самореализации</strong>, основанную не на конкуренции, а на доверии и разуме.</p>',
    '<h3>Как это возможно?</h3><p>Проект существует в рамках <strong>краевых условий</strong>:</p><ul><li>отсутствие денег — ресурсы распределяются по необходимости и смыслу,</li><li>отсутствие формальных законов — поведение регулируется этикой и доверием,</li><li>отсутствие иерархии — решения рождаются в горизонтальных сетях.</li></ul><p>Мы не идеализируем человеческую природу. Мы принимаем её несовершенство — и проектируем систему, в которой оно не ведёт к разрушению, а становится основой для устойчивости.</p>',
    '<h3>Как это будет развиваться?</h3><ol><li><strong>Нулевая фаза</strong> — информирование и сбор единомышленников.</li><li><strong>Первая фаза</strong> — становление как политической силы при поддержке заинтересованных государств (в обмен на научный, этический и политический капитал).</li><li><strong>Вторая фаза</strong> — практическая реализация первого автономного анклава.</li><li><strong>Третья фаза</strong> — создание сети анклавов на Земле.</li><li><strong>Четвёртая фаза</strong> — выход за пределы планеты: замкнутые космические сообщества как финальная проверка жизнеспособности модели.</li></ol><p><strong>Уже выход на вторую фазу — это успех.</strong></p>',
    '<h3>Почему это важно — даже если не сработает?</h3><p>Потому что сама попытка заставляет задать главный вопрос:<br><em>«Можем ли мы выжить как вид — без денег, без законов, без страха?»</em></p><p>Если ответ «да» — мы получим цивилизацию, готовую к звёздам.<br>Если «нет» — мы хотя бы узнаем, где лежит предел человеческого.</p>',
    '<blockquote><p><strong>Это не утопия. Это инженерия будущего.</strong><br>Присоединяйся — если готов строить, а не мечтать.</p></blockquote>'
))
ON DUPLICATE KEY UPDATE `value_json` = VALUES(`value_json`);

-- =========================================================
-- content_values: about (en) — полный контент из JSON
-- =========================================================
INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('about', 'en', 'title', JSON_QUOTE('About Project Humanity')),
('about', 'en', 'content', JSON_ARRAY(
    '<p><strong>"Project Humanity" is a social experiment with a success probability of less than 10%.</strong><br>We consciously begin with this acknowledgment—not out of pessimism, but honesty. We do not promise utopia. We do not believe in the perfect human. We are not trying to reform the old world.</p>',
    '<p>We are building a <strong>new one—from scratch</strong>, with those who share its <a href="?page=principles&amp;lang=en">principles</a>.</p>',
    '<h3>What do we offer?</h3><p>Liberation from millennia of subconscious stress—the fear of resource scarcity, the struggle for survival, and dependence on money and hierarchy.<br>In return: <strong>a high standard of living, health protection, quality education, and freedom of self-realization</strong>, grounded not in competition, but in trust and reason.</p>',
    '<h3>How is this possible?</h3><p>The Project operates under <strong>boundary conditions</strong>:</p><ul><li>no money—resources are distributed according to need and meaning,</li><li>no formal laws—behavior is regulated by ethics and trust,</li><li>no hierarchy—decisions emerge from horizontal networks.</li></ul><p>We do not idealize human nature. We accept its imperfection—and design a system where this imperfection does not lead to collapse, but becomes the foundation for resilience.</p>',
    '<h3>How will it evolve?</h3><ol><li><strong>Phase Zero</strong> — outreach and gathering like-minded individuals.</li><li><strong>Phase One</strong> — emergence as a political force, supported by interested states (in exchange for scientific, ethical, and political capital).</li><li><strong>Phase Two</strong> — practical implementation of the first autonomous enclave.</li><li><strong>Phase Three</strong> — creation of a network of enclaves on Earth.</li><li><strong>Phase Four</strong> — expansion beyond the planet: closed-loop space communities as the ultimate test of the model''s viability.</li></ol><p><strong>Simply reaching Phase Two would already constitute success.</strong></p>',
    '<h3>Why does this matter—even if it fails?</h3><p>Because the very attempt forces us to ask the central question:<br><em>"Can we survive as a species—without money, without laws, without fear?"</em></p><p>If the answer is "yes," we will gain a civilization ready for the stars.<br>If "no," we will at least learn where the limits of humanity lie.</p>',
    '<blockquote><p><strong>This is not utopia. This is the engineering of the future.</strong><br>Join us—if you''re ready to build, not just dream.</p></blockquote>'
))
ON DUPLICATE KEY UPDATE `value_json` = VALUES(`value_json`);

-- =========================================================
-- content_values: principles (ru) — все 8 принципов
-- =========================================================
INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('principles', 'ru', 'title', JSON_QUOTE('Этические принципы участника «Проекта Человечество»')),
('principles', 'ru', 'principles_list', JSON_ARRAY(
    '<strong>1. Солидарная ответственность за общий ресурс</strong><br>Участник признаёт, что ресурсы, обладающие свойством исключительности, в том числе время, энергия, физическая и цифровая среда, требуют коллективной заботы. Их использование без учёта последствий для других нарушает этическую солидарность.',
    '<strong>2. Действенная эмпатия</strong><br>Этическая значимость действия определяется его способностью снижать страдание или напряжение в системе «Другой», а не благостью намерения в системе «Я».',
    '<strong>3. Функциональная пластичность</strong><br>Роли участника не фиксируются и не становятся частью идентичности. Этически предпочтительна готовность к переходу между функциями в ответ на потребности сообщества.',
    '<strong>4. Когнитивная открытость</strong><br>Этическая зрелость измеряется готовностью пересматривать собственные ментальные модели под воздействием нового опыта и чужой перспективы.',
    '<strong>5. Циркуляция знания</strong><br>Знание, изъятое из обмена, утрачивает этическую ценность и рискует превратиться в инструмент власти. Этически значимым становится его участие в непрерывной циркуляции между теми, кто способен его использовать и развивать.',
    '<strong>6. Ответственное присутствие</strong><br>Участие в сообществе предполагает минимальную доступность: оставлять актуальные способы связи, не исчезать без необходимости, быть «найденным», когда это важно для других. Присутствие — не наблюдение, а возможность взаимодействия.',
    '<strong>7. Открытое будущее</strong><br>Ни один путь развития — личный, технический или социальный — не считается окончательным. Проект отвергает закрытые системы, догмы и необратимые иерархии. Будущее остаётся открытым для пересборки.',
    '<strong>8. Временность этики</strong><br>Все вышеперечисленные принципы носят переходный характер. Они возникают не как вечные нормы, а как ответ на незавершённость Проекта — на временное отсутствие безусловного изобилия, полной автоматизации рутинных функций и устойчивого избытка ресурсов.<br>Пока сообщество зависит от ручного труда, ограниченных пространств и неавтоматизированной логистики, эти принципы служат <em>минималистичной этической инфраструктурой</em>, предотвращающей возрождение иерархий и эксплуатации.<br>Однако по мере достижения рабочего режима — когда поддержание среды, распределение ресурсов и забота о другом больше не требуют сознательного усилия — этические обязательства растворяются в самой ткани повседневности. Ответственность перестаёт быть выбором и становится естественным состоянием, подобным дыханию.<br>Проект не стремится к миру, где люди «хороши». Он строит мир, в котором <strong>быть «плохим» станет бессмысленно</strong>, потому что условия жизни больше не порождают страха, дефицита и недоверия.'
))
ON DUPLICATE KEY UPDATE `value_json` = VALUES(`value_json`);

-- =========================================================
-- content_values: principles (en) — все 8 принципов
-- =========================================================
INSERT INTO `content_values` (`section`, `lang`, `item_key`, `value_json`) VALUES
('principles', 'en', 'title', JSON_QUOTE('Ethical Principles of a Participant in Project Humanity')),
('principles', 'en', 'principles_list', JSON_ARRAY(
    '<strong>1. Solidary Responsibility for Shared Resources</strong><br>The participant acknowledges that resources possessing the quality of exclusivity — including time, energy, and physical or digital environments — require collective care. Using them without regard for the consequences to others violates ethical solidarity.',
    '<strong>2. Actionable Empathy</strong><br>The ethical significance of an action is determined by its ability to reduce suffering or tension within the ''Other'' system, not by the goodness of intention within the ''Self'' system.',
    '<strong>3. Functional Flexibility</strong><br>A participant''s roles are not fixed and do not become part of their identity. Ethically preferable is the readiness to shift between functions in response to the community''s needs.',
    '<strong>4. Cognitive Openness</strong><br>Ethical maturity is measured by one''s willingness to revise their own mental models under the influence of new experience and others'' perspectives.',
    '<strong>5. Circulation of Knowledge</strong><br>Knowledge withdrawn from exchange loses its ethical value and risks becoming an instrument of power. Its ethical significance emerges only through continuous circulation among those capable of using and developing it.',
    '<strong>6. Responsible Presence</strong><br>Participation in the community implies minimal availability: maintaining up-to-date contact methods, not disappearing without necessity, and remaining ''findable'' when it matters to others. Presence is not observation—it is the possibility of interaction.',
    '<strong>7. Open Future</strong><br>No path of development—personal, technical, or social—is considered final. The Project rejects closed systems, dogmas, and irreversible hierarchies. The future remains open to reassembly.',
    '<strong>8. Temporality of Ethics</strong><br>All the above principles are transitional. They arise not as eternal norms but as a response to the Project''s incompleteness—specifically, the temporary absence of unconditional abundance, full automation of routine functions, and sustainable resource surplus.<br>As long as the community depends on manual labor, limited spaces, and non-automated logistics, these principles serve as a <em>minimal ethical infrastructure</em> preventing the resurgence of hierarchies and exploitation.<br>However, as the Project approaches operational maturity—when maintaining the environment, distributing resources, and caring for others no longer require conscious effort—<strong>ethical obligations dissolve into the very fabric of everyday life</strong>. Responsibility ceases to be a choice and becomes a natural state, akin to breathing.<br>The Project does not seek a world where people are ''good.'' It builds a world in which <strong>being ''bad'' becomes meaningless</strong>, because the conditions of life no longer generate fear, scarcity, or distrust.'
))
ON DUPLICATE KEY UPDATE `value_json` = VALUES(`value_json`);

COMMIT;
