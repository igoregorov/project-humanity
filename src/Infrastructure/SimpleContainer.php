<?php
declare(strict_types=1);
// SimpleContainer.php

// Упрощённая реализация DI-контейнера, вдохновлённая Pimple
namespace App\Infrastructure;

use InvalidArgumentException;

class SimpleContainer
{
    private array $values = [];
    private array $factories = [];

    public function set($id, $value): static
    {
        $this->values[$id] = $value;
        return $this;
    }

    public function factory($id, callable $callable): static
    {
        $this->factories[$id] = $callable;
        return $this;
    }

    public function get($id)
    {
        if (isset($this->values[$id])) {
            return $this->values[$id];
        }

        if (isset($this->factories[$id])) {
            $factory = $this->factories[$id];
            // Вызываем фабрику с контейнером в качестве аргумента
            $value = $factory($this);
            // Если это не shared (фабрика), сохраняем результат
            // Для простоты фабрики будут возвращать новый объект каждый раз.

            // Если будет нужен синглтон, как Pimple по умолчанию
            // $this->values[$id] = $value;
            return $value;
        }

        throw new InvalidArgumentException("Identifier '$id' is not defined.");
    }

    // Метод для получения shared (один экземпляр) сервиса
    public function singleton($id, callable $callable): static
    {
        $this->factories[$id] = function ($container) use ($id, $callable) {
            if (!array_key_exists($id, $container->values)) {
                $container->values[$id] = $callable($container);
            }
            return $container->values[$id];
        };
        return $this;
    }
}