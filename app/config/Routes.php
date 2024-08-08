<?php

use App\core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;

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
    $router->addRoute('GET', '/', [HomeController::class, 'index'], 'index');

    // Define the route for the about page with a dynamic {id} parameter
    $router->addRoute('GET', '/about/{id}', [HomeController::class, 'about'], 'about');

    // Define the route for the contact page
    $router->addRoute('GET', '/contact', [HomeController::class, 'contact'], 'contact');

    $router->addRoute('GET', '/login', [AuthController::class, 'loginForm'], 'login_form');

    $router->addRoute('POST', '/login', [AuthController::class, 'login'], 'login');

    $router->addRoute('GET', '/logout', [AuthController::class, 'logout'], 'logout');
}
