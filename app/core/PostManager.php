<?php

namespace App\core;

class PostManager
{
    /**
     * Validate and sanitize a POST parameter.
     *
     * @param string $key The key of the POST parameter.
     * @return string|null The sanitized value or null if the key does not exist.
     */
    public function getPostParam(string $key): ?string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);

        // Check if the value is null
        if (null === $value || false === $value) {
            return null;
        }

        // Trim and sanitize the input
        return trim($this->sanitizeInput($value));
    }

    /**
     * Sanitize a given input.
     *
     * @param string $input The input to sanitize.
     * @return string The sanitized input.
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}