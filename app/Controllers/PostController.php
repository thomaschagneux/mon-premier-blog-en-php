<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Post;
use App\Models\User;
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
            $postAddFormService = new PostAddFormService($this->twig);

            $postAddFormService->buildForm();
            // Get the form rows from the service
            $formRows = $postAddFormService->getFormRows();
            return $this->render('post/add.html.twig', ['form_rows' => $formRows,]);
        } else {
            return $this->redirectToReferer();
        }
    }

    public function addPostAction(): string|RedirectResponse
    {
        $title =  $this->postManager->getPostParam('title');
        $lede =  $this->postManager->getPostParam('lede');
        $content = $this->postManager->getPostParam('content');

        $postModel = new Post();
        $postModel->setContent($content);
        $postModel->setTitle($title);
        $postModel->setLede($lede);
        $postModel->setUserId(1);
        $postModel->setCreatedAt(new \DateTime());
        $postModel->save();
        return $this->redirectToRoute('list_post');
    }
}