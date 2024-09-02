<?php

namespace App\core;

use App\Manager\ServerManager;

/**
 * Class HttpRequest
 *
 * This class represents an HTTP request, providing access to the URL, HTTP method,
 * and any parameters associated with the request.
 */
class HttpRequest
{
     /**
     * @var string The URL of the current request
     */
    private $url;

    /**
     * @var string The HTTP method of the current request (e.g., GET, POST)
     */
    private $method;

    /**
     * @var array<int|string, array<mixed>|string> The parameters of the current request
     */
    private array $param;

    private ServerManager $server;

    /**
     * HttpRequest constructor.
     *
     * Initializes the HttpRequest object with the current URL and HTTP method.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->server = new ServerManager();
        $this->url = $this->server->getRequiredServerParam('REQUEST_URI');
        $this->method = $this->server->getRequiredServerParam('REQUEST_METHOD');
        $this->param = $this->initializeParams();
    }

     /**
     * Initializes the parameters of the current request.
     *
     * @return array<int|string, array<mixed>|string> The parameters of the current request
     */
    private function initializeParams(): array
    {
        switch ($this->method) {
            case 'GET':
                return $_GET;
            case 'POST':
                return $_POST;
            case 'PUT':
            case 'DELETE':
                $input = file_get_contents('php://input');
                if ($input === false) {
                    return [];
                }
                parse_str($input, $params);
                return $params;
            default:
                return [];
        }
    }

    /**
     * Gets the URL of the current request.
     *
     * @return string The URL of the current request
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Gets the HTTP method of the current request.
     *
     * @return string The HTTP method of the current request
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Gets the parameters of the current request.
     *
     * @return array<int|string, array<mixed>|string> The parameters of the current request, or null if none are set
     */
    public function getParams(): array
    {
        return $this->param;
    }
}
