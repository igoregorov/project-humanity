<?php
declare(strict_types=1);

// src/Infrastructure/SimpleContainer.php

namespace App\Infrastructure;

class SimpleContainer implements ContainerInterface
{
    private array $values = [];
    private array $factories = [];

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->values)) {
            return $this->values[$id];
        }

        if (isset($this->factories[$id])) {
            $factory = $this->factories[$id];
            $value = $factory($this);
            return $value;
        }

        throw new \InvalidArgumentException("Identifier '$id' is not defined.");
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->values) || isset($this->factories[$id]);
    }

    public function set(string $id, mixed $value): ContainerInterface
    {
        $this->values[$id] = $value;
        return $this;
    }

    public function factory(string $id, callable $callable): ContainerInterface
    {
        $this->factories[$id] = $callable;
        return $this;
    }

    public function singleton(string $id, callable $callable): ContainerInterface
    {
        if (array_key_exists($id, $this->values)) {
            throw new \RuntimeException("Service '{$id}' already resolved and cannot be redefined.");
        }

        if (isset($this->factories[$id])) {
            throw new \RuntimeException("Service '{$id}' is already registered.");
        }

        $this->factories[$id] = function (ContainerInterface $container) use ($id, $callable) {
            if (!array_key_exists($id, $this->values)) {
                $this->values[$id] = $callable($container);
            }

            return $this->values[$id];
        };

        return $this;
    }
}