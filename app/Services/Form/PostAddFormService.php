<?php

namespace App\Services\Form;

use App\Components\FormRowComponent;
use Twig\Environment;

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
            ['class' => 'form-control', 'placeholder' => 'Enter title'],
            [],

        ));

        $this->addFormRow('lede', new FormRowComponent(
            'text',
            'lede',
            '',
            ['class' => 'form-control', 'placeholder' => 'Enter lede'],
        ));

        $this->addFormRow('content', new FormRowComponent(
            'textarea',
            'content',
            '',
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
