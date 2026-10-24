<?php
// src/Http/functions.php
declare(strict_types=1);

namespace App\Http;

use JetBrains\PhpStorm\NoReturn;

#[NoReturn]
function sendCleanErrorPage(
    string $message = '',
    ?string $file = null,
    ?string $line = null,
    ?string $trace = null,
    bool $debugMode = false,
    int $statusCode = 500
): void {
    if (ob_get_level()) {
        ob_clean();
    }

    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=utf-8');
    }

    $pageTitle = htmlspecialchars(
        $statusCode === 404 ? 'Страница не найдена' : 'Ошибка сервера',
        ENT_QUOTES,
        'UTF-8'
    );

    $heading = htmlspecialchars(
        $statusCode === 404 ? 'Страница не найдена' : 'Произошла ошибка при генерации страницы',
        ENT_QUOTES,
        'UTF-8'
    );

    $description = htmlspecialchars(
        $statusCode === 404
            ? 'Запрошенная страница не существует.'
            : 'Пожалуйста, сообщите об этом администратору.',
        ENT_QUOTES,
        'UTF-8'
    );

    $html = <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$pageTitle}</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.error-container {
    background-color: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 800px;
    width: 90%;
}
h1 {
    color: #d32f2f;
    margin-top: 0;
}
.error-details {
    background-color: #f9f9f9;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 1rem;
    font-family: monospace;
    white-space: pre-wrap;
    overflow-x: auto;
}
</style>
</head>
<body>
<div class="error-container">
<h1>{$heading}</h1>
<p>{$description}</p>
HTML;

    if ($debugMode) {
        $html .= '<div class="error-details">';
        $html .= '<strong>Ошибка:</strong> ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '<br/>';

        if ($file !== null && $file !== '') {
            $html .= '<strong>Файл:</strong> ' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '<br/>';
        }

        if ($line !== null && $line !== '') {
            $html .= '<strong>Строка:</strong> ' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '<br/>';
        }

        if ($trace !== null && $trace !== '') {
            $html .= '<strong>Трассировка стека:</strong><br/>' . htmlspecialchars($trace, ENT_QUOTES, 'UTF-8');
        }

        $html .= '</div>';
    }

    $html .= '</div></body></html>';

    echo $html;
    exit(1);
}