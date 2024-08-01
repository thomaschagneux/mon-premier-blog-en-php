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
     * Validate nonce to prevent CSRF attacks.
     *
     * @return bool Returns true if the nonce is valid, false otherwise.
     */
    public function isValidNonce(): bool
    {
        // Use filter_input to access the nonce
        $nonce = filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);

        if ($nonce === null) {
            return false;
        }

        // Replace the return statement with actual nonce validation logic
        return $_SESSION['nonce'] === $nonce; // Replace with real verification
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