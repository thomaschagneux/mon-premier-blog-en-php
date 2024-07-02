<?php

namespace App\core;

/**
 * Interface HttpHeadersInterface
 *
 * This interface defines methods for sending HTTP headers.
 */
interface HttpHeadersInterface
{
    /**
     * Sends an HTTP header.
     *
     * @param string $header The HTTP header to send.
     * @return void
     */
    public function sendHeader(string $header): void;
}
