<?php

namespace App\Controllers;

use App\Manager\ServerManager;
use App\Models\Post;
use App\Models\User;
use App\core\RedirectResponse;
use App\Services\Form\ContactFormService;
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

        $successMessage = $this->cookieManager->getCookie('success_message');
        if ($successMessage) {
            $this->cookieManager->deleteCookie('success_message');
        }

        $contactForm = new ContactFormService($this->twig);

        $contactForm->buildForm();
        $contactFormRows = $contactForm->getFormRows();

        $postModel = new Post();
        $posts = array_reverse($postModel->getAllPosts());

        $lastPost = $posts[0] ?? null;

        return $this->twig->render('index.html.twig', [
            'title' => 'Home Page',
            'contact_form' => $contactFormRows,
            'success_message' => $successMessage,
            'posts' => $posts,
            'last_post' => $lastPost
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
