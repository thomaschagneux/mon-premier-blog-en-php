<?php

namespace App\Services\CustomTables;

use App\core\Router;
use App\Models\Post;
use App\Models\User;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PostTableService extends AbstractTableService
{
    private Post $post;

    public function __construct(
        Post $post,
        Environment $twig,
        Router $router,
    )
    {
        $this->post = $post;
        $this->columnMappings = [
            'title' => 'Titre',
            'lede' => 'Chapô',
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
    public function getTableContent(): string
    {
        $posts = $this->post->getAllPosts();

        $rows = [];
        foreach ($posts as $post) {
            $rows[] = [
                'title' => $post->getTitle(),
                'lede' => $post->getLede(),
                'author' => $this->getAuthor($post),
                'created_at' => $post->getCreatedAt()->format('d/m/Y'),
                'updated_at' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('d/m/Y') : '',
                'actions' => $this->getAction($post),
            ];
        }

        return $this->renderTable($rows);
    }

    public  function getAction(Post $post): string
    {
        return $this->twig->render('tables/_actions.html.twig', [
            'edit' => $this->edit($post),
            'remove' => $this->remove($post),
            'show' => $this->show($post),
        ]);
    }

    private function edit(Post $post): string
    {
        return  $this->getEditLink('index');
    }

    private function remove(Post $post): string
    {
        return $this->getDeleteLink('index');
    }

    private function show(Post $post): string
    {
        return $this->getShowLink('post_show', ['id' => (string) $post->getId()]);
    }

    /**
     * @throws \Exception
     */
    private function getAuthor(Post $post): string
    {
        $userModel = new User();

        $user = $post->getUserId() ? $userModel->findById($post->getUserId()) : null;
        if ($user instanceof User) {
            return $user->getFirstName() . ' ' . $user->getLastName();
        } else {
            return '';
        }
    }
}