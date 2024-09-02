<?php

namespace App\Controllers;

use App\core\RedirectResponse;


class Admincontroller extends AbstractController
{
    /**
     * @throws \Exception
     *
     * @return string|RedirectResponse
     */
    public function adminHome(): string|RedirectResponse
    {
        if ($this->isAdmin()) {
            return $this->render('admin/index.html.twig');
        }

        return $this->redirectToRoute('login');
    }
}