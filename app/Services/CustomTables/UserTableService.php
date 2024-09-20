<?php

namespace App\Services\CustomTables;

use App\core\Router;
use App\Models\User;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserTableService extends AbstractTableService
{
    private User $userModel;

    public function __construct(User $userModel, Environment $twig, Router $router)
    {
        $this->userModel = $userModel;
        $this->columnMappings = [
            'name' => 'Nom',
            'email' => 'Email',
            'role' => 'Role',
            'created_at' => 'Date de création',
            'actions' => 'Actions'
        ];
        parent::__construct($twig, $router);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return string
     */
    public function getUserTable(): string
    {
        $users = $this->userModel->getAllUsers();

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'email' => $user->getEmail(),
                'role' => $this->getRole($user),
                'created_at' => $user->getCreatedAt()->format('d/m/Y'),
                'actions' => $this->getActions($user)
            ];
        }

        return $this->renderTable($rows);
    }


    /**
     * @param User $user
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @return string
     */
    private function getActions(User $user): string
    {
        return $this->twig->render('tables/_actions.html.twig', [
            'edit' => $this->edit($user),
            'remove' => $this->remove($user),
            'show' => $this->show($user),
        ]);
    }

    private function edit(User $user): string
    {
        return sprintf('<a href="%s" class="btn btn-sm btn-warning rounded">%s</a>', $this->router->getRouteUrl('user_edit_form', ['id' => (string) $user->getId()]), 'Modifier');
    }

    private function remove(User $user): string
    {
        return sprintf('<a href="%s" class="btn btn-sm btn-danger rounded">%s</a>', $this->router->getRouteUrl('user_remove', ['id' => (string) $user->getId()]), 'Supprimer');
    }

    private function show(User $user): string
    {
        return sprintf('<a href="%s" class="btn btn-sm btn-primary rounded">%s</a>', $this->router->getRouteUrl('user_show', ['id' => (string) $user->getId()]), 'Voir');
    }

    private function getRole(User $user): string
    {
        $role = '';
        if ($user->getRole() === 'ROLE_ADMIN') {
            $role = 'Admin';
        } elseif ($user->getRole() === 'ROLE_USER') {
            $role = 'User';
        }
        return $role;
    }
}
