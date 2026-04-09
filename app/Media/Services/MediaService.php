<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Media\Services;

use ItsMeStevieG\PHPBasePlate\Media\Repositories\MediaRepository;

class MediaService
{
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt',
        'zip', 'mp4', 'mp3',
    ];

    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

    public function __construct(
        private readonly MediaRepository $repo,
        private readonly string $uploadPath,
    ) {
    }

    public function find(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function list(int $page = 1, int $perPage = 24, ?string $mimeFilter = null): array
    {
        return $this->repo->list($page, $perPage, $mimeFilter);
    }

    /**
     * @return array{success: bool, id?: int, error?: string}
     */
    public function upload(array $file, ?int $userId = null): array
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload failed.'];
        }

        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return ['success' => false, 'error' => "File type '.{$extension}' is not allowed."];
        }

        $mimeType = mime_content_type($file['tmp_name']) ?: 'application/octet-stream';

        // Generate safe file name
        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        $yearMonth = date('Y/m');
        $relativePath = $yearMonth . '/' . $safeName;
        $absoluteDir = $this->uploadPath . '/' . $yearMonth;

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0775, true);
        }

        $absolutePath = $absoluteDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file.'];
        }

        // Get image dimensions if applicable
        $width = null;
        $height = null;
        if (in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true) && $mimeType !== 'image/svg+xml') {
            $imageInfo = @getimagesize($absolutePath);
            if ($imageInfo !== false) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        $id = $this->repo->create([
            'disk' => 'local',
            'path' => $relativePath,
            'file_name' => $safeName,
            'original_name' => $originalName,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size_bytes' => $file['size'],
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $userId,
        ]);

        return ['success' => true, 'id' => $id];
    }

    public function updateMeta(int $id, array $data): void
    {
        $this->repo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $media = $this->repo->findById($id);
        if ($media === null) {
            return false;
        }

        $filePath = $this->uploadPath . '/' . $media['path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->repo->delete($id);

        return true;
    }

    public function getPublicUrl(array $media): string
    {
        return '/uploads/' . $media['path'];
    }
}
