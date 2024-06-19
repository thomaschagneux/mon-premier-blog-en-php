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
     * @var array<array{method: string, path: string, callback: callable|array{class-string, string}}> $routes Array of registered routes
     */
    private $routes = [];

    /**
     * Adds a route to the routing table.
     *
     * @param string $method HTTP method (e.g., 'GET', 'POST')
     * @param string $path The route path, with optional dynamic segments (e.g., '/user/{id}')
     * @param callable|array{class-string, string} $callback The callback to be executed when the route is matched. This can be
     *                                 a function or an array with a class and method (e.g., [HomeController::class, 'index'])
     * @return void
     */
    public function addRoute(string $method, string $path, $callback): void
    {
        // Convert dynamic segments in the path to regular expression patterns
        $path = preg_replace('/{[a-zA-Z0-9_]+}/', '([^/]+)', $path);
        // Add the route to the routing table
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

                if (is_string($response)) {
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
}
