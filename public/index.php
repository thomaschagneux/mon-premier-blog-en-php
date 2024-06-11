<?php

// Autoload dependencies installed via Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Use statements to import necessary classes
use App\core\Router;
use App\core\HttpRequest;

// Initialize a new HTTP request instance
$request = new HttpRequest();

/**
 * Create a new Router instance
 */
$router = new Router();

// Include the routes definition file from the config directory
require_once __DIR__ . '/../app/config/Routes.php';

// Define the application routes
defineRoutes($router);

$router->handleRequest($request);
