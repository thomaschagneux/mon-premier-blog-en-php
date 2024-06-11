<?php

use App\core\Router;
use App\Controllers\HomeController;

/**
 * Define application routes
 * 
 * Each route maps a URL pattern and HTTP method to a specific controller action.
 * The controller action is specified as an array with the controller class and method.
 *
 * @param Router $router The router instance to add routes to
 */
function defineRoutes(Router $router): void
{
    // Define the route for the home page
    $router->addRoute('GET', '/', [HomeController::class, 'index']);

    // Define the route for the about page with a dynamic {id} parameter
    $router->addRoute('GET', '/about/{id}', [HomeController::class, 'about']);

    // Define the route for the contact page
    $router->addRoute('GET', '/contact', [HomeController::class, 'contact']);
}
