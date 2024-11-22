<?php

namespace App\Controllers;

use App\core\RedirectResponse;
use App\core\Router;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class AdminController extends AbstractController
{
    public function __construct(
        Router $router,
    ) {
        parent::__construct($router);
    }

    /**
     * @throws \Exception
     *
     * @return string|RedirectResponse
     */
    public function adminHome(): string|RedirectResponse
    {
        if ($this->isConnected()) {
            $errorMessage = $this->cookieManager->getCookie('error_message');
            if ($errorMessage) {
                $this->cookieManager->deleteCookie('error_message');
            }

            $userData = $this->getUserData();

            if (is_array($userData) && isset($userData['email'])) {

                $userModel = new User();
                $user      = $userModel->findByUsermail($userData['email']);

                if ($user instanceof User) {

                    $postModel  = new Post();
                    $posts      = $postModel->findPostsByUserId($user->getId());
                    $posts      = array_reverse($posts);
                    $views      = 0;
                    $totalPosts = 0;
                    foreach ($posts as $post) {
                        $totalPosts++;
                        if ($post instanceof Post) {
                            $views += $post->getViews();
                        }
                    }
                    $commentModel  = new Comment();
                    $comments      = $commentModel->findcommentsByUserId($user->getId());
                    $totalComments = 0;
                    foreach ($comments as $comment) {
                        $totalComments++;
                    }

                    return $this->render('admin/index.html.twig', [
                        'user'           => $user,
                        'posts'          => $posts,
                        'total_posts'    => $totalPosts,
                        'views'          => $views,
                        'total_comments' => $totalComments,
                        'error_message'  => $errorMessage,
                    ]);
                }
            }
            return $this->redirectToReferer();
        }

        return $this->redirectToRoute('login');
    }

}
