<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Random\RandomException;
use RuntimeException;

final class BookPhotoUploadService
{
    private const MAX_SIZE = 25 * 1024 * 1024;

    private const PATH_REGEX = '#^(\d+)/book_\1_[a-f0-9]{16}\.jpg$#';

    private string $storageRoot;

    public function __construct(?string $storageRoot = null)
    {
        $root = $storageRoot ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'books';

        $this->storageRoot = rtrim($root, '/\\');
    }

    /**
     * Загружает одно фото.
     *
     * @param array<string, mixed> $file
     *
     * @return string|null Относительный путь для БД или null, если фото не передано.
     * @throws RandomException
     */
    public function uploadOne(array $file, int $userId): ?string
    {
        if ($userId <= 0) {
            throw new RuntimeException('living_book_error_photo');
        }

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('living_book_error_photo');
        }

        $tmpName = $file['tmp_name'] ?? '';

        if (!is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('living_book_error_photo');
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > self::MAX_SIZE) {
            throw new RuntimeException('living_book_error_photo');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);

        if ($mime !== 'image/jpeg') {
            throw new RuntimeException('living_book_error_photo');
        }

        $imageInfo = @getimagesize($tmpName);

        if (
            $imageInfo === false
            || ($imageInfo[2] ?? null) !== IMAGETYPE_JPEG
        ) {
            throw new RuntimeException('living_book_error_photo');
        }

        if (
            isset($imageInfo['mime'])
            && $imageInfo['mime'] !== 'image/jpeg'
        ) {
            throw new RuntimeException('living_book_error_photo');
        }

        $userDir = (string) $userId;

        if (!preg_match('/^\d+$/', $userDir)) {
            throw new RuntimeException('living_book_error_photo');
        }

        $dir = $this->storageRoot . DIRECTORY_SEPARATOR . $userDir;

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('living_book_error_photo');
        }

        $fileName = 'book_' . $userDir . '_' . bin2hex(random_bytes(8)) . '.jpg';
        $targetPath = $dir . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new RuntimeException('living_book_error_photo');
        }

        clearstatcache(true, $targetPath);

        if (!is_file($targetPath) || filesize($targetPath) > self::MAX_SIZE) {
            @unlink($targetPath);

            throw new RuntimeException('living_book_error_photo');
        }

        @chmod($targetPath, 0644);

        return $userDir . '/' . $fileName;
    }

    public function deleteRelativePath(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        if (!preg_match(self::PATH_REGEX, $relativePath)) {
            return;
        }

        $basePath = realpath($this->storageRoot);

        if ($basePath === false) {
            return;
        }

        $fullPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $realPath = realpath($fullPath);

        if (
            $realPath === false
            || !is_file($realPath)
            || !str_starts_with($realPath, $basePath . DIRECTORY_SEPARATOR)
        ) {
            return;
        }

        @unlink($realPath);
    }

    public function buildPublicUrl(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        if (!preg_match(self::PATH_REGEX, $relativePath)) {
            return null;
        }

        return '/public/get_book_photo.php?path=' . rawurlencode($relativePath);
    }
}