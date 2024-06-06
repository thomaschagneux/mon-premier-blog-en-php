<?php

namespace App\core;

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
     * @var array|null The parameters of the current request
     */
    private $param;

    /**
     * HttpRequest constructor.
     *
     * Initializes the HttpRequest object with the current URL and HTTP method.
     */
    public function __construct()
    {
        $this->url = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Gets the URL of the current request.
     *
     * @return string The URL of the current request
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets the HTTP method of the current request.
     *
     * @return string The HTTP method of the current request
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Gets the parameters of the current request.
     *
     * @return array|null The parameters of the current request, or null if none are set
     */
    public function getParams()
    {
        return $this->param;
    }
}
