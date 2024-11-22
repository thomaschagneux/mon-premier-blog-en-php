<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;

class PostAddFormService extends AbstractFormService
{
    /**
     * Build the form components and store them in an associative array.
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
