<?php
declare(strict_types=1);

// src/Infrastructure/ContainerInterface.php

namespace App\Infrastructure;

interface ContainerInterface
{
    public function get(string $id): mixed;
    public function set(string $id, mixed $value): ContainerInterface;
    public function has(string $id): bool;
    public function factory(string $id, callable $callable): ContainerInterface;
    public function singleton(string $id, callable $callable): ContainerInterface;
}