<?php

declare(strict_types=1);

namespace App\Helpers;

class ServerLimitsHelper
{
    /**
     * Get server limits for file uploads.
     */
    public static function getServerLimits(): array
    {
        $postMaxSize = self::parseSize(ini_get('post_max_size') ?: '0');
        $uploadMaxFilesize = self::parseSize(ini_get('upload_max_filesize') ?: '0');
        $maxFileUploads = (int) ini_get('max_file_uploads');

        return [
            'post_max_size'          => $postMaxSize,
            'upload_max_filesize'    => $uploadMaxFilesize,
            'max_file_uploads'       => $maxFileUploads,
            'recommended_total_size' => (int) ($postMaxSize * 0.8), // 80% of post_max_size for safety
        ];
    }

    /**
     * Parse PHP ini size value (e.g., "8M", "1G", "512K") to bytes.
     */
    public static function parseSize(string $size): int
    {
        $unit = strtolower($size[strlen($size) - 1] ?? '');
        $size = (int) $size;

        return match ($unit) {
            'g'     => $size * 1024 * 1024 * 1024,
            'm'     => $size * 1024 * 1024,
            'k'     => $size * 1024,
            default => $size,
        };
    }

    /**
     * Format bytes to human-readable size (e.g., "2.5 MB", "1.2 GB").
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get the maximum safe upload size based on server configuration.
     */
    public static function getMaxSafeUploadSize(): int
    {
        $limits = self::getServerLimits();

        return min(
            $limits['upload_max_filesize'],
            $limits['recommended_total_size']
        );
    }

    /**
     * Check if a file size is within server limits.
     */
    public static function isFileSizeValid(int $fileSize, ?int $customLimit = null): bool
    {
        $limit = $customLimit ?? self::getMaxSafeUploadSize();

        return $fileSize <= $limit;
    }

    /**
     * Get server limits formatted for display.
     */
    public static function getFormattedServerLimits(): array
    {
        $limits = self::getServerLimits();

        return [
            'post_max_size'          => self::formatBytes($limits['post_max_size']),
            'upload_max_filesize'    => self::formatBytes($limits['upload_max_filesize']),
            'max_file_uploads'       => $limits['max_file_uploads'],
            'recommended_total_size' => self::formatBytes($limits['recommended_total_size']),
        ];
    }
}
