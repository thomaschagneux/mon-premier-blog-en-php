<?php

namespace App\core;

/**
 * Class Router
 *
 * This class handles the routing mechanism for the application. It allows
 * registering routes and dispatching requests to the appropriate controller
 * actions based on the URL and HTTP method.
 */
class Router
{
    /**
     * @var array<array{method: string, path: string, callback: callable|array{class-string, string}}> $routes 
     * 
     * Array of registered routes
     */
    private array $routes = [];
    
    /** 
     * @var array<string, array{method: string, path: string, callback: callable|array{class-string, string}}> $namedRoutes
     * 
     * Array of named routes for easier URL generation.
     */
    private array $namedRoutes = [];

    /**
     * Adds a route to the routing table.
     *
     * @param string $method HTTP method (e.g., 'GET', 'POST')
     * @param string $path The route path, with optional dynamic segments (e.g., '/user/{id}')
     * @param callable|array{class-string, string} $callback The callback to be executed when the route is matched. This can be
     *                                 a function or an array with a class and method (e.g., [HomeController::class, 'index'])
     * @return void
     */
    public function addRoute(string $method, string $path, $callback, string $name): void
    {
        // Convert dynamic segments in the path to regular expression patterns
        $path = preg_replace('/{[a-zA-Z0-9_]+}/', '([^/]+)', $path);
        // Add the route to the routing table
        $route = [
            'method' => $method,
            'path' => '#^' . $path . '$#',
            'callback' => $callback
        ];

        if ($name) {
            $this->namedRoutes[$name] = $route;
        }

        $this->routes[] = $route;
    }

    /**
     * Handles an incoming request and dispatches it to the appropriate route callback.
     *
     * @param HttpRequest $request The current HTTP request instance
     * 
     * @return void
     */
    public function handleRequest(HttpRequest $request): void
    {
        // Iterate over the registered routes to find a match
        foreach ($this->routes as $route) {

            // Check if the HTTP method and URL match the current route
            if ($route['method'] === $request->getMethod() && preg_match($route['path'], $request->getUrl(), $matches)) {
                array_shift($matches);// Remove the full match from the matches array

                if (is_array($route['callback'])) {
                    // If the callback is an array, treat it as a class method
                    list($controllerName, $method) = $route['callback'];

                    // Check if the controller class exists
                    if (!class_exists($controllerName)) {
                        throw new \RuntimeException("Controller class $controllerName does not exist");
                    }

                    // Instantiate the controller
                    $controller = new $controllerName($this);


                    /** @var callable $callable */
                    $callable = [$controller, $method];
                    // Call the controller method with the matched parameters
                    $response = call_user_func_array($callable, $matches);
                } else {
                    // Call the callback function with the matched parameters
                    $response = call_user_func_array($route['callback'], $matches);
                }

                 // Check if the response is an instance of RedirectResponse
                if ($response instanceof RedirectResponse) {
                    // Send the redirection response to the client
                    $response->send();

                } elseif (is_string($response)) {
                    // If the response is a string, output it
                    echo $response;
                }

                return;// Exit after handling the request
            }
        }
        // If no route was matched, return a 404 response
        http_response_code(404);
        include __DIR__ . '/../Views/404.php';
    }

    /**
     * Generates a URL for a named route with the given parameters.
     * 
     * @param string $name The name of the route
     * @param array<int|string, array<mixed>|string> $params
     * @return string The generated URL
     * @throws \RuntimeException If the named route does not exist or if there is an error in processing the route regex
     */
    public function getRouteUrl(string $name, array $params = []): string
    {
        // Check if the named route exists
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route does not exist");
        }

        // Retrieve the path for the named route
        $route = $this->namedRoutes[$name]['path'];

        // Replace dynamic segments in the path with the provided parameters
        foreach ($params as $value) {
            $route = preg_replace('/\(\[\^\/\]\+\)/', $value, $route, 1);
             // Check if preg_replace returned null, indicating an error
            if ($route === null) {
                throw new \RuntimeException("Error processing route");
            }
        }
        // Remove start (^) and end ($) anchors from the route pattern
        $finalRoute = preg_replace('/#\^|\$#/', '', $route);
        // Check if preg_replace returned null, indicating an error
        if ($finalRoute === null) {
            throw new \RuntimeException("Error processing final route regex");
        }
        // Return the generated URL
        return $finalRoute;
    }
}
