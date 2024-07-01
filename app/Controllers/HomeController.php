<?php

namespace App\Controllers;

use App\Models\User;

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

    public function contact(): string
    {
        $userModel = new User;
       $users = $userModel->getAllUsers();
       
       return $this->twig->render('contact.html.twig', [
        'users' => $users
       ]);
            
    }
}
