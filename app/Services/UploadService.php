<?php

declare(strict_types=1);

namespace App\Services;

final class UploadService
{
    private const MIME_ALIASES = [
        'image/jpg' => 'image/jpeg',
        'image/pjpeg' => 'image/jpeg',
        'image/x-png' => 'image/png',
    ];

    public function store(?array $file, string $directory): ?string
    {
        $stored = $this->storeWithRules($file, public_path('uploads/' . trim($directory, '/')), 'uploads/' . trim($directory, '/'), [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ], 'Only JPG, PNG, WEBP, and GIF images are allowed.');

        return $stored['path'] ?? null;
    }

    public function storePatientRecordDocument(?array $file, string $directory): ?array
    {
        return $this->storeWithRules($file, storage_path(trim($directory, '/')), trim($directory, '/'), [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'image/heic',
            'image/heif',
            'application/pdf',
        ], 'Only JPG, PNG, WEBP, GIF, HEIC, HEIF, and PDF files are allowed.');
    }

    private function storeWithRules(?array $file, string $targetDir, string $relativeDir, array $allowedMimeTypes, string $invalidTypeMessage): ?array
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

        $mimeType = $this->normalizeMimeType($this->detectMimeType($file));
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \RuntimeException($invalidTypeMessage);
        }

        if (!is_dir($targetDir) && !@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to create the upload directory.');
        }

        if (!is_writable($targetDir)) {
            throw new \RuntimeException('Upload directory is not writable.');
        }

        $extension = pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION) ?: $this->extensionFromMimeType($mimeType);
        $filename = bin2hex(random_bytes(12)) . '.' . strtolower($extension);
        $destination = $targetDir . DIRECTORY_SEPARATOR . $filename;
        $tmpName = (string) ($file['tmp_name'] ?? '');

        $moved = $tmpName !== '' && move_uploaded_file($tmpName, $destination);
        if (!$moved && $tmpName !== '' && is_file($tmpName)) {
            $moved = @rename($tmpName, $destination) || @copy($tmpName, $destination);
        }

        if (!$moved) {
            throw new \RuntimeException('Unable to move the uploaded file.');
        }

        return [
            'path' => trim($relativeDir, '/') . '/' . $filename,
            'absolute_path' => $destination,
            'mime_type' => $mimeType,
            'original_filename' => (string) ($file['name'] ?? $filename),
        ];
    }

    private function detectMimeType(array $file): string
    {
        $tmpName = (string) ($file['tmp_name'] ?? '');
        $mimeType = '';

        if ($tmpName !== '' && function_exists('mime_content_type')) {
            $mimeType = mime_content_type($tmpName) ?: '';
        }

        if (($mimeType === '' || $mimeType === 'application/octet-stream') && $tmpName !== '' && function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mimeType = finfo_file($finfo, $tmpName) ?: $mimeType;
                finfo_close($finfo);
            }
        }

        if ($mimeType !== 'application/octet-stream' && $mimeType !== '') {
            return $mimeType;
        }

        return match (strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'heic' => 'image/heic',
            'heif' => 'image/heif',
            'pdf' => 'application/pdf',
            default => $mimeType,
        };
    }

    private function extensionFromMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'application/pdf' => 'pdf',
            default => 'jpg',
        };
    }

    private function normalizeMimeType(string $mimeType): string
    {
        $mimeType = strtolower(trim($mimeType));

        return self::MIME_ALIASES[$mimeType] ?? $mimeType;
    }
}
