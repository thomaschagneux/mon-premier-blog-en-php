<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use Twig\Environment;

/**
 * Class CommentAddFormService
 * Provides a form service for adding comments, using the AbstractFormService as a base.
 */
class CommentAddFormService extends AbstractFormService
{
    /**
     * CommentAddFormService constructor.
     * Initializes the form service with the Twig environment.
     *
     * @param Environment $twig The Twig environment for rendering templates.
     */
    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    /**
     * Builds the form components for the comment form and stores them in the form rows array.
     * This method defines a single form row for the comment content field.
     *
     * @return void
     */
    public function buildForm(): void
    {
        $this->addFormRow('content', new FormRowComponent(
            'textarea',
            'content',
            '',
            ['class' => 'tiny-light', 'rows' => 5],
            [],
            'Votre commentaire'
        ));
    }
}
