<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\Form\CommentAddFormService;

class CommentController extends AbstractController
{
    private Comment $commentModel;

    public function __construct(
        Router $router,
    )
    {
        parent::__construct($router);
        $this->commentModel = new Comment();
    }

    public function commentShow(int $commentId): string|RedirectResponse
    {
        if ($this->isAdmin()) {
           $comment = $this->commentModel->findById($commentId);
           return $this->render('post/comment/show.html.twig', [
               'comment' => $comment
           ]);
        } else {
            return $this->redirectToReferer();
        }
    }

    public function commentAddForm(int $postId): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }

        if ($this->isConnected()) {
            $postModel = new Post();
            $post = $postModel->findById($postId);
            $commentAddForm = new CommentAddFormService($this->twig);
            $commentAddForm->buildForm();

            return $this->render('post/comment/add.html.twig', [
                'post' => $post,
                'comment_add_form' => $commentAddForm->getFormRows(),
                'error_message' => $message,
            ]);
        }

        return $this->redirectToReferer();
    }

    public function CommentAddAction(int $postId):RedirectResponse
    {

        $content = $this->postManager->getPostParam('content');

        if (null === $content) {
            return $this->redirectToRoute('comment_add_form', ['id' => (string) $postId]);
        }

        $userModel = new User();
        $userData = $this->getUserData();

        if (!is_array($userData) || !isset($userData['email']) || !is_string($userData['email'])) {
            $this->cookieManager->setCookie('error_message', 'Il y a eu une erreur, veuillez recommencer');
            return $this->redirectToRoute('comment_add_form', ['id' => (string) $postId]);
        }

        $user = $userModel->findByUsermail($userData['email']);

        if (!$user instanceof User) {
            $this->cookieManager->setCookie('error_message', 'Il y a eu une erreur, veuillez recommencer');
            return $this->redirectToRoute('comment_add_form', ['id' => (string) $postId]);
        }

        $commentModel = new Comment();
        $commentModel->setPostId($postId);
        $commentModel->setUserId($user->getId());
        $commentModel->setContent($content);
        $commentModel->setCreatedAt(new \DateTime());

        $commentModel->save();

        $this->cookieManager->setCookie('success_message', 'Le commentaire a bien été enregistré', 60);
        return $this->redirectToRoute('post_show', ['id' => (string) $postId]);
    }

    public function commentRemove(int $commentId): void
    {

    }

    public function commentEditForm(int $commentId):void
    {

    }

    public function commentEditAction(int $commentId):void
    {

    }
}