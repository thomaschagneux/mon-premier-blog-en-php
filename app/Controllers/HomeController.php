<?php

namespace App\Controllers;

use App\Models\User;
use App\core\RedirectResponse;


class HomeController extends AbstractController
{
    public function index(): string
    {
        return $this->twig->render('index.html.twig', [
            'title' => 'Home Page',
            'content' => 'This is the content of the home page.',
        ]);
    }

    public function about(int $id): string
    {
        return 'This is the about page of ' . $id;
    }

    /**
     * @return RedirectResponse|string
     */
    public function contact() {
    if ($this->isAdmin()) {
        $userModel = new User();
        
        $testuser = $userModel->findByUsermail("marie.curie@example.com");
    $users = $userModel->getAllUsers();
   
    return $this->twig->render('contact.html.twig', [
        'users' => $users,
        'test' => $testuser
    ]);
    }else {
        return $this->redirectToRoute('index');
    }
        
    }
}
