<?php

namespace App\core;

/**
 * Class HttpResponse
 *
 * This class encapsulates HTTP response operations.
 */
class HttpResponse
{
    /**
     * Terminates the script execution.
     *
     * @return void
     */
    public function terminate(): void
    {
        exit;
    }
}
