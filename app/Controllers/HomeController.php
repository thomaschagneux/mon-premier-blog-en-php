<?php

namespace App\Controllers;

use App\Manager\ServerManager;
use App\Models\User;
use App\core\RedirectResponse;
use App\Services\Sanitizer;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class HomeController extends AbstractController
{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    public function index(): string
    {
        $content = 'index';

        $successMessage = $this->cookieManager->getCookie('success_message');
        if ($successMessage) {
            $this->cookieManager->deleteCookie('success_message');
        }
        return $this->twig->render('index.html.twig', [
            'title' => 'Home Page',
            'content' => $content,
            'success_message' => $successMessage,
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
     * @throws Exception
     * @return string|RedirectResponse
     */
    public function contact(): string|RedirectResponse
    {
        return $this->twig->render('contact.html.twig');
    }
}
