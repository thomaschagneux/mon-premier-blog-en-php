<?php

namespace App\Manager;

use App\Services\Sanitizer;
use Exception;

class ServerManager
{
    /**
     * @var array|mixed[]
     */
    private array $server;

    public function __construct()
    {
        $this->server = Sanitizer::sanitizeArray($_SERVER);
    }

    /**
     * Gets a sanitized server parameter by its key.
     *
     * @param string $key The key of the server parameter.
     * @return string|null The sanitized server parameter or null if the key does not exist.
     */
    public function getServerParams(string $key): ?string
    {
        if (isset($this->server[$key]) && is_string($this->server[$key])) {
            return Sanitizer::sanitizeString($this->server[$key]);
        }
        return null;
    }

    /**
     * Gets a sanitized server parameter by its key.
     * Throws an exception if the key does not exist or is empty.
     *
     * @param string $key The key of the server parameter.
     * @return string The sanitized server parameter.
     * @throws Exception if the server parameter is not set or is empty.
     */
    public function getRequiredServerParam(string $key): string
    {
        if (empty($this->server[$key]) || !is_string($this->server[$key])) {
            throw new Exception("The server parameter is required but not set or empty.");
        }

        return Sanitizer::sanitizeString($this->server[$key]);
    }
}
