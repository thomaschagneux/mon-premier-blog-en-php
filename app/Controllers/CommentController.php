<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\Form\CommentAddFormService;
use App\Services\Form\CommentEditFormService;

class CommentController extends AbstractController
{
    private Comment $commentModel;

    public function __construct(
        Router $router,
    ) {
        parent::__construct($router);
        $this->commentModel = new Comment();
    }

    public function commentShow(int $commentId): string|RedirectResponse
    {
        $comment = null;
        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }
        if (!$this->isAdmin()) {
            $user = (new User())->findByUsermail($this->getUserData()['email']);
            if (!$user instanceof User) {
                $this->cookieManager->setCookie('error_message', 'Utilisateur non trouvé', 60);
                return $this->redirectToReferer();
            }
            $comment = $this->commentModel->findById($commentId);
            if (!$comment instanceof Comment) {
                $this->cookieManager->setCookie('error_message', 'Commentaire non trouvé', 60);
                return $this->redirectToReferer();
            }
            if ($user->getId() !== $comment->getUserId()) {
                $this->cookieManager->setCookie('error_message', 'Commentaire non trouvé', 60);
                return $this->redirectToReferer();
            }

        }
        return $this->render('post/comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    public function commentAddForm(int $postId): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }

        if ($this->isConnected()) {
            $postModel      = new Post();
            $post           = $postModel->findById($postId);
            $commentAddForm = new CommentAddFormService($this->twig);
            $commentAddForm->buildForm();

            return $this->render('post/comment/add.html.twig', [
                'post'             => $post,
                'comment_add_form' => $commentAddForm->getFormRows(),
                'error_message'    => $message,
            ]);
        }

        return $this->redirectToReferer();
    }

    public function CommentAddAction(int $postId): RedirectResponse
    {
        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }

        $content = $this->postManager->getPostParam('content');

        if (null === $content) {
            return $this->redirectToRoute('comment_add_form', ['id' => (string) $postId]);
        }

        $userModel = new User();
        $userData  = $this->getUserData();

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

    public function commentRemove(int $commentId): RedirectResponse
    {
        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }

        $commentModel = new Comment();
        $comment      = $commentModel->findById($commentId);

        if (!$this->isAdmin()) {
            $user = (new User())->findByUsermail($this->getUserData()['email']);
            if (!$user instanceof User) {
                $this->cookieManager->setCookie('error_message', 'Utilisateur non trouvé', 60);
                return $this->redirectToReferer();
            }
            if ($user->getId() !== $comment->getUserId()) {
            }
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à ce commentaire', 60);
            return $this->redirectToReferer();
        }


        if ($comment instanceof Comment) {
            $postId = $comment->getPostId();

            if ($comment->remove()) {
                $this->cookieManager->setCookie('success_message', 'Cet commentaire a bien été supprimé', 60);
                return $this->redirectToRoute('post_show', ['id' => (string) $postId]);
            }
        }
        $this->cookieManager->setCookie('error_message', 'Il y a eu un problème dans la suppression de ce message', 60);

        return $this->redirectToReferer();
    }

    public function commentEditForm(int $commentId): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }



        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }

        $comment = (new Comment())->findById($commentId);

        if (!$comment instanceof Comment) {
            $this->cookieManager->setCookie('error_message', 'Commentaire non trouvé', 60);
            return $this->redirectToReferer();
        }

        if (!$this->isAdmin()) {
            $user = (new User())->findByUsermail($this->getUserData()['email']);
            if (!$user instanceof User) {
                $this->cookieManager->setCookie('error_message', 'Utilisateur non trouvé', 60);
                return $this->redirectToReferer();
            }

            if ($user->getId() !== $comment->getUserId()) {
                $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
                return $this->redirectToReferer();
            }
        }
        $commentEditForm = null;
        $post            = null;

        if ($comment instanceof Comment) {
            $commentEditForm = new CommentEditFormService($this->twig, $comment);
            $commentEditForm->buildForm();

            $postModel = new Post();
            $post      = $comment->getPostId() ? $postModel->findById($comment->getPostId()) : null;

        }


        return $this->render('post/comment/edit.html.twig', [
            'post'              => $post instanceof Post ? $post : null,
            'comment_edit_form' => $commentEditForm?->getFormRows(),
            'error_message'     => $message,
        ]);
    }

    public function commentEditAction(int $commentId): RedirectResponse
    {
        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }

        $comment      = (new Comment())->findById($commentId);

        if (!$comment instanceof Comment) {
            $this->cookieManager->setCookie('error_message', 'Commentaire non trouvé', 60);
            return $this->redirectToReferer();
        }

        if (!$this->isAdmin()) {
            $user = (new User())->findByUsermail($this->getUserData()['email']);
            if (!$user instanceof User) {
                $this->cookieManager->setCookie('error_message', 'Utilisateur non trouvé', 60);
                return $this->redirectToReferer();
            }

            if ($user->getId() !== $comment->getUserId()) {
                $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à ce commentaire', 60);
                return $this->redirectToReferer();
            }

        }
        $content = $this->postManager->getPostParam('content');


        if (empty($content)) {
            $this->cookieManager->setCookie('error_message', 'Le commentaire ne peux pas être vide', 60);
            return $this->redirectToReferer();
        }

        $comment->setContent($content);
        $comment->setUpdatedAt(new \DateTime());
        $comment->save();

        $postModel = new Post();
        $post      = $comment->getPostId() ? $postModel->findById($comment->getPostId()) : null;

        $this->cookieManager->setCookie('success_message', 'Le commentaire a bien été enregistré', 60);
        return $this->redirectToRoute('post_show', ['id' => (string) $post->getId()]);

    }

    /**
     * @throws \Exception
     */
    public function commentValidate(int $id): RedirectResponse
    {
        if (!$this->isAdmin()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas valider les commentaires', 60);
            return $this->redirectToReferer();
        }
        $comment = (new Comment())->findById($id);

        if ($comment && !$comment->isValidated()) {
            $comment->validate();
            $this->cookieManager->setCookie('validate_message', 'Le commentaire a bien été validé.', 60);
            return $this->redirectToRoute('comment_show', ['id' => (string) $id]);
        }
        return $this->redirectToReferer();

    }
}
