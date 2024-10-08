<?php

namespace App\Controllers;

use App\core\Router;
class CommentController extends AbstractController
{
    public function __construct(
        Router $router,
    )
    {
        parent::__construct($router);
    }

    public function commentShow(int $commentId): void
    {

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