<?php

namespace App\Services\CustomTables;

use App\core\Router;
use App\Models\Post;
use App\Models\User;
use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class PostTableService
 * Manages the rendering of a table for posts, including actions and formatting.
 */
class PostTableService extends AbstractTableService
{
    /**
     * @var Post The Post model instance.
     */
    private Post $post;

    /**
     * PostTableService constructor.
     * Initializes the service and sets up column mappings.
     *
     * @param Post        $post   The Post model.
     * @param Environment $twig   The Twig environment.
     * @param Router      $router The router instance.
     */
    public function __construct(
        Post $post,
        Environment $twig,
        Router $router,
    ) {
        $this->post           = $post;
        $this->columnMappings = [
            'title'      => 'Titre',
            'lede'       => 'Chapô',
            'author'     => 'Auteur',
            'created_at' => 'Date de création',
            'updated_at' => 'Date de modification',
            'actions'    => 'Action',
        ];
        parent::__construct($twig, $router);
    }

    /**
     * Generates the table content for posts, optionally filtered by a user.
     *
     * @param User|null $user The user to filter posts by (optional).
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     *
     * @return string The rendered table HTML.
     */
    public function getTableContent(?User $user = null): string
    {
        $posts = $user === null
            ? $this->post->getAllPosts()
            : $this->post->findPostsByUserId($user->getId());

        $rows = [];
        foreach ($posts as $post) {
            $rows[] = [
                'title'      => $post->getTitle(),
                'lede'       => $this->getLede($post, 100),
                'author'     => $this->getAuthor($post),
                'created_at' => $post->getCreatedAt()->format('d/m/Y'),
                'updated_at' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('d/m/Y') : '',
                'actions'    => $this->getAction($post),
            ];
        }

        return $this->renderTable($rows);
    }

    /**
     * Renders action buttons for a post.
     *
     * @param Post $post The post to generate actions for.
     *
     * @return string The rendered action buttons HTML.
     */
    public function getAction(Post $post): string
    {
        return $this->twig->render('tables/_actions.html.twig', [
            'edit'   => $this->edit($post),
            'remove' => $this->remove($post),
            'show'   => $this->show($post),
        ]);
    }

    /**
     * Generates the "Edit" action link for a post.
     *
     * @param Post $post The post to edit.
     *
     * @return string The "Edit" action link HTML.
     */
    private function edit(Post $post): string
    {
        return $this->getEditLink('edit_post_form', ['id' => (string) $post->getId()]);
    }

    /**
     * Generates the "Remove" action link for a post.
     *
     * @param Post $post The post to remove.
     *
     * @return string The "Remove" action link HTML.
     */
    private function remove(Post $post): string
    {
        return $this->getDeleteLink('post_remove', ['id' => (string) $post->getId()]);
    }

    /**
     * Generates the "Show" action link for a post.
     *
     * @param Post $post The post to show.
     *
     * @return string The "Show" action link HTML.
     */
    private function show(Post $post): string
    {
        return $this->getShowLink('post_show', ['id' => (string) $post->getId()]);
    }

    /**
     * Retrieves the author name of a post.
     *
     * @param Post $post The post to retrieve the author for.
     *
     * @throws Exception
     *
     * @return string The full name of the author, or an empty string if not found.
     */
    private function getAuthor(Post $post): string
    {
        $userModel = new User();
        $user      = $post->getUserId() ? $userModel->findById($post->getUserId()) : null;

        if ($user instanceof User) {
            return $user->getFirstName() . ' ' . $user->getLastName();
        }
        return '';
    }

    /**
     * Truncates the lede (introduction) of a post to a specified length.
     *
     * @param Post $post      The post to retrieve the lede for.
     * @param int  $strlength The maximum length of the lede.
     *
     * @return string The truncated lede.
     */
    private function getLede(Post $post, int $strlength): string
    {
        $lede = html_entity_decode(strip_tags($post->getContent()), ENT_QUOTES, 'UTF-8');

        if (mb_strlen($lede) > $strlength) {
            $lede = mb_substr($lede, 0, $strlength) . ' [...]';
        }

        return $lede;
    }
}
