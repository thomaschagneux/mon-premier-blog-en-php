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
        header($header);
    }
}
