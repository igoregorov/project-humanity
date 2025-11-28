<?php
declare(strict_types=1);
/** @var \App\View\NavData $data */
use App\View\NavData;
?>
<nav class="main-nav">
    <a href="?lang=<?= htmlspecialchars($data->current_lang) ?>"<?= $data->current_page === 'home' ? ' class="active"' : '' ?>>Главная</a>
    <a href="?page=about&lang=<?= htmlspecialchars($data->current_lang) ?>"<?= $data->current_page === 'about' ? ' class="active"' : '' ?>>О Проекте</a>
</nav>