<?php

namespace App\Twig;

use App\Manager\ServerManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\core\Router;

/**
 * Class UrlExtension
 *
 * Provides custom Twig functions for generating URLs and retrieving the HTTP referer.
 * Registers the functions 'path' for URL generation and 'referer' for obtaining the referer URL.
 */
class UrlExtension extends AbstractExtension
{
    /**
     * @var ServerManager The server manager instance for retrieving server parameters.
     */
    private ServerManager $serverManager;

    /**
     * UrlExtension constructor.
     * Initializes the extension with the Router and ServerManager instances.
     *
     * @param Router $router The router instance used to generate URLs.
     */
    public function __construct(
        private readonly Router $router,
    ) {
        $this->serverManager = new ServerManager();
    }

    /**
     * Returns the list of custom Twig functions provided by this extension.
     *
     * @return array<TwigFunction> An array of custom Twig functions.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'generatePath']),
            new TwigFunction('referer', [$this, 'getReferer']),
        ];
    }

    /**
     * Generates a URL for the given route name and parameters.
     *
     * Example usage in Twig:
     * {{ path('route_name', { 'param1': 'value1', 'param2': 'value2' }) }}
     *
     * @param string                                 $routeName The name of the route.
     * @param array<int|string, array<mixed>|string> $params    Parameters to replace in the route path.
     *
     * @return string The generated URL.
     */
    public function generatePath(string $routeName, array $params = []): string
    {
        return $this->router->getRouteUrl($routeName, $params);
    }

    /**
     * Retrieves the HTTP referer from the server parameters.
     *
     * Example usage in Twig:
     * {{ referer() }}
     *
     * @return string|null The referer URL, or null if not available.
     */
    public function getReferer(): ?string
    {
        return $this->serverManager->getServerParams('HTTP_REFERER') ?? null;
    }
}
