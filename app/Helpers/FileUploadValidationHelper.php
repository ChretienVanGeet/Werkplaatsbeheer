<?php

declare(strict_types=1);

namespace App\Helpers;

class FileUploadValidationHelper
{
    /**
     * Get all translations needed for file upload validation.
     */
    public static function getTranslations(): array
    {
        return [
            'File exceeds :limitMB limit'                                                                                      => __('File exceeds :limitMB limit'),
            'File type not allowed'                                                                                            => __('File type not allowed'),
            'Total file size exceeds server limit. Please upload fewer or smaller files.'                                      => __('Total file size exceeds server limit. Please upload fewer or smaller files.'),
            'File Upload Errors'                                                                                               => __('File Upload Errors'),
            'Upload failed: Files are too large for server limits. Please upload smaller files or contact your administrator.' => __('Upload failed: Files are too large for server limits. Please upload smaller files or contact your administrator.'),
            'Upload failed: Please check your files and try again.'                                                            => __('Upload failed: Please check your files and try again.'),
        ];
    }

    /**
     * Get the configuration data for JavaScript file upload validation.
     */
    public static function getValidationConfig(array $customLimits = []): array
    {
        return [
            'serverLimits' => array_merge(ServerLimitsHelper::getServerLimits(), $customLimits),
            'translations' => self::getTranslations(),
        ];
    }

    /**
     * Get allowed file types for validation.
     */
    public static function getAllowedTypes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'image/jpeg',
            'image/jpg',
            'image/pjpeg',
            'image/png',
            'image/x-png',
            'image/gif',
            'image/webp',
            'application/zip',
            'application/x-rar-compressed',
        ];
    }

    /**
     * Get human-readable description of allowed file types.
     */
    public static function getAllowedTypesDescription(): string
    {
        return __('Allowed files: PDF, Word, Excel, PowerPoint, images, text, archives. Max size: 5MB per file.');
    }
}
