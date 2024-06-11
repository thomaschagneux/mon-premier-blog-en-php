<?php

namespace App\Controllers;



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
        echo "<pre>";
	print_r($_SERVER);
	echo "</pre>";
	exit;
    }
}
