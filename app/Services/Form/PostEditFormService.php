<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use App\Models\Post;
use Twig\Environment;

/**
 * Class PostEditFormService
 * Provides a form service for editing posts, extending the AbstractFormService.
 */
class PostEditFormService extends AbstractFormService
{
    /**
     * PostEditFormService constructor.
     * Initializes the form service with the Twig environment and a Post model instance.
     *
     * @param Environment $twig The Twig environment for rendering templates.
     * @param Post        $post The Post model instance to edit.
     */
    public function __construct(
        Environment $twig,
        private readonly Post $post,
    ) {
        parent::__construct($twig);
    }

    /**
     * Builds the form components for editing a post and stores them in the form rows array.
     * The form includes fields for title, lede, content, and an optional cover image.
     *
     * @return void
     */
    public function buildForm(): void
    {
        $this
            ->addFormRow('title', new FormRowComponent(
                'text',
                'title',
                $this->post->getTitle(),
                ['class' => 'form-control', 'placeholder' => 'Enter title', 'required' => true],
                [],
                'Titre'
            ));

        $this->addFormRow('lede', new FormRowComponent(
            'text',
            'lede',
            $this->post->getLede(),
            ['class' => 'form-control', 'placeholder' => 'Enter lede', 'required' => true],
            [],
            'Chapô'
        ));

        $this->addFormRow('content', new FormRowComponent(
            'textarea',
            'content',
            $this->post->getContent(),
            ['class' => 'tiny-mce', 'rows' => 5],
            [],
            'Contenu de l\'article'
        ));

        $this->addFormRow('image', new FormRowComponent(
            'file',
            'image',
            '',
            ['class' => 'form-control-file'],
            [],
            'Image de couverture'
        ));
    }
}
