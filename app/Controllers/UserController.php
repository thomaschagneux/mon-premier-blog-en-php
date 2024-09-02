<?php

namespace App\Controllers;


use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     *
     * @return string
     */
    public function adminListUser(): string
    {

        return $this->render('user/list.html.twig');
    }
}