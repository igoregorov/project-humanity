<?php
declare(strict_types=1);

// src/Infrastructure/SimpleContainer.php

namespace App\Infrastructure;

class SimpleContainer implements ContainerInterface
{
    private array $values = [];
    private array $factories = [];

    public function get(string $id)
    {
        if (isset($this->values[$id])) {
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
        return isset($this->values[$id]) || isset($this->factories[$id]);
    }

    public function set(string $id, $value): ContainerInterface
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
        $this->factories[$id] = function (ContainerInterface $container) use ($id, $callable) {
            if (!array_key_exists($id, $this->values)) {
                $this->values[$id] = $callable($container);
            }
            return $this->values[$id];
        };
        return $this;
    }
}