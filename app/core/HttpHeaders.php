<?php

namespace App\core;

/**
 * Class HttpHeaders
 *
 * This class encapsulates HTTP header operations.
 */
class HttpHeaders
{
    /**
     * Sends an HTTP header.
     *
     * @param string $header The HTTP header to send.
     * @return void
     */
    public function sendHeader(string $header): void
    {
        // Validate and sanitize the header value
        if ($this->isValidHeader($header)) {
            header($header);
        } else {
            throw new \InvalidArgumentException("Invalid header value: $header");
        }
    }

    /**
     * Validates the HTTP header.
     *
     * @param string $header The HTTP header to validate.
     * @return bool True if the header is valid, false otherwise.
     */
    private function isValidHeader(string $header): bool
    {
        // Basic validation for the header value
        // patern: matches with a->z & A->Z & '/' & '-'
        return preg_match('/^[a-zA-Z0-9\-\/\s:]+$/', $header) === 1;
    }
}
