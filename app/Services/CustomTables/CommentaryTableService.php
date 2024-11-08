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
            'validated' => 'Validé',
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
                // Utilise le post passé en paramètre plutôt que de le rechercher à nouveau
                'post' => $this->getPost($comment, $post) ? $this->getPost($comment, $post)->getTitle() : '',
                'validated' => $this->isValidated($comment),
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
        return  $this->getEditLink('comment_edit_form', ['id' => (string) $comment->getId()]);
    }

    private function remove(Comment $comment): string
    {
        return $this->getDeleteLink('comment_remove', ['id' => (string) $comment->getId()]);
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

    private function getPost(Comment $comment, Post $post): ?Post
    {
        // Si l'ID du post dans le commentaire est le même que celui du post passé en paramètre
        if ($comment->getPostId() === $post->getId()) {
            return $post;
        }

        // Si le commentaire fait référence à un autre post, charger ce post
        $postModel = new Post();
        $foundPost = $comment->getPostId() ? $postModel->findById($comment->getPostId()) : null;

        return $foundPost instanceof Post ? $foundPost : null;
    }


    private function getContent(Comment $comment, int $strlength): string
    {
        $content = strip_tags($comment->getContent());


        if (mb_strlen($content) > $strlength) {
            $content = mb_substr($content, 0, $strlength) . ' [...]';
        }
        return $content;
    }

    private function isValidated(Comment $comment): string
    {
        if ($comment->isValidated()) {
            return '<span class="badge text-bg-success">Validé</span>';
        } else {
            return '<span class="badge text-bg-warning">Non validé</span>';
        }
    }
}