<?php
declare(strict_types=1);

// src/Domain/Sidebar/SocialSidebarWidget.php

namespace App\Domain\Sidebar;

class SocialSidebarWidget extends AbstractSidebarWidget
{
    public function getContent(string $lang, array $context = []): array
    {
        return [
            'type' => 'social',
            'title' => 'Мы в соцсетях',
            'networks' => $this->config['networks'] ?? []
        ];
    }
}