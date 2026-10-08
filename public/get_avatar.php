<?php
declare(strict_types=1);

// Отключаем вывод ошибок в браузер, чтобы не повредить бинарные данные картинки
ini_set('display_errors', '0');
error_reporting(0);

$filename = $_GET['file'] ?? '';

// Строжайшая валидация: разрешаем только имена, которые генерирует наш AvatarUploadService
// Формат: user_{число}_{16 hex символов}.{расширение}
if (!preg_match('/^user_\d+_[a-f0-9]{16}\.(jpg|png|webp|gif)$/i', $filename)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('Not found');
}

// Формируем абсолютный путь к файлу вне публичной директории
// __DIR__ указывает на папку public/, поэтому ../ поднимает нас в корень проекта
$filePath = __DIR__ . '/../storage/avatars/' . $filename;

if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('Not found');
}

// Отдаем файл с правильными заголовками для кэширования и безопасности
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($filePath);

header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
header('Content-Length: ' . filesize($filePath));
// Запрещаем выполнение этого файла как скрипта и разрешаем кэширование на 1 год
header('Cache-Control: public, max-age=31536000, immutable');
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;