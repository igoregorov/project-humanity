<?php
declare(strict_types=1);

// src/Domain/Sidebar/TimelineSidebarWidget.php

namespace App\Domain\Sidebar;

class TimelineSidebarWidget extends AbstractSidebarWidget
{
    public function getContent(string $lang, array $context = []): array
    {
        return [
            'type' => 'timeline',
            'title' => 'История развития',
            'events' => [],
            'display_mode' => $this->config['display_mode'] ?? 'compact'
        ];
    }
}