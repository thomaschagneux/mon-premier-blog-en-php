<?php

namespace App\core;

/**
 * Class HttpHeaders
 *
 * This class encapsulates HTTP header operations.
 */
class HttpHeaders implements HttpHeadersInterface
{
    /**
     * Sends an HTTP header.
     *
     * @param string $header The HTTP header to send.
     * @return void
     */
    public function sendHeader(string $header): void
    {
        if (!$this->isValidHeader($header)) {
            throw new \InvalidArgumentException("Invalid header value: ");
        }

        // Use header() function to send the HTTP header
        // The use of header() is necessary here to send HTTP headers, which is a common and standard operation in PHP.
        // The header() function is used to send raw HTTP headers and it is essential for operations like redirection.
        // By encapsulating the header() function in this method, we ensure that it is used in a controlled and secure manner.
        header($header);
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
        return preg_match('/^[a-zA-Z0-9\-\/\s:]+$/', $header) === 1 
        && strpos($header, "\n") === false 
        && strpos($header, "\r") === false;
    }
}
