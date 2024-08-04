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
     */
    public function loginForm(): string
    {
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
            $csrfToken = $this->postManager->getPostParam('csrf_token');
            if (!$this->session->has('csrf_token') || $csrfToken !== $this->session->get('csrf_token')) {
                $error = "Jeton CSRF invalide.";
                return $this->twig->render('login/login.html.twig', ['error' => $error]);
            }

            $email = $this->postManager->getPostParam('loginEmail');
            $password = $this->postManager->getPostParam('loginPassword');



            if (null === $email || '' === $email ||null === $password || '' === $password) {
                // Gérer le cas où les données POST ne sont pas présentes
                $error = "Email ou mot de passe non renseigné.";
                return $this->twig->render('login/login.html.twig', ['error' => $error]);
            }


            $userModel = new User();
            $user = $userModel->findByUsermail($email);
    
            
            if ($user && password_verify($password, $user->getPassword())) {                

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

           return $this->redirectToRoute('contact');
            } else {

                $error = "Identifiants invalides";
                return $this->twig->render('login/login.html.twig', ['error' => $error]);
            }
        }
    
        // Redirection vers la méthode showLoginForm en cas de requête GET
        return $this->loginForm();
    }


    /**
     * @throws \Exception
     */
    public function logout(): RedirectResponse {
        $this->session->destroy();
        return $this->redirectToRoute('index');
    }
}

