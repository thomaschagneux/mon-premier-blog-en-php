<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;

/**
 * Class PostAddFormService
 * Provides a form service for adding posts, extending the AbstractFormService.
 */
class PostAddFormService extends AbstractFormService
{
    /**
     * Builds the form components for the post creation form and stores them in the form rows array.
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
                '',
                ['class' => 'form-control', 'placeholder' => 'Enter title', 'required' => true],
                [],
                'Titre'
            ));

        $this->addFormRow('lede', new FormRowComponent(
            'text',
            'lede',
            '',
            ['class' => 'form-control', 'placeholder' => 'Enter lede', 'required' => true],
            [],
            'Chapô'
        ));

        $this->addFormRow('content', new FormRowComponent(
            'textarea',
            'content',
            '',
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
