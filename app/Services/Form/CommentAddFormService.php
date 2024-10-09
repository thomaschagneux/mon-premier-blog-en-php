<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use App\Models\Post;
use Twig\Environment;

class CommentAddFormService extends AbstractFormService
{

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    /**
     * Build the form components and store them in an associative array.
     */
    public function buildForm(): void
    {
        $this->addFormRow('content', new FormRowComponent(
            'textarea',
            'content',
            '',
            ['class' => 'tiny-light', 'rows' => 5],
            [],
        ));
    }
}
