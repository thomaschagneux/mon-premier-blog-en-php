<?php

namespace App\Controllers;

use App\core\Router;
use App\Models\Comment;

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

    public function commentShow(int $commentId)
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

    public function commentAddForm(int $postId): void
    {

    }

    public function CommentAddAction():void
    {

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