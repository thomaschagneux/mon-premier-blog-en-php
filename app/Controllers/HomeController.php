<?php

namespace App\Controllers;

use App\core\Router;
use App\Models\Post;
use App\core\RedirectResponse;
use App\Services\Form\ContactFormService;
use Dotenv\Dotenv;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController extends AbstractController
{
    public function __construct(Router $router)
    {
        parent::__construct($router);
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

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
        $errorMessage = $this->cookieManager->getCookie('error_message');
        if ($errorMessage) {
            $this->cookieManager->deleteCookie('error_message');
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
            'error_message' => $errorMessage,
            'posts' => $posts,
            'last_post' => $lastPost,
            'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
        ]);
    }

    public function contactSubmit()
    {
        $recaptchaResponse = $this->postManager->getPostParam('g-recaptcha-response') ?? '';

        $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'];

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
        $responseKeys = json_decode($response, true);

        if ($responseKeys['success'] ?? false) {

            $this->cookieManager->setCookie('success_message', 'Formulaire envoyé avec succès', 60);
            return $this->redirectToRoute('index');
        } else {

            $this->cookieManager->setCookie('error_message', 'Le reCAPTCHA a échoué, veuillez réessayer', 60);
            return $this->redirectToRoute('index');
        }
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
