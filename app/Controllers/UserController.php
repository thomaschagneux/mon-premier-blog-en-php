<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Picture;
use App\Models\User;
use App\Services\CustomTables\UserTableService;
use App\Services\Sanitizer;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserController extends AbstractController
{
    private User $user;
    private UserTableService $userTableService;

    public function __construct(Router $router)
    {
        parent::__construct($router);
        $this->user             = new User();
        $this->userTableService = new UserTableService($this->user, $this->twig, $router);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function adminListUser(): string|RedirectResponse
    {
        if ($this->isAdmin()) {
            $this->user = new User();
            $users      = $this->user->getAllUsers();
            $table      = $this->userTableService->getTableContent();

            $validateMessage = $this->cookieManager->getCookie('success_message');
            if (null !== $validateMessage) {
                $this->cookieManager->deleteCookie('success_message');
            }
            $errorMessage = $this->cookieManager->getCookie('error_message');
            if (null !== $errorMessage) {
                $this->cookieManager->deleteCookie('error_message');
            }

            return $this->render('user/list.html.twig', [
                'users'           => $users,
                'table'           => $table,
                'success_message' => $validateMessage,
                'error_message'   => $errorMessage,
            ]);
        }
        $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
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
        }
        return $this->redirectToRoute('register_form');

    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function registerForm(): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }

        if ($this->isConnected()) {
            return $this->redirectToRoute('admin_home');
        }
        return $this->render('user/registration.html.twig', ['message' => $message]);

    }

    /**
     * @throws Exception
     */
    public function register(): RedirectResponse
    {
        if ($this->isPostRequest()) {
            $email     = $this->postManager->getPostParam('email');
            $firstName = $this->postManager->getPostParam('first_name');
            $lastName  = $this->postManager->getPostParam('last_name');
            $password  = $this->postManager->getPostParam('password');

            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $this->cookieManager->setCookie('error_message', 'Tous les champs sont requis.', 60);
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
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $user->setPictureId(null);

            if ($this->fileManager->isPostFiles('avatar')) {
                $picture  = new Picture();

                $fileData = $this->fileManager->getFile('avatar');


                if (null !== $fileData) {
                    $extension      = pathinfo($fileData->name, PATHINFO_EXTENSION);
                    $uniqueFileName = 'avatar_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid() . '.' . $extension;
                    $uniqueFileName = Sanitizer::sanitizeString($uniqueFileName);

                    $picture->setFileName($uniqueFileName);
                    $picture->setPathName('assets/img/avatars/');
                    $picture->setMimeType($fileData->type);

                    $this->fileManager->setDestination($picture->getPathName());
                    $this->fileManager->moveFile($fileData->tmp_name, $picture->getFileName());
                    $picture->save();

                    $user->setPictureId($picture->getId());
                }
            }

            $user->save();
            $this->cookieManager->setCookie('success_message', 'Vous vous êtes bien inscrits', 60);
            return $this->redirectToRoute('index');
        }

        $this->cookieManager->setCookie('error_message', 'Il y a une erreur dans la soumission du formulaire, veuillez recommencer', 60);
        return $this->redirectToRoute('register_form');
    }

    /**
     * @throws Exception
     */
    public function addUser(): RedirectResponse
    {
        if ($this->isPostRequest()) {
            $firstName = $this->postManager->getPostParam('first_name');
            $lastName  = $this->postManager->getPostParam('last_name');
            $email     = $this->postManager->getPostParam('email');
            $password  = $this->postManager->getPostParam('password');
            $role      = $this->postManager->getPostParam('role') ?? 'ROLE_USER';

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
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $user->setRole($role);

            try {
                $picture  = new Picture();
                $fileData = $this->fileManager->getFile('avatar');

                if (null !== $fileData) {
                    $extension      = pathinfo($fileData->name, PATHINFO_EXTENSION);
                    $uniqueFileName = 'avatar_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid() . '.' . $extension;
                    $uniqueFileName = Sanitizer::sanitizeString($uniqueFileName);

                    $picture->setFileName($uniqueFileName);
                    $picture->setPathName('assets/img/avatars/');
                    $picture->setMimeType($fileData->type);

                    $this->fileManager->setDestination($picture->getPathName());
                    $this->fileManager->moveFile($fileData->tmp_name, $picture->getFileName());
                    $picture->save();

                    $user->setPictureId($picture->getId());
            }

            } catch(Exception $e) {
                $user->setPictureId(null);
            }

            $user->save();
            $this->cookieManager->setCookie('success_message', 'Cet utilisateur a bien été ajouté', 60);
            return $this->redirectToRoute('admin_list_user');
        }

        $this->cookieManager->setCookie('error_message', 'Il y a une erreur dans la soumission du formulaire, veuillez recommencer', 60);
        return $this->redirectToRoute('adminAddUserForm');
    }

    /**
     * @throws Exception
     */
    public function removeUser(int $id): RedirectResponse
    {
        $user = new User();
        $user->setId($id);
        $name = $user->getFirstName() . ' ' . $user->getLastName();

        if ($user->remove()) {
            $this->cookieManager->setCookie('success_message', 'Cet utilisateur a bien été supprimé', 60);
            return $this->redirectToRoute('admin_list_user');
        }
        $this->cookieManager->setCookie('error_message', 'Il y a eu un problème dans la suppression de l\'utilisateur ' . $name, 60);
        return $this->redirectToRoute('admin_list_user');
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function editUserForm(int $id): string|RedirectResponse
    {
        $errorMessage = $this->cookieManager->getCookie('error_message') ?? null;

        if ($errorMessage !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }

        if ($this->isAdmin()) {
            $user = $this->user->findById($id);
            if (null === $user) {
                return $this->redirectToRoute('admin_list_user');
            }

            return $this->render('user/edit.html.twig', [
                'user'          => $user,
                'error_message' => $errorMessage,
                ]);
        } elseif ($this->isConnected()) {
            $userData = $this->getUserData();
            if (is_array($userData) && isset($userData['email'])) {
                $user = $this->user->findByUsermail($userData['email']);
                if ($user instanceof User) {
                    return $this->render('user/edit.html.twig', [
                        'user'          => $user,
                        'error_message' => $errorMessage,
                    ]);
                }
            }
        }
        return $this->redirectToReferer();
    }

    /**
     * @throws Exception
     */
    public function editUser(int $id): RedirectResponse
    {
        if (!$this->isAdmin()) {
            return $this->redirectToRoute('admin_list_user');
        }

        $user = $this->user->findById($id);
        if (null === $user) {
            return $this->redirectToRoute('admin_list_user');
        }

        if (!$this->isPostRequest()) {
            return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
        }

        $params = $this->getUserInput();
        if ($this->hasMissingFields($params)) {
            $this->cookieManager->setCookie('error_message', 'Veuillez remplir les champs requis', 60);
            return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
        }

        $params['password'] = $this->getPasswordOrDefault($params['password'], $user);

        $this->updateUserFields($user, $params);
        if (!$this->handleAvatarUpload($user)) {
            return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
        }

        $user->save();
        $this->cookieManager->setCookie('success_message', 'Cet utilisateur a bien été modifié', 60);
        return $this->redirectToRoute('admin_list_user');
    }

    /**
     * @return array<string, string>
     */
    private function getUserInput(): array
    {
        return [
            'first_name' => $this->postManager->getPostParam('first_name') ?? '',
            'last_name'  => $this->postManager->getPostParam('last_name')  ?? '',
            'email'      => $this->postManager->getPostParam('email')      ?? '',
            'password'   => $this->postManager->getPostParam('password')   ?? '',
            'role'       => $this->postManager->getPostParam('role')       ?? 'ROLE_USER',
        ];
    }

    /**
     * @param  array<string, string|null> $params
     * @return bool
     */
    private function hasMissingFields(array $params): bool
    {
        return empty($params['first_name']) || empty($params['last_name']) || empty($params['email']);
    }

    private function getPasswordOrDefault(?string $password, User $user): string
    {
        return empty($password) ? $user->getPassword() : password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param  User                  $user
     * @param  array<string, string> $params
     * @return void
     */
    private function updateUserFields(User $user, array $params): void
    {
        $user->setFirstName($params['first_name']);
        $user->setLastName($params['last_name']);
        $user->setEmail($params['email']);
        $user->setPassword($params['password']);
        $user->setRole($params['role']);
    }

    private function handleAvatarUpload(User $user): bool
    {
        $file = $this->fileManager->sanitizedFiles('avatar');

        if ($file['error'] === UPLOAD_ERR_OK) {
            return $this->processAvatar($file, $user);
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (false === is_int($file['error'])) {
            throw new Exception('File error must be int');
        }
        return $this->handleFileUploadError($file['error']);
    }

    /**
     * @param  array<string, string|int> $file
     * @param  User                      $user
     * @return bool
     * @throws Exception
     */
    private function processAvatar(array $file, User $user): bool
    {
        $fileData = $this->fileManager->getFile('avatar');
        if ($fileData === null) {
            return false;
        }

        $extension      = pathinfo($fileData->name, PATHINFO_EXTENSION);
        $uniqueFileName = Sanitizer::sanitizeString(
            'avatar_' . $user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid() . '.' . $extension
        );

        $picture = new Picture();
        $picture->setFileName($uniqueFileName);
        $picture->setPathName('assets/img/avatars/');
        $picture->setMimeType($fileData->type);

        $this->fileManager->setDestination($picture->getPathName());
        $this->fileManager->moveFile($fileData->tmp_name, $picture->getFileName());
        $picture->save();

        $user->setPictureId($picture->getId());
        return true;
    }

    private function handleFileUploadError(int $errorCode): bool
    {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE  => 'Le fichier dépasse la taille maximale autorisée',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée',
        ];

        $message = $errorMessages[$errorCode] ?? 'Erreur inconnue dans le chargement du fichier';
        $this->cookieManager->setCookie('error_message', $message, 60);
        return false;
    }



    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    public function adminUserShow(int $id): string|RedirectResponse
    {
        if ($this->isAdmin()) {
            $user = $this->user->findById($id);
            if (null === $user) {
                return $this->redirectToRoute('error_500');
            }
            $picture = null;
            if (null !== $user->getPictureId()) {
                $pictureModel = new Picture();
                $picture      = $pictureModel->findById($user->getPictureId());
            }
            return $this->render('user/show.html.twig', ['user' => $user, 'picture' => $picture]);
        }
        return $this->redirectToReferer();
    }

}
