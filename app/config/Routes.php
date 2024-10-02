<?php

use App\Controllers\Admincontroller;
use App\Controllers\ErrorController;
use App\Controllers\PostController;
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

    $router->addRoute('GET', '/error/500', [ErrorController::class, 'error500'], 'error_500');

    $router->addRoute('GET', '/error/404', [ErrorController::class, 'error404'], 'error_404');

    $router->addRoute('GET', '/error/403', [ErrorController::class, 'error403'], 'error_403');

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

    $router->addRoute('GET', '/admin/user/{id}/edit', [Usercontroller::class, 'editUserForm'], 'user_edit_form');

    $router->addRoute('POST', '/admin/user/{id}/edit', [Usercontroller::class, 'editUser'], 'edit_user_action');

    $router->addRoute('GET', '/admin/user/{id}/show', [Usercontroller::class, 'adminUserShow'], 'user_show');

    $router->addRoute('GET', '/admin/post/list', [PostController::class, 'postList'], 'list_post');

    $router->addRoute('GET', '/admin/post/add', [PostController::class, 'addPostForm'], 'add_post_form');

    $router->addRoute('POST', '/admin/post/add', [PostController::class, 'addPostAction'], 'add_post_action');

    $router->addRoute('GET', '/admin/post/{id}/show', [PostController::class, 'postShow'], 'post_show');
}
