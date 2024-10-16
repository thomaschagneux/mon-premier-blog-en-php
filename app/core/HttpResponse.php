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
     * @throws SystemExit
     * @return void
     */
    public function terminate(): void
    {
        throw new SystemExit("Script termination requested.");
    }
}
