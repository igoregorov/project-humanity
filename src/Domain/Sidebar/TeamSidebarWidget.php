<?php
declare(strict_types=1);

// src/Domain/Sidebar/TeamSidebarWidget.php

namespace App\Domain\Sidebar;

use App\Application\TeamService;

class TeamSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly TeamService $teamService,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        return [
            'type' => 'team',
            'title' => 'Наша команда',
            'members' => $this->teamService->getTeamMembers($lang),
            'max_members' => $this->config['max_members'] ?? 5
        ];
    }
}