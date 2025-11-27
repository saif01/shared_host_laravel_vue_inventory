<?php

namespace App\Support;

use Illuminate\Support\Str;

class MediaPath
{
    /**
        * Normalize an uploaded file path for storage (strip host and leading slashes).
        */
    public static function normalize(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $value = trim($path);
        if ($value === '') {
            return null;
        }

        // Handle full URLs (keep external URLs intact unless they point to uploads)
        if (Str::startsWith($value, ['http://', 'https://'])) {
            $parsed = parse_url($value);
            $urlPath = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';

            if ($urlPath !== '' && self::isUploadPath($urlPath)) {
                return self::stripStoragePrefix($urlPath);
            }

            return $value;
        }

        return self::stripStoragePrefix($value);
    }

    /**
        * Build a full URL for an uploaded file path.
        */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $value = trim($path);
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//', 'data:'])) {
            // If the URL points to our uploads folder, rebuild it using the current host
            if (Str::startsWith($value, ['http://', 'https://'])) {
                $parsed = parse_url($value);
                $urlPath = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';

                if ($urlPath !== '' && self::isUploadPath($urlPath)) {
                    return asset(self::stripStoragePrefix($urlPath));
                }
            }

            return $value;
        }

        return asset(self::stripStoragePrefix($value));
    }

    /**
        * Strip storage prefix and leading slashes from a path.
        */
    private static function stripStoragePrefix(string $path): string
    {
        $clean = ltrim($path, '/');
        // Convert storage/uploads/... to uploads/...
        $clean = preg_replace('/^storage\//i', '', $clean);

        return $clean;
    }

    /**
        * Check if the path is inside the uploads directory.
        */
    private static function isUploadPath(string $path): bool
    {
        $clean = ltrim($path, '/');
        return preg_match('/^(uploads\/|storage\/uploads\/)/i', $clean) === 1;
    }
}
