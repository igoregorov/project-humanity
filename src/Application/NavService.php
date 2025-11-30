<?php
declare(strict_types=1);
// src/Application/NavService.php

namespace App\Application;

use App\Domain\Auth\AuthServiceInterface;
use App\Infrastructure\ContainerInterface;

class NavService
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    public function isUserLoggedIn(): bool
    {
        $authService = $this->container->get('auth_service');
        return $authService->isLoggedIn();
    }

    public function getCurrentUser()
    {
        $authService = $this->container->get('auth_service');
        return $authService->getCurrentUser();
    }
}