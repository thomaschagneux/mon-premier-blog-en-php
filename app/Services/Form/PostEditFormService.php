<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use App\Models\Post;
use Twig\Environment;

class PostEditFormService extends AbstractFormService
{

    public function __construct(
        Environment $twig,
        private readonly Post $post,
    )
    {
        parent::__construct($twig);
    }

    /**
     * Build the form components and store them in an associative array.
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

        ));

        $this->addFormRow('lede', new FormRowComponent(
            'text',
            'lede',
            $this->post->getLede(),
            ['class' => 'form-control', 'placeholder' => 'Enter lede', 'required' => true],
        ));

        $this->addFormRow('content', new FormRowComponent(
            'textarea',
            'content',
            $this->post->getContent(),
            ['class' => 'tiny-mce', 'rows' => 5],
            [],
        ));


       /* $this->addFormRow('image', new FormRowComponent(
            'file',
            'image',
            '',
            ['class' => 'form-control-file'],
            [],
        ));

        $selectOptions = [
            ['value' => '1', 'label' => 'Option 1'],
            ['value' => '2', 'label' => 'Option 2'],
            ['value' => '3', 'label' => 'Option 3', 'selected' => true],
        ];
        $this->addFormRow('category', new FormRowComponent(
            'select',
            'category',
            null,
            ['class' => 'form-select'],
            $selectOptions,
        ));*/
    }
}
