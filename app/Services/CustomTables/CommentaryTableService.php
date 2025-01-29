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

/**
 * Class CommentaryTableService
 * Manages the rendering of a table for comments, including actions and formatting.
 */
class CommentaryTableService extends AbstractTableService
{
    /**
     * @var Comment The Comment model instance.
     */
    private Comment $comment;

    /**
     * CommentaryTableService constructor.
     * Initializes the service and sets up column mappings.
     *
     * @param Environment $twig   The Twig environment.
     * @param Router      $router The router instance.
     */
    public function __construct(
        Environment $twig,
        Router $router,
    ) {
        $this->comment        = new Comment();
        $this->columnMappings = [
            'content'    => 'Contenu',
            'post'       => 'Post',
            'author'     => 'Auteur',
            'validated'  => 'Validé',
            'created_at' => 'Date de création',
            'updated_at' => 'Date de modification',
            'actions'    => 'Action',
        ];
        parent::__construct($twig, $router);
    }

    /**
     * Generates the table content for comments related to a post.
     *
     * @param Post      $post The post to retrieve comments for.
     * @param User|null $user The user to filter comments by (optional).
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws \Exception
     *
     * @return string The rendered table HTML.
     */
    public function getTableContent(Post $post, ?User $user = null): string
    {
        $comments = $user
            ? $this->comment->findCommentsByPostIdAndUserId($post->getId(), $user->getId())
            : $this->comment->getCommentsByPostId($post->getId());

        $rows = [];
        foreach ($comments as $comment) {
            $rows[] = [
                'content'    => $this->getContent($comment, 200),
                'author'     => $this->getAuthor($comment),
                'post'       => $this->getPost($comment, $post) ? $this->getPost($comment, $post)->getTitle() : '',
                'validated'  => $this->isValidated($comment),
                'created_at' => $comment->getCreatedAt()->format('d/m/Y'),
                'updated_at' => $comment->getUpdatedAt() ? $comment->getUpdatedAt()->format('d/m/Y') : '',
                'actions'    => $this->getAction($comment),
            ];
        }

        return $this->renderTable($rows);
    }

    /**
     * Renders action buttons for a comment.
     *
     * @param Comment $comment The comment to generate actions for.
     *
     * @return string The rendered action buttons HTML.
     */
    public function getAction(Comment $comment): string
    {
        return $this->twig->render('tables/_actions.html.twig', [
            'edit'   => $this->edit($comment),
            'remove' => $this->remove($comment),
            'show'   => $this->show($comment),
        ]);
    }

    /**
     * Generates the "Edit" action link for a comment.
     *
     * @param Comment $comment The comment to edit.
     *
     * @return string The "Edit" action link HTML.
     */
    private function edit(Comment $comment): string
    {
        return $this->getEditLink('comment_edit_form', ['id' => (string) $comment->getId()]);
    }

    /**
     * Generates the "Remove" action link for a comment.
     *
     * @param Comment $comment The comment to remove.
     *
     * @return string The "Remove" action link HTML.
     */
    private function remove(Comment $comment): string
    {
        return $this->getDeleteLink('comment_remove', ['id' => (string) $comment->getId()]);
    }

    /**
     * Generates the "Show" action link for a comment.
     *
     * @param Comment $comment The comment to show.
     *
     * @return string The "Show" action link HTML.
     */
    private function show(Comment $comment): string
    {
        return $this->getShowLink('comment_show', ['id' => (string) $comment->getId()]);
    }

    /**
     * Retrieves the author name of a comment.
     *
     * @param Comment $comment The comment to retrieve the author for.
     *
     * @throws \Exception
     *
     * @return string The full name of the author, or an empty string if not found.
     */
    private function getAuthor(Comment $comment): string
    {
        $userModel = new User();
        $user      = $comment->getUserId() ? $userModel->findById($comment->getUserId()) : null;
        if ($user instanceof User) {
            return $user->getFirstName() . ' ' . $user->getLastName();
        }
        return '';
    }

    /**
     * Retrieves the post associated with a comment.
     *
     * @param Comment $comment The comment to retrieve the post for.
     * @param Post    $post    The current post being processed.
     *
     * @return Post|null The associated post, or null if not found.
     */
    private function getPost(Comment $comment, Post $post): ?Post
    {
        if ($comment->getPostId() === $post->getId()) {
            return $post;
        }

        $postModel = new Post();
        $foundPost = $comment->getPostId() ? $postModel->findById($comment->getPostId()) : null;

        return $foundPost instanceof Post ? $foundPost : null;
    }

    /**
     * Truncates the content of a comment to a specified length.
     *
     * @param Comment $comment   The comment to truncate.
     * @param int     $strlength The maximum length of the content.
     *
     * @return string The truncated content.
     */
    private function getContent(Comment $comment, int $strlength): string
    {
        $content = strip_tags($comment->getContent());

        if (mb_strlen($content) > $strlength) {
            $content = mb_substr($content, 0, $strlength) . ' [...]';
        }
        return $content;
    }

    /**
     * Returns a formatted validation badge for the comment's validation status.
     *
     * @param Comment $comment The comment to check.
     *
     * @return string The HTML badge indicating the validation status.
     */
    private function isValidated(Comment $comment): string
    {
        if ($comment->isValidated()) {
            return '<span class="badge text-bg-success">Validé</span>';
        }
        return '<span class="badge text-bg-warning">Non validé</span>';
    }
}
