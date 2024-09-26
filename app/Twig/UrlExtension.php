<?php

namespace App\Twig;

use App\Controllers\AbstractController;
use App\Manager\ServerManager;
use App\Services\HelperServices;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\core\Router;

/**
 * Class UrlExtension
 *
 * This class defines a custom Twig extension for generating URLs based on route names.
 * It registers a Twig function 'path' that can be used in Twig templates to generate URLs.
 */
class UrlExtension extends AbstractExtension
{
    /**
     * @var Router The router instance used to generate URLs.
     */
    private Router $router;

    private ServerManager $serverManager;

    private HelperServices $helperServices;

    /**
     * UrlExtension constructor.
     *
     * @param Router $router The router instance used to generate URLs.
     */

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->serverManager = new ServerManager();
        $this->helperServices = new HelperServices();
    }

    /**
     * Returns the list of custom Twig functions provided by this extension.
     *
     * @return array<TwigFunction> The list of custom Twig functions.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'generatePath']),
            new TwigFunction('referer', [$this, 'getReferer']),
            new TwigFunction('dumper', [$this, 'dumper']),
        ];
    }

     /**
     * Generates a URL for the given route name and parameters.
     *
     * @param string $routeName The name of the route.
     * @param array<int|string, array<mixed>|string> $params Parameters to replace in the route path.
     * @return string The generated URL.
     */
    public function generatePath(string $routeName, array $params = []): string
    {
        return $this->router->getRouteUrl($routeName, $params);
    }

    public function getReferer(): ?string
    {
        return $this->serverManager->getServerParams('HTTP_REFERER') ?? null;
    }

    public function dumper(mixed $var): void
    {
        $this->helperServices->dump($var);
    }
}
