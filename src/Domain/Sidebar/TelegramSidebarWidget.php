<?php
declare(strict_types=1);

// src/Domain/Sidebar/TelegramSidebarWidget.php

namespace App\Domain\Sidebar;

class TelegramSidebarWidget extends AbstractSidebarWidget
{
    public function getContent(string $lang, array $context = []): array
    {
        return [
            'type' => 'telegram',
            'title' => 'Telegram канал',
            'posts' => [],
            'channel_url' => $this->config['channel_url'] ?? '#'
        ];
    }
}