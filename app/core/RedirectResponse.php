<?php

namespace App\core;

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
     * RedirectResponse constructor.
     *
     * @param string $url The URL to which the client will be redirected.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
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
        header('Location: ' . $this->url);
        exit;
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
}