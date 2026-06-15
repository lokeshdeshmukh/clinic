<?php

declare(strict_types=1);

namespace App\Services;

final class UploadService
{
    public function store(?array $file, string $directory): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed.');
        }

        $maxBytes = ((int) config('app.upload_max_mb', 5)) * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes) {
            throw new \RuntimeException('Uploaded file exceeds the configured size limit.');
        }

        $mimeType = mime_content_type($file['tmp_name']) ?: '';
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new \RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
        }

        $extension = pathinfo((string) $file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $relativeDir = 'uploads/' . trim($directory, '/');
        $targetDir = public_path($relativeDir);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $filename = bin2hex(random_bytes(12)) . '.' . strtolower($extension);
        $destination = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Unable to move the uploaded file.');
        }

        return $relativeDir . '/' . $filename;
    }
}
