<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\Models\User;

class AuthController extends AbstractController
{
    public function loginForm(): string
    {
       return $this->twig->render('login/login.html.twig');
    }

    /**
     * @return string|RedirectResponse
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['loginEmail'] ?? null;
            $password = $_POST['loginPassword'] ?? null;
    
            if ($email === null || $password === null) {
                // Gérer le cas où les données POST ne sont pas présentes
                $error = "Email or password not provided.";
                return $this->twig->render('login/login.html.twig', ['error' => $error]);
            }
    
            $userModel = new User();
            $user = $userModel->findByUsermail($email);
    
            
            if ($user && password_verify($password, $user->getPassword())) {
                // Démarrer la session et stocker les informations utilisateur
                session_start();

                

                $_SESSION['user'] = [
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'password' => $user->getPassword(),
                    'picture_id' => $user->getpictureId(),   
                    'created_at' => $user->getCreatedAt()->format('d/m/y'),
                    'updated_at' => $user->getUpdatedAt()->format('d/m/y'),                 
                ];

           return $this->redirectToRoute('contact');
            } else {
                
                $error = "Identifiants invalides";
                return $this->twig->render('login/login.html.twig', ['error' => $error]);
            }
        }
    
        // Redirection vers la méthode showLoginForm en cas de requête GET
        return $this->loginForm();
    }
    

    public function logout(): RedirectResponse {
        $this->session->destroySession();
        return $this->redirectToRoute('index');
    }
}

