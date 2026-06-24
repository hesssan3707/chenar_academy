<?php

namespace App\Support;

/**
 * Helper class for handling video embeds and URLs.
 * Supports both regular URLs and iframe embed code.
 */
class VideoEmbedHelper
{
    /**
     * Check if a value is an iframe embed code.
     *
     * @param string $value The value to check
     * @return bool True if it's iframe embed code
     */
    public static function isIframe(string $value): bool
    {
        $trimmed = trim($value);
        if (empty($trimmed)) {
            return false;
        }

        // Check if it starts with iframe tag
        if (stripos($trimmed, '<iframe') !== 0 && stripos($trimmed, '< iframe') !== 0) {
            return false;
        }

        // Validate iframe tag has src attribute
        if (preg_match('/<\s*iframe\s+[^>]*src\s*=\s*["\']?([^"\'>\s]+)["\']?[^>]*>/i', $trimmed)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a value is a valid URL.
     *
     * @param string $value The value to check
     * @return bool True if it's a valid URL
     */
    public static function isUrl(string $value): bool
    {
        $trimmed = trim($value);
        return !empty($trimmed) && filter_var($trimmed, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if a value is a valid video source (URL or iframe).
     *
     * @param string $value The value to check
     * @return bool True if it's a valid video source
     */
    public static function isValidSource(string $value): bool
    {
        return self::isIframe($value) || self::isUrl($value);
    }

    /**
     * Sanitize iframe embed code for safe display.
     * Extracts and returns the iframe tag safely.
     *
     * @param string $value The iframe code to sanitize
     * @return string|null The sanitized iframe code or null if invalid
     */
    public static function sanitizeIframe(string $value): ?string
    {
        $trimmed = trim($value);
        if (!self::isIframe($trimmed)) {
            return null;
        }

        // Extract the iframe tag up to and including the closing tag
        if (preg_match('/<\s*iframe\s+[^>]*><\s*\/\s*iframe\s*>/is', $trimmed, $matches)) {
            return $matches[0];
        }

        // If no closing tag, try to find just the opening tag and close it
        if (preg_match('/<\s*iframe\s+[^>]*>/i', $trimmed, $matches)) {
            return $matches[0] . '</iframe>';
        }

        return null;
    }

    /**
     * Extract src URL from iframe embed code.
     *
     * @param string $value The iframe code
     * @return string|null The src URL or null if not found
     */
    public static function extractIframeSrc(string $value): ?string
    {
        if (!self::isIframe($value)) {
            return null;
        }

        if (preg_match('/src\s*=\s*["\']?([^"\'>\s]+)["\']?/i', $value, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
