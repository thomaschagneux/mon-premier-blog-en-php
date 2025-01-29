<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use App\Models\Comment;
use Twig\Environment;

/**
 * Class CommentEditFormService
 * Provides a form service for editing comments, extending the AbstractFormService.
 */
class CommentEditFormService extends AbstractFormService
{
    /**
     * CommentEditFormService constructor.
     * Initializes the form service with the Twig environment and a Comment model instance.
     *
     * @param Environment $twig    The Twig environment for rendering templates.
     * @param Comment     $comment The Comment model instance to edit.
     */
    public function __construct(Environment $twig, private readonly Comment $comment)
    {
        parent::__construct($twig);
    }

    /**
     * Builds the form components for editing a comment and stores them in the form rows array.
     *
     * @return void
     */
    public function buildForm(): void
    {
        $this->addFormRow('content', new FormRowComponent(
            'textarea',
            'content',
            $this->comment->getContent(),
            ['class' => 'tiny-light', 'rows' => 5],
            [],
            ''
        ));
    }
}
