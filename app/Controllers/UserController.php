<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Picture;
use App\Models\User;
use App\Services\CustomTables\UserTableService;
use App\Services\Form\UserEditForm;
use App\Services\HelperServices;
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
        $this->user = new User();
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
            $users = $this->user->getAllUsers();
            $table = $this->userTableService->getTableContent();

            $validateMessage = $this->cookieManager->getCookie('success_message');
            if (null !== $validateMessage) {
                $this->cookieManager->deleteCookie('success_message');
            }
            $errorMessage = $this->cookieManager->getCookie('error_message');
            if (null !== $errorMessage) {
                $this->cookieManager->deleteCookie('error_message');
            }

            return $this->render('user/list.html.twig', [
                'users' => $users,
                'table' => $table,
                'success_message' => $validateMessage,
                'error_message' => $errorMessage
            ]);
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
            $email = $this->postManager->getPostParam('email');
            $firstName = $this->postManager->getPostParam('first_name');
            $lastName = $this->postManager->getPostParam('last_name');
            $password = $this->postManager->getPostParam('password');

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
            } else{
                $user->setPictureId(null);
            }

            $user->save();
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
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
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
            } else {
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
            $editForm = new UserEditForm($user, $this->generateUrl('edit_user_action', ['id' => (string) $user->getId()]));

            dd($editForm);
            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'error_message' => $errorMessage,
                'edit_form' => $editForm]);
        } else {
            return $this->redirectToReferer();
        }
    }

    /**
     * @throws Exception
     */
    public function editUser(int $id): RedirectResponse
    {

        $user = $this->user->findById($id);
        if (null === $user) {
            return $this->redirectToRoute('admin_list_user');
        }
        if ($this->isPostRequest()) {
            $firstName = $this->postManager->getPostParam('first_name');
            $lastName = $this->postManager->getPostParam('last_name');
            $email = $this->postManager->getPostParam('email');
            $password = $this->postManager->getPostParam('password');
            $role = $this->postManager->getPostParam('role') ?? 'ROLE_USER';
            dd($this->postManager->getPostParam('contenu'));
            if (empty($password)) {
                $password = $user->getPassword();
            }

            if (empty($firstName) || empty($lastName) || empty($email)) {
                $this->cookieManager->setCookie('error_message', 'Veuillez remplir les champs requis', 60);
                return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
            }
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $user->setRole($role);

            $picture = new Picture();

            $file = $this->fileManager->sanitizedFiles('avatar');

            if($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                $this->cookieManager->setCookie('error_message', 'Le fichier dépasse la taille maximale autorisée', 60);
                return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
            } elseif ($file['error'] === UPLOAD_ERR_NO_FILE) {
                $this->cookieManager->setCookie('error_message', 'Fichier inexistant', 60);
                return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $this->cookieManager->setCookie('error_message', 'Erreur inconnue dans le chargement du fichier', 60);
                return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
            }


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
            } elseif(null !== $user->getPictureId()) {
                $user->setPictureId($user->getPictureId());
            }else {
                $this->cookieManager->setCookie('error_message', 'Erreur dans le chargement de cette image', 60);
                return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
            }
            $user->save();
            $this->cookieManager->setCookie('success_message', 'Cet utilisateur a bien été modifié', 60);
            return $this->redirectToRoute('admin_list_user');
        }
        return $this->redirectToRoute('user_edit_form', ['id' => (string) $id]);
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
                $picture = $pictureModel->findById($user->getPictureId());
            }
            return $this->render('user/show.html.twig', ['user' => $user, 'picture' => $picture]);
        }
        return $this->redirectToReferer();
    }

}