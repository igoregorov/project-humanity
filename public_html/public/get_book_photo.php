<?php

declare(strict_types=1);

$path = $_GET['path'] ?? '';

if (!is_string($path) || $path === '') {
    http_response_code(404);
    exit;
}

if (str_contains($path, "\0")) {
    http_response_code(404);
    exit;
}

if (!preg_match('#^(\d+)/book_\1_[a-f0-9]{16}\.jpg$#', $path)) {
    http_response_code(404);
    exit;
}

$storageRoot = realpath(__DIR__ . '/../../storage/books');

if ($storageRoot === false) {
    http_response_code(404);
    exit;
}

$fullPath = $storageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
$realPath = realpath($fullPath);

if (
    $realPath === false
    || !is_file($realPath)
    || !is_readable($realPath)
    || strpos($realPath, $storageRoot . DIRECTORY_SEPARATOR) !== 0
) {
    http_response_code(404);
    exit;
}

try {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($realPath);
} catch (Throwable) {
    http_response_code(404);
    exit;
}

if ($mime !== 'image/jpeg') {
    http_response_code(404);
    exit;
}

$size = filesize($realPath);

if ($size === false) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/jpeg');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . (string) $size);

readfile($realPath);
exit;