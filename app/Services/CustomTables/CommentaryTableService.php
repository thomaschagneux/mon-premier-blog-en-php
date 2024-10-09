<?php

namespace App\Services\CustomTables;

use App\core\Router;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CommentaryTableService extends AbstractTableService
{
    private Comment $comment;


    public function __construct(
        Environment $twig,
        Router $router,
    )
    {
        $this->comment = new Comment();
        $this->columnMappings = [
            'content' => 'Contenu',
            'post' => 'Post',
            'author' => 'Auteur',
            'created_at' => 'Date de création',
            'updated_at' => 'Date de modification',
            'actions' => 'Action',
        ];
        parent::__construct($twig, $router);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws \Exception
     */
    public function getTableContent(Post $post): string
    {
        $comments = $this->comment->getCommentsByPostId($post->getId());

        $rows = [];
        foreach ($comments as $comment) {
            $rows[] = [
                'content' => $this->getContent($comment, 200),
                'author' => $this->getAuthor($comment),
                'post' => $this->getPost($comment) ? $this->getPost($comment)->getTitle() :  '',
                'created_at' => $comment->getCreatedAt()->format('d/m/Y'),
                'updated_at' => $comment->getUpdatedAt() ? $comment->getUpdatedAt()->format('d/m/Y') : '',
                'actions' => $this->getAction($comment),
            ];
        }

        return $this->renderTable($rows);
    }

    public  function getAction(Comment $comment): string
    {
        return $this->twig->render('tables/_actions.html.twig', [
            'edit' => $this->edit($comment),
            'remove' => $this->remove($comment),
            'show' => $this->show($comment),
        ]);
    }

    private function edit(Comment $comment): string
    {
        return  $this->getEditLink('edit_post_form', ['id' => (string) $comment->getId()]);
    }

    private function remove(Comment $comment): string
    {
        return $this->getDeleteLink('post_remove', ['id' => (string) $comment->getId()]);
    }

    private function show(Comment $comment): string
    {
        return $this->getShowLink('comment_show', ['id' => (string) $comment->getId()]);
    }

    /**
     * @throws \Exception
     */
    private function getAuthor(Comment $comment): string
    {
        $userModel = new User();

        $user = $comment->getUserId() ? $userModel->findById($comment->getUserId()) : null;
        if ($user instanceof User) {
            return $user->getFirstName() . ' ' . $user->getLastName();
        } else {
            return '';
        }
    }

    private function getPost(Comment $comment): ?Post
    {
        $postModel = new Post();
        $post = $comment->getPostId() ? $postModel->findById($comment->getPostId()) : null;
        if ($post instanceof Post) {
            return $post;

        } else {
            return null;
        }
    }

    private function getContent(Comment $comment, int $strlength): string
    {
        $content = strip_tags($comment->getContent());


        if (mb_strlen($content) > $strlength) {
            $content = mb_substr($content, 0, $strlength) . ' [...]';
        }
        return $content;
    }
}