<?php

namespace App\Controllers;

use App\Manager\ServerManager;
use App\Models\User;
use App\core\RedirectResponse;
use App\Services\Sanitizer;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class HomeController extends AbstractController
{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): string
    {
        $content = 'index';

        return $this->twig->render('index.html.twig', [
            'title' => 'Home Page',
            'content' => $content,
        ]);
    }

    public function about(int $id): string
    {
        return 'This is the about page of ' . $id;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Exception
     * @return string|RedirectResponse
     */
    public function contact(): string|RedirectResponse
    {

    if ($this->isAdmin()) {
        $userModel = new User();
        
        $testuser = $userModel->findByUsermail("marie.curie@example.com");
    $users = $userModel->getAllUsers();

    return $this->twig->render('contact.html.twig', [
        'users' => $users,
        'test' => $testuser
    ]);
    } else {
        return $this->redirectToRoute('index');
    }

    }
}
