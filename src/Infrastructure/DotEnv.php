<?php
declare(strict_types=1);

namespace App\Infrastructure;

use InvalidArgumentException;

class DotEnv
{
    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("Файл .env не найден в $path");
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with($line, '#')) {
                continue;
            }

            if (trim($line) === '') {
                continue;
            }

            $parts = explode('=', $line, 2);

            if (count($parts) !== 2) {
                error_log("Неверный формат строки в .env файле: $line");
                continue;
            }

            [$name, $value] = $parts;
            $name = trim($name);
            $value = trim($value);

            if ($name === '') {
                error_log("Пустое имя переменной в .env файле: $line");
                continue;
            }

            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
            } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                $value = substr($value, 1, -1);
            }

            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}