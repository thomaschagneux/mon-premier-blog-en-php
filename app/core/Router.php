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
     * @var array<array{method: string, path: string, callback: callable|array{string, string}}> $routes Array of registered routes
     */
    private $routes = [];

    /**
     * Adds a route to the routing table.
     *
     * @param string $method HTTP method (e.g., 'GET', 'POST')
     * @param string $path The route path, with optional dynamic segments (e.g., '/user/{id}')
     * @param callable|array{string, string} $callback The callback to be executed when the route is matched. This can be
     *                                 a function or an array with a class and method (e.g., [HomeController::class, 'index'])
     * @return void
     */
    public function addRoute($method, $path, $callback): void
    {
        $path = preg_replace('/{[a-zA-Z0-9_]+}/', '([^/]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'path' => '#^' . $path . '$#',
            'callback' => $callback
        ];
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
        // Iterate over each registered route
        foreach ($this->routes as $route) {
            // Check if the HTTP method and URL match the current route
            if ($route['method'] === $request->getMethod() && preg_match($route['path'], $request->getUrl(), $matches)) {
                // Remove the full match from the matches array, leaving only the captured groups
                array_shift($matches);
                
                // Check if the callback is an array (i.e., a controller and method)
                if (is_array($route['callback'])) {
                    // Instantiate the controller
                    $controller = new $route['callback'][0]();
                    // Get the method name
                    $method = $route['callback'][1];
                    // Call the method on the controller with the captured URL parameters
                    $response = call_user_func_array([$controller, $method], $matches);
                } else {
                    // Call the callback function with the captured URL parameters
                    $response = call_user_func_array($route['callback'], $matches);
                }
                // Check if the response is a string and output it
                if (is_string($response)) {
                    echo $response;
                }
                // Stop processing further routes once a match is found
                return;
            }
        }

        // Default 404 handler if no route matches
        http_response_code(404);
        include __DIR__ . '/../Views/404.php';
        
    }
}
