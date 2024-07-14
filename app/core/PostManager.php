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
        if (!isset($_POST[$key])) {
            return null;
        }

        //
        $value = stripslashes($_POST[$key]);
        return trim($this->sanitizeInput($value));
    }

    /**
     * Validate nonce to prevent CSRF attacks.
     *
     * @return bool Returns true if the nonce is valid, false otherwise.
     */
    public function isValidNonce(): bool
    {
        $nonce = $_POST['nonce'] ?? '';
        // Votre logique pour valider le nonce
        // Par exemple : return $_SESSION['nonce'] === $nonce;
        return true; // Remplacer par la vérification réelle
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