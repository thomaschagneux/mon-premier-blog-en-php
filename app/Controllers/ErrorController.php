<?php

namespace App\Controllers;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ErrorController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function error404(string $error): string
    {
        $params = ['error' => $error];
        return $this->render('errors/404.html.twig', $params);
    }
}