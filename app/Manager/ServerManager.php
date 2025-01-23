<?php

namespace App\Manager;

use App\Services\Sanitizer;
use Exception;

/**
 * Class ServerManager
 * Manages and sanitizes access to the PHP `$_SERVER` global variable.
 */
class ServerManager
{
    /**
     * @var array<string, mixed> The sanitized server parameters.
     */
    private array $server;

    /**
     * ServerManager constructor.
     * Initializes the sanitized server parameters from the `$_SERVER` global.
     */
    public function __construct()
    {
        $this->server = Sanitizer::sanitizeArray($_SERVER);
    }

    /**
     * Gets a sanitized server parameter by its key.
     *
     * @param string $key The key of the server parameter.
     *
     * @return string|null The sanitized server parameter, or null if the key does not exist.
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
     *
     * @throws Exception If the server parameter is not set or is empty.
     *
     * @return string The sanitized server parameter.
     */
    public function getRequiredServerParam(string $key): string
    {
        if (empty($this->server[$key]) || !is_string($this->server[$key])) {
            throw new Exception('The server parameter is required but not set or empty.');
        }

        return Sanitizer::sanitizeString($this->server[$key]);
    }
}
