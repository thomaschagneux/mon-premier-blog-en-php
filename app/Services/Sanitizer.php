<?php

namespace App\Services;

class Sanitizer
{
    /**
     * Sanitize a string by removing harmful characters.
     *
     * @param string $data The string to sanitize.
     * @return string The sanitized string.
     */
    public static function sanitizeString(string $data): string
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize an array recursively.
     *
     * @param array<mixed> $data The array to sanitize.
     * @return array<mixed> The sanitized array.
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitizedArray = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitizedArray[$key] = self::sanitizeArray($value);
            } else {
                $sanitizedArray[$key] = self::sanitizeVariable($value);
            }
        }
        return $sanitizedArray;
    }

    /**
     * Sanitize a variable based on its type.
     *
     * @param mixed $data The data to sanitize (string, array, etc.).
     * @return mixed The sanitized data.
     */
    public static function sanitizeVariable(mixed $data): mixed
    {
        if (is_array($data)) {
            return self::sanitizeArray($data);
        } elseif (is_string($data)) {
            return self::sanitizeString($data);
        }

        // If it's not an array or string, return the data as-is
        // Add more sanitization rules if needed for other types
        return $data;
    }
}
