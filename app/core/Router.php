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
        foreach ($this->routes as $route) {
            if ($route['method'] === $request->getMethod() && preg_match($route['path'], $request->getUrl(), $matches)) {
                array_shift($matches);

                if (is_array($route['callback'])) {
                    list($controllerName, $method) = $route['callback'];

                    if (!class_exists($controllerName)) {
                        throw new \RuntimeException("Controller class $controllerName does not exist");
                    }

                    $controller = new $controllerName();


                    /** @var callable $callable */
                    $callable = [$controller, $method];
                    $response = call_user_func_array($callable, $matches);
                } else {

                    $response = call_user_func_array($route['callback'], $matches);
                }

                if (is_string($response)) {
                    echo $response;
                }

                return;
            }
        }

        http_response_code(404);
        include __DIR__ . '/../Views/404.php';
    }
}
