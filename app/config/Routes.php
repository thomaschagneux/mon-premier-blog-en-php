<?php

use App\Controllers\Admincontroller;
use App\Controllers\UserController;
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
    $router->addRoute('GET', '/', [HomeController::class, 'index'], 'index');

    $router->addRoute('GET', '/about/{id}', [HomeController::class, 'about'], 'about');

    $router->addRoute('GET', '/contact', [HomeController::class, 'contact'], 'contact');

    $router->addRoute('GET', '/login', [AuthController::class, 'loginForm'], 'login_form');

    $router->addRoute('POST', '/login', [AuthController::class, 'login'], 'login');

    $router->addRoute('GET', '/logout', [AuthController::class, 'logout'], 'logout');

    $router->addRoute('GET', '/admin/home', [Admincontroller::class, 'adminHome'], 'admin_home');

    $router->addRoute('GET', '/admin/user/list', [Usercontroller::class, 'adminListUser'], 'admin_list_user');

    $router->addRoute('GET', '/admin/user/add', [Usercontroller::class, 'adminAddUserForm'], 'admin_add_user_form');

    $router->addRoute('POST', '/admin/user/add', [Usercontroller::class, 'addUser'], 'add_user_action');

    $router->addRoute('GET', '/register', [Usercontroller::class, 'registerForm'], 'register_form');

    $router->addRoute('POST', '/register', [Usercontroller::class, 'register'], 'register');

    $router->addRoute('GET', '/admin/user/{id}/remove', [Usercontroller::class, 'removeUser'], 'user_remove');

}
