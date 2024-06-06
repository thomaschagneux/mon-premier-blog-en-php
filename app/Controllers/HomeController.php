<?php

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        include __DIR__ . '/../Views/index.php';
    }

    public function about($id)
    {
        echo 'This is the about page of ' . $id;
    }

    public function contact()
    {
        echo 'This is the contact page.';
    }
}
