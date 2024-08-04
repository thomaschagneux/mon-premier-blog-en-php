<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\SessionManager;
use App\Models\User;
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
     *
     * @return string|RedirectResponse
     */
    public function loginForm(): string|RedirectResponse
    {
        if ($this->isConnected()) {
            return $this->redirectToRoute('index');
        }
        $csrfToken = bin2hex(random_bytes(32));
        $this->session->put('csrf_token', $csrfToken);
        return $this->twig->render('login/login.html.twig', ['csrf_token' => $csrfToken]);
    }

    /**
     * @return string|RedirectResponse
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Exception
     */
    public function login(): string|RedirectResponse
    {
        if ($this->isPostRequest()) {

            if (!$this->isValidCsrfToken()) {
                $error = "Jeton CSRF invalide.";
                return $this->twig->render('login/login.html.twig', ['error' => $error]);
            }

            [$email, $password] = $this->getPostCredentials();

            if (null === $email || '' === $email || null === $password || '' === $password) {
                $error = "Email ou mot de passe non renseigné.";
                return $this->renderError($error);
            } elseif ($this->authenticateUser($email, $password)) {
                return $this->redirectToRoute('contact');
            } else {
               $error = "Identifiants invalides";
                return $this->renderError($error);
            }
        }
    
        // Redirection vers la méthode showLoginForm en cas de requête GET
        return $this->loginForm();
    }

    private function isValidCsrfToken(): bool
    {
        $csrfToken = $this->postManager->getPostParam('csrf_token');
        return $this->session->has('csrf_token') && $csrfToken === $this->session->get('csrf_token');
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
     * @throws \Exception
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

    private function initializeUserSession(User $user): void
    {
        $this->session->put('user', [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'password' => $user->getPassword(),
            'picture_id' => $user->getpictureId(),
            'created_at' => $user->getCreatedAt()->format('d/m/y'),
            'updated_at' => $user->getUpdatedAt()->format('d/m/y'),
        ]);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    private function renderError(string $error): string
    {
        return $this->twig->render('login/login.html.twig', ['error' => $error]);
    }


    /**
     * @throws \Exception
     */
    public function logout(): RedirectResponse {
        $this->session->destroy();
        return $this->redirectToRoute('index');
    }
}

