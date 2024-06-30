<?php

namespace App\core;

use Exception;

/**
 * Class RedirectResponse
 *
 * This class represents an HTTP response that redirects the client to a different URL.
 * It encapsulates the URL to which the client should be redirected and provides methods
 * to send the redirection response.
 */
class RedirectResponse
{
    /**
     * @var string The URL to which the client will be redirected.
     */
    private string $url;

    /**
     * @var HttpHeaders The HTTP headers handler.
     */
    private HttpHeaders $headers;

    /**
     * @var HttpResponse The HTTP response handler.
     */
    private HttpResponse $response;

    /**
     * RedirectResponse constructor.
     *
     * @param string $url The URL to which the client will be redirected.
     * @param HttpHeaders $headers The HTTP headers handler.
     * @param HttpResponse $response The HTTP response handler.
     * @throws Exception if the URL is not valid
     * 
     */
    public function __construct(string $url, HttpHeaders $headers, HttpResponse $response)
    {
        $this->url = $this->sanitizeUrl($url);
        $this->headers = $headers;
        $this->response = $response;
    }

    /**
     * Sends the redirection response to the client.
     *
     * This method sends an HTTP header to the client instructing it to redirect to the URL
     * specified in this response. It then terminates the script execution to ensure that
     * no further output is sent to the client.
     *
     * @return void
     */
    public function send(): void
    {
        $this->headers->sendHeader('Location: ' . $this->url);
        $this->response->terminate();
    }
    
    /**
     * Gets the URL to which the client will be redirected.
     *
     * @return string The URL to which the client will be redirected.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Sanitizes the URL.
     *
     * @param string $url The URL to sanitize.
     * @return string The sanitized URL.
     * @throws Exception if the URL is not valid
     */
    private function sanitizeUrl(string $url): string
    {
        $sanitizeUrl = filter_var($url, FILTER_SANITIZE_URL);

        if($sanitizeUrl === false || empty($sanitizeUrl)) {
            throw new Exception("Invalid URL");
        }

        return $sanitizeUrl;
    }
}