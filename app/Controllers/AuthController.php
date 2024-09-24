<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\Models\User;
use Exception;
use Random\RandomException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AuthController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws RandomException
     * @throws Exception
     *
     * @return string|RedirectResponse
     */
    public function loginForm(): string|RedirectResponse
    {
        if ($this->isConnected()) {
            return $this->redirectToRoute('index');
        }

        $csrfToken = bin2hex(random_bytes(32));


        $errorMessage = $this->cookieManager->getCookie('error_message');
        if ($errorMessage) {
            $this->cookieManager->deleteCookie('error_message');
        }

        return $this->render('login/login.html.twig', [
            'csrf_token' => $csrfToken,
            'error_message' => $errorMessage
        ]);
    }

    /**
     * @return string|RedirectResponse
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function login(): string|RedirectResponse
    {
        if ($this->isPostRequest()) {
            [$email, $password] = $this->getPostCredentials();

            if (null === $email || '' === $email || null === $password || '' === $password) {
                $this->cookieManager->setCookie('error_message', "Email ou mot de passe non renseigné.", 60);
                return $this->redirectToRoute('login_form');
            } elseif ($this->authenticateUser($email, $password)) {
                $this->cookieManager->setCookie('success_message', 'Vous vous êtes bien connecté', 60);
                return $this->redirectToRoute('index');
            } else {
                $this->cookieManager->setCookie('error_message', "Identifiants invalides", 60);
                return $this->redirectToRoute('login_form'); // Redirection pour recharger les cookies
            }
        }

        return $this->loginForm();
    }

    /**
     * @return array<int, null|string>
     */
    private function getPostCredentials(): array
    {
        $email = $this->postManager->getPostParam('loginEmail');
        $password = $this->postManager->getPostParam('loginPassword');
        return [$email, $password];
    }

    /**
     * @throws Exception
     */
    private function authenticateUser(string $email, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findByUsermail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            $this->initializeUserSession($user);
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function initializeUserSession(User $user): void
    {
        $userData = json_encode([
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'password' => $user->getPassword(),
            'picture_id' => $user->getpictureId(),
            'created_at' => $user->getCreatedAt()->format('d/m/y'),
            'updated_at' => $user->getUpdatedAt()?->format('d/m/y'),
        ]);
        if (null == $userData) {
            throw new Exception("Erreur lors de l'enregistrement de l'utilisateur");
        }

        $this->cookieManager->setCookie('user_data', $userData, time() + (60 * 60 * 24)); // temps en secondes
    }

    /**
     * @throws Exception
     */
    public function logout(): RedirectResponse
    {
        $this->cookieManager->deleteCookie('user_data');
        $this->cookieManager->setCookie('success_message', "Vous vous êtes bien déconnecté", 60);
        return $this->redirectToRoute('index');
    }
}

