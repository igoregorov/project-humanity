// src/Infrastructure/Security/AvatarUploadService.php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use InvalidArgumentException;
use RuntimeException;
use finfo;

class AvatarUploadService
{
    private const MAX_SIZE_BYTES = 20 * 1024 * 1024; // 20 MB
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    public function __construct(
        private readonly string $uploadDir,
        private readonly string $publicUrlPrefix = '/avatars/'
    ) {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
                throw new RuntimeException("Не удалось создать директорию: {$this->uploadDir}");
            }
        }
    }

    /**
     * Загружает аватар и возвращает имя сохранённого файла.
     *
     * @param array $file Массив $_FILES['avatar']
     * @param int   $userId ID пользователя (для имени файла)
     * @return string Имя файла (без пути)
     * @throws InvalidArgumentException|RuntimeException
     */
    public function upload(array $file, int $userId): string
    {
        $this->validateUpload($file);

        // Проверка MIME по реальному содержимому (не по расширению!)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime === false || !isset(self::ALLOWED_MIME[$mime])) {
            throw new InvalidArgumentException('Недопустимый тип файла. Разрешены: JPG, PNG, WEBP, GIF.');
        }

        // Дополнительная проверка: расширение должно соответствовать MIME
        $ext = self::ALLOWED_MIME[$mime];

        // Уникальное имя: user_{id}_{random}.{ext}
        $filename = sprintf('user_%d_%s.%s', $userId, bin2hex(random_bytes(8)), $ext);
        $destination = rtrim($this->uploadDir, '/') . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Не удалось сохранить файл на сервере.');
        }

        return $filename;
    }

    /**
     * Удаляет файл аватара.
     */
    public function delete(string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }
        // basename() — защита от path traversal
        $path = rtrim($this->uploadDir, '/') . '/' . basename($filename);
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Возвращает публичный URL для отображения.
     */
    public function getPublicUrl(string $filename): string
    {
        return $this->publicUrlPrefix . basename($filename);
    }

    private function validateUpload(array $file): void
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new InvalidArgumentException('Некорректные параметры файла.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new InvalidArgumentException('Файл превышает максимально допустимый размер.');
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE:
                throw new InvalidArgumentException('Файл не был загружен.');
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                throw new RuntimeException('Ошибка сервера при загрузке файла.');
            default:
                throw new RuntimeException('Неизвестная ошибка загрузки.');
        }

        if ($file['size'] === 0) {
            throw new InvalidArgumentException('Пустой файл.');
        }

        if ($file['size'] > self::MAX_SIZE_BYTES) {
            throw new InvalidArgumentException('Размер файла превышает 20 МБ.');
        }
    }
}
