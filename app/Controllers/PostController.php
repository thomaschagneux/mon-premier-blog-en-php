<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Post;
use App\Services\CustomTables\PostTableService;
use App\Services\Form\PostAddFormService;
use App\Services\HelperServices;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PostController extends AbstractController
{
    private  Post $post;

    private  PostTableService $postTableService;
    public function __construct(Router $router)
    {
        parent::__construct($router);
        $this->post = new Post();
        $this->postTableService = new PostTableService($this->post, $this->twig, $this->router);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws \Exception
     */
    public function postList(): string|RedirectResponse
    {
        if ($this->isAdmin()) {
            $posts = $this->post->getAllPosts();
            $table = $this->postTableService->getTableContent();

            return $this->render('post/list.html.twig', ['posts' => $posts, 'table' => $table]);
        }
        return $this->redirectToReferer();
    }

    public function addPostForm(): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }

        if ($this->isConnected()) {
            $form = new PostAddFormService($this->generateUrl('add_post_form'));
            $formView = $form->render($this->twig);
            return $this->render('post/add.html.twig', ['form' => $formView]);
        } else {
            return $this->redirectToReferer();
        }
    }
}