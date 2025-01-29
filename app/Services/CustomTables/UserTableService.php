<?php

namespace App\Services\CustomTables;

use App\core\Router;
use App\Models\User;
use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UserTableService
 * Manages the rendering of a table for users, including actions and custom column classes.
 */
class UserTableService extends AbstractTableService
{
    /**
     * @var User The User model instance.
     */
    private User $userModel;

    /**
     * UserTableService constructor.
     * Initializes the service and sets up column mappings.
     *
     * @param User        $userModel The User model.
     * @param Environment $twig      The Twig environment.
     * @param Router      $router    The router instance.
     */
    public function __construct(User $userModel, Environment $twig, Router $router)
    {
        $this->userModel      = $userModel;
        $this->columnMappings = [
            'name'       => 'Nom',
            'email'      => 'Email',
            'role'       => 'Role',
            'created_at' => 'Date de création',
            'actions'    => 'Actions',
        ];
        parent::__construct($twig, $router);
    }

    /**
     * Generates the table content for all users.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     *
     * @return string The rendered table HTML.
     */
    public function getTableContent(): string
    {
        $users = $this->userModel->getAllUsers();

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                'name'       => $user->getFirstName() . ' ' . $user->getLastName(),
                'email'      => $user->getEmail(),
                'role'       => $this->getRole($user),
                'created_at' => $user->getCreatedAt()->format('d/m/Y'),
                'actions'    => $this->getActions($user),
            ];
        }

        return $this->renderTable($rows);
    }

    /**
     * Returns a custom CSS class for a given column key.
     *
     * @param string $key The column key.
     *
     * @return string The CSS class for the column.
     */
    protected function getColumnClass(string $key): string
    {
        $customClasses = [
            'name'       => 'column-name',
            'email'      => 'column-email',
            'role'       => 'column-role',
            'created_at' => 'column-created',
            'actions'    => 'column-actions',
        ];

        return $customClasses[$key] ?? parent::getColumnClass($key);
    }

    /**
     * Generates the action buttons for a user.
     *
     * @param User $user The user to generate actions for.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string The rendered action buttons HTML.
     */
    private function getActions(User $user): string
    {
        return $this->twig->render('tables/_actions.html.twig', [
            'edit'   => $this->edit($user),
            'remove' => $this->remove($user),
            'show'   => $this->show($user),
        ]);
    }

    /**
     * Generates the "Edit" action link for a user.
     *
     * @param User $user The user to edit.
     *
     * @return string The "Edit" action link HTML.
     */
    private function edit(User $user): string
    {
        return sprintf(
            '<a href="%s" class="btn btn-sm btn-warning rounded">%s</a>',
            $this->router->getRouteUrl('user_edit_form', ['id' => (string) $user->getId()]),
            'Modifier'
        );
    }

    /**
     * Generates the "Remove" action link for a user.
     *
     * @param User $user The user to remove.
     *
     * @return string The "Remove" action link HTML.
     */
    private function remove(User $user): string
    {
        return sprintf(
            '<a href="%s" class="btn btn-sm btn-danger rounded">%s</a>',
            $this->router->getRouteUrl('user_remove', ['id' => (string) $user->getId()]),
            'Supprimer'
        );
    }

    /**
     * Generates the "Show" action link for a user.
     *
     * @param User $user The user to show.
     *
     * @return string The "Show" action link HTML.
     */
    private function show(User $user): string
    {
        return sprintf(
            '<a href="%s" class="btn btn-sm btn-primary rounded">%s</a>',
            $this->router->getRouteUrl('user_show', ['id' => (string) $user->getId()]),
            'Voir'
        );
    }

    /**
     * Retrieves the role of the user in a readable format.
     *
     * @param User $user The user to retrieve the role for.
     *
     * @return string The formatted role (e.g., "Admin" or "User").
     */
    private function getRole(User $user): string
    {
        if ($user->getRole() === 'ROLE_ADMIN') {
            return 'Admin';
        } elseif ($user->getRole() === 'ROLE_USER') {
            return 'User';
        }
        return '';
    }
}
