<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Comment;
use App\Models\Picture;
use App\Models\Post;
use App\Models\User;
use App\Services\CustomTables\CommentaryTableService;
use App\Services\CustomTables\PostTableService;
use App\Services\Form\PostAddFormService;
use App\Services\Form\PostEditFormService;
use App\Services\Sanitizer;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PostController extends AbstractController
{
    private Post $post;

    private Comment $comment;

    private PostTableService $postTableService;

    private CommentaryTableService $commentaryTableService;

    public function __construct(Router $router)
    {
        parent::__construct($router);
        $this->post                   = new Post();
        $this->comment                = new Comment();
        $this->postTableService       = new PostTableService($this->post, $this->twig, $this->router);
        $this->commentaryTableService = new CommentaryTableService($this->twig, $this->router);
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
            $messageSuccess = $this->cookieManager->getCookie('success_message');
            if (null !== $messageSuccess) {
                $this->cookieManager->deleteCookie('success_message');
            }
            $messageError = $this->cookieManager->getCookie('error_message');
            if (null !== $messageError) {
                $this->cookieManager->deleteCookie('error_message');
            }
            $posts = $this->post->getAllPosts();
            $table = $this->postTableService->getTableContent();

            return $this->render('post/list.html.twig', [
                'posts'           => $posts,
                'table'           => $table,
                'success_message' => $messageSuccess,
                'error_message'   => $messageError,
            ]);
        }

        if ($this->isConnected()) {
            $messageSuccess = $this->cookieManager->getCookie('success_message');
            if (null !== $messageSuccess) {
                $this->cookieManager->deleteCookie('success_message');
            }
            $messageError = $this->cookieManager->getCookie('error_message');
            if (null !== $messageError) {
                $this->cookieManager->deleteCookie('error_message');
            }

            $userData = $this->getUserData();
            if (!is_array($userData) || !isset($userData['email'])) {
                return $this->redirectToRoute('logout');
            }
            $mail        = (string) $userData['email'];
            $currentUser = (new User())->findByUsermail($mail);
            if (!$currentUser instanceof User) {
                return $this->redirectToRoute('logout');
            }
            $posts       = $this->post->findPostsByUserId($currentUser->getId());
            $table       = $this->postTableService->getTableContent($currentUser);
            return $this->render('post/list.html.twig', [
                'posts'           => $posts,
                'table'           => $table,
                'success_message' => $messageSuccess,
                'error_message'   => $messageError,
            ]);
        }

        $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
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
            $formRows = $postAddFormService->getFormRows();

            return $this->render('post/add.html.twig', [
                'form_rows'     => $formRows,
                'error_message' => $message,
            ]);
        }
        return $this->redirectToReferer();

    }

    public function addPostAction(): string|RedirectResponse
    {
        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }
        $title   =  $this->postManager->getPostParam('title');
        $lede    =  $this->postManager->getPostParam('lede');
        $content = $this->postManager->getPostParam('content');

        if (null === $title || null === $content || null === $lede) {
            $this->cookieManager->setCookie('error_message', 'Veuillez remplir les champs requis', 60);
            return $this->redirectToRoute('add_post_form');
        }

        $userModel = new User();
        $userData  = $this->getUserData();

        if (!is_array($userData) || !isset($userData['email']) || !is_string($userData['email'])) {
            $this->cookieManager->setCookie('error_message', 'Il y a eu une erreur, veuillez recommencer');
            return $this->redirectToRoute('add_post_form');
        }

        $user = $userModel->findByUsermail($userData['email']);

        if (!$user instanceof User) {
            $this->cookieManager->setCookie('error_message', 'Il y a eu une erreur, veuillez recommencer');
            return $this->redirectToRoute('add_post_form');
        }

        $postModel = new Post();
        $postModel->setContent($content);
        $postModel->setTitle($title);
        $postModel->setLede($lede);
        $postModel->setUserId($user->getId());
        $postModel->setCreatedAt(new \DateTime());

        $picture    = new Picture();

        try {
            $fileData   =  $this->fileManager->getFile('image');
        } catch (\Exception $e) {
            $this->cookieManager->setCookie('error_message', 'Le post doit avoir une image de présentation', 60);
            return $this->redirectToRoute('add_post_form');
        }

        if (null !== $fileData) {
            $extension      = pathinfo($fileData->name, PATHINFO_EXTENSION);
            $uniqueFileName = 'featured_image_' . uniqid() . '.' . $extension;
            $uniqueFileName = Sanitizer::sanitizeString($uniqueFileName);

            $picture->setFileName($uniqueFileName);
            $picture->setPathName('assets/img/featured_image/');
            $picture->setMimeType($fileData->type);

            $this->fileManager->setDestination($picture->getPathName());
            $this->fileManager->moveFile($fileData->tmp_name, $picture->getFileName());
            $picture->save();

            $postModel->setFeaturedImage($picture);
            $postModel->setFeaturedImageId($picture->getId());
        } else {
            $postModel->setFeaturedImage(null);
        }

        $postModel->save();
        $this->cookieManager->setCookie('success_message', 'Le post a bien été enregistré', 60);
        return $this->redirectToRoute('list_post');
    }

    public function editPostForm(int $id): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('error_message') ?? null;

        if ($message !== null) {
            $this->cookieManager->deleteCookie('error_message');
        }

        if ($this->isConnected()) {
            $PostModel = new Post();
            $post      = $PostModel->findById($id);

            if (!$post instanceof Post) {
                $this->cookieManager->setCookie('error_message', 'Il y a eu une erreur, veuillez recommencer', 60);
                return $this->redirectToRoute('list_post');
            }
            $postEditFormService = new PostEditFormService($this->twig, $post);

            $postEditFormService->buildForm();
            $formRows = $postEditFormService->getFormRows();

            return  $this->render('post/edit.html.twig', [
                'form_rows'     => $formRows,
                'post'          => $post,
                'error_message' => $message,
                ]);
        }

        $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
        return $this->redirectToReferer();

    }

    /**
     * @throws \Exception
     */
    public function editPostAction(int $id): string|RedirectResponse
    {
        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }
        $title   =  $this->postManager->getPostParam('title');
        $lede    =  $this->postManager->getPostParam('lede');
        $content = $this->postManager->getPostParam('content');
        if (null === $title || null === $content || null === $lede) {
            $this->cookieManager->setCookie('error_message', 'Veuillez remplir les champs requis', 60);
            return $this->redirectToRoute('add_post_form');
        }

        $postModel = new Post();
        $post      = $postModel->findById($id);

        if (!$post instanceof Post) {
            $this->cookieManager->setCookie('error_message', 'Il y a eu une erreur, veuillez recommencer', 60);
            return $this->redirectToRoute('add_post_form');
        }
        $post->setContent($content);
        $post->setTitle($title);
        $post->setLede($lede);
        $post->setUpdatedAt(new \DateTime());

        if ($this->fileManager->isPostFiles('image')) {
            $picture    = new Picture();
            $fileData   =  $this->fileManager->getFile('image');

            if (null !== $fileData) {
                $extension      = pathinfo($fileData->name, PATHINFO_EXTENSION);
                $uniqueFileName = 'featured_image_' . uniqid() . '.' . $extension;
                $uniqueFileName = Sanitizer::sanitizeString($uniqueFileName);

                $picture->setFileName($uniqueFileName);
                $picture->setPathName('assets/img/featured_image/');
                $picture->setMimeType($fileData->type);
                $this->fileManager->setDestination($picture->getPathName());
                $this->fileManager->moveFile($fileData->tmp_name, $picture->getFileName());

                $picture->save();

                $post->setFeaturedImage($picture);
            }

        } elseif (null !== $post->getFeaturedImageId()) {
            // Une image existe déjà, on peut continuer sans erreur
        } else {
            $this->cookieManager->setCookie('error_message', 'Le post doit avoir une image de présentation', 60);
            return $this->redirectToRoute('edit_post_form', ['id' => (string) $id]);
        }

        $post->save();
        $this->cookieManager->setCookie('success_message', 'Le post a bien été enregistré', 60);
        return $this->redirectToRoute('list_post');
    }

    /**
     * @throws \DateMalformedStringException
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function postShow(int $id): string|RedirectResponse
    {
        $message = $this->cookieManager->getCookie('success_message');
        if (null !== $message) {
            $this->cookieManager->deleteCookie('success_message');
        }

        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }
        if ($this->isAdmin()) {
            $post     = $this->post->findById($id);
            $comments = $this->comment->getCommentsByPostId($id);

            $commentsTable = '';
            if ($post instanceof Post) {
                $commentsTable = $this->commentaryTableService->getTableContent($post);
            }
        } elseif ($this->isConnected()) {
            $post     = $this->post->findById($id);
            $user     = (new User())->findByUsermail($this->getUserData()['email']);
            if (!$user instanceof User || !$post instanceof Post) {
                $commentsTable = '';
            }

            $comments      = $this->comment->findCommentsByPostIdAndUserId($post->getId(), $user->getId());
            $commentsTable = $this->commentaryTableService->getTableContent($post, $user);

        }


        return $this->render('post/show.html.twig', [
            'post'            => $post,
            'comments'        => $comments,
            'comments_table'  => $commentsTable,
            'success_message' => $message,
        ]);
    }

    public function postRemove(int $id): string|RedirectResponse
    {
        $post = (new Post())->findById($id);
        if (!$post instanceof Post) {
            $this->cookieManager->setCookie('error_message', 'Post non trouvé', 60);
            return $this->redirectToReferer();
        }

        if (!$this->isConnected()) {
            $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
            return $this->redirectToReferer();
        }
        if (!$this->isAdmin()) {
            $user = (new User())->findByUsermail($this->getUserData()['email']);
            if ($user instanceof User) {
                if ($user->getId() !== $post->getUserId()) {
                    $this->cookieManager->setCookie('error_message', 'Vous ne pouvez pas accéder à cette page', 60);
                    return $this->redirectToReferer();
                }
            }
        }
        if ($post->remove()) {
            $this->cookieManager->setCookie('success_message', 'Ce post a bien été supprimé', 60);
            return $this->redirectToRoute('list_post');
        }
        $this->cookieManager->setCookie('error_message', 'Il y a eu un problème dans la suppression du post', 60);
        return $this->redirectToRoute('list_post');
    }
}
