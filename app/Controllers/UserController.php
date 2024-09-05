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

    /**
     * @throws Exception
     */
    public function adminAddUserForm(): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }
        if ($this->isAdmin()) {
            return $this->render('user/admin_add.html.twig', ['message' => $message]);
        } elseif ($this->isConnected()) {
            return $this->redirectToReferer();
        } else {
           return $this->redirectToRoute('register_form');
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function registerForm(): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }
        if ($this->isConnected()) {
            return $this->redirectToRoute('admin_home');
        } else {
            return $this->render('user/registration.html.twig', ['message' => $message]);
        }
    }

    /**
     * @throws Exception
     */
    public function register(): RedirectResponse
    {

        if ($this->isPostRequest()) {
            $firstName = $this->postManager->getPostParam('first_name');
            $lastName = $this->postManager->getPostParam('last_name');
            $email = $this->postManager->getPostParam('email');
            $password = $this->postManager->getPostParam('password');

            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $this->cookieManager->setCookie('error_message', 'Veuillez remplir les champs requis', 60);

                return $this->redirectToRoute('register_form');
            }

            $user = new User();
            if ($user->emailExists($email)) {
                $this->cookieManager->setCookie('error_message', 'Cet email existe déjà', 60);

                return $this->redirectToRoute('register_form');
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setPassword(password_hash($password,PASSWORD_DEFAULT));

            $picture = new Picture();

            $fileData = $this->fileManager->getFile('avatar');
            if (null !== $fileData) {

                $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);

                $uniqueFileName = 'avatar_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid() . '.' . $extension;

                $uniqueFileName = Sanitizer::sanitizeString($uniqueFileName);

                $picture->setFileName($uniqueFileName);
                $picture->setPathName('assets/img/avatars/');
                $picture->setMimeType($fileData['type']);

                $this->fileManager->setDestination($picture->getPathName());

                $this->fileManager->moveFile($fileData['tmp_name'], $picture->getFileName());

                $picture->save();

                $user->setPictureId($picture->getId());
            }

            $user->save();
            return $this->redirectToRoute('index');

        }
        $this->cookieManager->setCookie('error_message',
            'Il y a une erreur dans la soumission du formulaire, veuillez recommencer', 60);
        return $this->redirectToRoute('register_form');
    }


    /**
     * @throws Exception
     */
    public function addUser(): RedirectResponse
    {
        if ($this->isPostRequest()) {
            $firstName = $this->postManager->getPostParam('first_name');
            $lastName = $this->postManager->getPostParam('last_name');
            $email = $this->postManager->getPostParam('email');
            $password = $this->postManager->getPostParam('password');
            $role = $this->postManager->getPostParam('role') ?? 'ROLE_USER';

            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $this->cookieManager->setCookie('error_message', 'Veuillez remplir les champs requis', 60);

                return $this->redirectToRoute('admin_add_user_form');
            }

            $user = new User();
            if ($user->emailExists($email)) {
                $this->cookieManager->setCookie('error_message', 'Cet email existe déjà', 60);

                return $this->redirectToRoute('admin_add_user_form');
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setPassword(password_hash($password,PASSWORD_DEFAULT));
            $user->setRole($role);

            $picture = new Picture();

            $fileData = $this->fileManager->getFile('avatar');
            if (null !== $fileData) {

                $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);

                $uniqueFileName = 'avatar_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid() . '.' . $extension;

                $uniqueFileName = Sanitizer::sanitizeString($uniqueFileName);

                $picture->setFileName($uniqueFileName);
                $picture->setPathName('assets/img/avatars/');
                $picture->setMimeType($fileData['type']);

                $this->fileManager->setDestination($picture->getPathName());

                $this->fileManager->moveFile($fileData['tmp_name'], $picture->getFileName());

                $picture->save();

                $user->setPictureId($picture->getId());
            }
            $user->save();

            return $this->redirectToRoute('admin_list_user');

        }
        $this->cookieManager->setCookie('error_message',
        'Il y a une erreur dans la soumission du formulaire, veuillez recommencer', 60);
       return $this->redirectToRoute('adminAddUserForm');
    }
}