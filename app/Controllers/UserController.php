<?php

namespace App\Controllers;


use App\core\RedirectResponse;
use App\core\Router;
use App\Models\User;
use App\Services\CustomTables\UserTableService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserController extends AbstractController
{
    private User $user;
    private UserTableService $userTableService;


    public function __construct(
        Router $router,
    )
    {
        parent::__construct($router);
        $this->user = new User();
        $this->userTableService = new UserTableService($this->user, $this->twig, $router);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     *
     */
    public function adminListUser(): string|RedirectResponse
    {
        if ($this->isAdmin())
        {
            $this->user = new User();
            $users = $this->user->getAllUsers();
            $table = $this->userTableService->getUserTable();

            return $this->render('user/list.html.twig', ['users' => $users, 'table' => $table]);
        }
        return $this->redirectToReferer();
    }
}